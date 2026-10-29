<?php

use App\Jobs\ComputeMatchPredictionJob;
use App\Models\BasketballMatch;
use App\Models\MatchPrediction;
use App\Models\Season;
use App\Models\TeamEloRating;
use App\Services\Prediction\EloService;
use App\Services\Prediction\PredictionService;
use App\Services\Stats\Sync\MatchPreviewSyncService;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$expectedSha = $argv[1] ?? '';
$marker = base_path('.release-sha');
if (! app()->isProduction() || ! preg_match('/^[a-f0-9]{40}$/', $expectedSha)
    || ! is_file($marker) || trim(file_get_contents($marker)) !== $expectedSha) {
    fwrite(STDERR, "Refusing repair: production environment and exact release marker are required.\n");
    exit(1);
}

if (! app()->isDownForMaintenance()) {
    throw new RuntimeException('Maintenance mode is required to prevent new queue workers.');
}

// Old in-flight workers must finish before their prediction writes can race repair.
$queueConnection = config('queue.connections.database.connection');
$queueTable = config('queue.connections.database.table', 'jobs');
$deadline = microtime(true) + 180;
do {
    $active = DB::connection($queueConnection)->table($queueTable)
        ->whereNotNull('reserved_at')
        ->where('payload', 'like', '%ComputeMatchPredictionJob%')->count();
    if ($active === 0) {
        break;
    }
    if (microtime(true) >= $deadline) {
        throw new RuntimeException('Active prediction workers did not drain; no data changed.');
    }
    usleep(500000);
} while (true);

umask(0077);
$directory = storage_path('app/private/prediction-repairs');
if (! is_dir($directory) && ! mkdir($directory, 0700, true) && ! is_dir($directory)) {
    throw new RuntimeException('Cannot create private backup directory.');
}
$lock = fopen($directory.'/repair.lock', 'c');
if (! $lock || ! flock($lock, LOCK_EX | LOCK_NB)) {
    throw new RuntimeException('Another prediction repair is running.');
}

// This repair computes predictions synchronously after the Elo replay.
Bus::fake([ComputeMatchPredictionJob::class]);
// Fetch only verified upcoming preview inputs; never import rosters or boxscores.
$previewCandidates = BasketballMatch::whereHas('season', fn ($query) => $query->where('is_active', true))
    ->whereIn('status', ['planned', 'scheduled'])->where('scheduled_at', '>', now())
    ->whereNotNull('team_id')->whereNotNull('opponent_id')->orderBy('scheduled_at')->get();
$previewsRefreshed = 0;
foreach ($previewCandidates as $candidate) {
    $before = $candidate->metadata['preview_synced_at'] ?? null;
    app(MatchPreviewSyncService::class)->sync($candidate->id, $candidate->team_id, true);
    $after = $candidate->fresh()->metadata['preview_synced_at'] ?? null;
    $previewsRefreshed += (int) ($after !== null && $after !== $before);
    usleep(200000);
}
echo json_encode(['preview_candidates' => $previewCandidates->count(), 'previews_refreshed' => $previewsRefreshed], JSON_THROW_ON_ERROR).PHP_EOL;
$summary = DB::transaction(function () use ($directory, $expectedSha): array {
    // Lock before the first snapshot read so a concurrent import cannot finish a
    // selected fixture or publish a newer season rating during this repair.
    Season::orderBy('id')->lockForUpdate()->get();
    BasketballMatch::orderBy('id')->lockForUpdate()->get();
    $beforePredictions = MatchPrediction::orderBy('id')->lockForUpdate()->get();
    $beforeRatings = TeamEloRating::orderBy('id')->get();
    $backup = $directory.'/'.gmdate('Ymd-His').'-'.bin2hex(random_bytes(4)).'.json';
    $data = json_encode([
        'release' => $expectedSha,
        'created_at' => now()->toIso8601String(),
        'team_elo_ratings' => $beforeRatings->map->getRawOriginal()->all(),
        'match_predictions' => $beforePredictions->map->getRawOriginal()->all(),
    ], JSON_THROW_ON_ERROR);
    if (file_put_contents($backup, $data, LOCK_EX) !== strlen($data)) {
        throw new RuntimeException('Backup failed; no data has been changed.');
    }

    $historicalIds = BasketballMatch::whereIn('status', ['finished', 'played', 'completed'])
        ->orWhere(function ($query): void {
            $query->whereNotNull('score_home')->whereNotNull('score_away')
                ->whereRaw('(score_home + score_away) > 0');
        })->pluck('id');
    $historicalBefore = MatchPrediction::whereIn('basketball_match_id', $historicalIds)
        ->orderBy('id')->get()->map->getRawOriginal()->all();

    $seasons = BasketballMatch::distinct()->pluck('season_id')
        ->merge(TeamEloRating::distinct()->pluck('season_id'))->filter()->unique()->sort();
    $results = 0;
    foreach ($seasons as $seasonId) {
        $results += app(EloService::class)->rebuildSeason((int) $seasonId);
    }

    $matches = BasketballMatch::whereIn('status', ['planned', 'scheduled'])
        ->whereNotNull('team_id')->whereNotNull('opponent_id')
        ->where(function ($query): void {
            $query->whereNull('score_home')->orWhereNull('score_away')->orWhereRaw('(score_home + score_away) = 0');
        })->orderBy('id')->lockForUpdate()->get();
    $changed = 0;
    foreach ($matches as $match) {
        $previous = $beforePredictions->firstWhere('basketball_match_id', $match->id);
        $prediction = app(PredictionService::class)->predict($match);
        if (! $prediction || $prediction->model_version !== 'elo+form+roster+preview+h2h:v4'
            || ! is_finite($prediction->probability_win) || ! is_finite($prediction->probability_loss)
            || $prediction->probability_win < 0.1 || $prediction->probability_win > 0.9
            || abs($prediction->probability_win + $prediction->probability_loss - 1) > 0.000001) {
            throw new RuntimeException('Prediction verification failed for match '.$match->id);
        }
        if (! $previous || abs($previous->probability_win - $prediction->probability_win) > 0.000001) {
            $changed++;
        }

    }

    $historicalAfter = MatchPrediction::whereIn('basketball_match_id', $historicalIds)
        ->orderBy('id')->get()->map->getRawOriginal()->all();
    if ($historicalBefore !== $historicalAfter) {
        throw new RuntimeException('Historical snapshots changed; rolling back repair.');
    }
    if (TeamEloRating::all()->contains(fn ($rating) => ! is_finite($rating->rating))) {
        throw new RuntimeException('Non-finite Elo rating; rolling back repair.');
    }

    return [
        'release' => $expectedSha,
        'backup_file' => basename($backup),
        'seasons_rebuilt' => $seasons->count(),
        'completed_results_replayed' => $results,
        'planned_predictions_recomputed' => $matches->count(),
        'probabilities_changed' => $changed,
        'historical_snapshots_preserved' => count($historicalAfter),
    ];
}, 3);
echo json_encode($summary, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT).PHP_EOL;
