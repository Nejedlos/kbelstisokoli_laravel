<?php

use App\Models\BasketballMatch;
use App\Models\Season;
use App\Models\Team;
use App\Models\TeamEloRating;
use Illuminate\Contracts\Console\Kernel;

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();
if (! app()->isProduction()) {
    throw new RuntimeException('Production only.');
}
$season = Season::where('is_active', true)->firstOrFail();
foreach (Team::whereIn('slug', ['muzi-e', 'muzi-c'])->get() as $team) {
    $matches = BasketballMatch::where('season_id', $season->id)->where(fn ($q) => $q->where('team_id', $team->id)->orWhereHas('teams', fn ($t) => $t->where('teams.id', $team->id)))->with('prediction')->get();
    $completed = $matches->filter(fn ($m) => in_array($m->status, ['finished', 'played', 'completed']) && $m->score_home !== null && $m->score_away !== null && $m->score_home + $m->score_away > 0);
    $planned = $matches->whereIn('status', ['planned', 'scheduled']);
    echo json_encode([
        'team' => $team->slug,
        'completed_result_count' => $completed->count(),
        'completed_without_primary_team_count' => $completed->whereNull('team_id')->count(),
        'planned_count' => $planned->count(),
        'planned_with_team_comparison_count' => $planned->filter(fn ($m) => ! empty($m->metadata['team_comparison']))->count(),
        'planned_with_opponent_recent_results_count' => $planned->filter(fn ($m) => ! empty($m->metadata['last_matches'][$m->is_home ? 'away' : 'home']))->count(),
        'planned_with_mutual_results_count' => $planned->filter(fn ($m) => ! empty($m->metadata['mutual_matches']))->count(),
        'planned_preview_refreshed_count' => $planned->filter(fn ($m) => ! empty($m->metadata['preview_synced_at']))->count(),
        'planned_v4_prediction_count' => $planned->filter(fn ($m) => $m->prediction?->model_version === 'elo+form+roster+preview+h2h:v4')->count(),
        'maximum_own_form_games_count' => $planned->max(fn ($m) => $m->prediction?->factors['form_team']['count'] ?? 0),
        'planned_with_opponent_form_used_count' => $planned->filter(fn ($m) => ($m->prediction?->factors['form_opp']['count'] ?? 0) > 0)->count(),
        'opponent_ratings_available_count' => TeamEloRating::where('season_id', $season->id)->whereIn('team_key', $planned->pluck('opponent_id')->filter()->map(fn ($id) => TeamEloRating::getOpponentKey($id)))->count(),
    ], JSON_THROW_ON_ERROR).PHP_EOL;
}
