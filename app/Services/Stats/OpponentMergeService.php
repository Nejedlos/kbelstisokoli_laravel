<?php

namespace App\Services\Stats;

use App\Models\BasketballMatch;
use App\Models\ExternalEntityMapping;
use App\Models\Opponent;
use App\Models\OpponentMergeSuggestion;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OpponentMergeService
{
    /**
     * Skenuje databázi soupeřů a hledá potenciální duplicity.
     *
     * @param bool $hard Pokud je true, zahrne i dříve odmítnuté návrhy.
     */
    public function scan(bool $hard = false): int
    {
        $opponents = Opponent::all();
        $suggestionsCreated = 0;

        foreach ($opponents as $i => $opponentA) {
            foreach ($opponents as $j => $opponentB) {
                // Srovnáváme každou dvojici jen jednou (A.id < B.id)
                if ($i >= $j || $opponentA->id >= $opponentB->id) {
                    continue;
                }

                $similarity = $this->calculateSimilarity($opponentA, $opponentB);

                if ($similarity >= 70) { // Práh podobnosti 70 %
                    $suggestion = OpponentMergeSuggestion::where(function ($query) use ($opponentA, $opponentB) {
                        $query->where('source_opponent_id', $opponentA->id)
                            ->where('target_opponent_id', $opponentB->id);
                    })->first();

                    if (! $suggestion) {
                        OpponentMergeSuggestion::create([
                            'source_opponent_id' => $opponentA->id,
                            'target_opponent_id' => $opponentB->id,
                            'similarity' => $similarity,
                            'status' => 'pending',
                        ]);
                        $suggestionsCreated++;
                    } elseif ($hard && $suggestion->status === 'rejected') {
                        $suggestion->update(['status' => 'pending']);
                        $suggestionsCreated++;
                    }
                }
            }
        }

        return $suggestionsCreated;
    }

    /**
     * Vypočítá podobnost mezi dvěma soupeři (0 - 100).
     */
    protected function calculateSimilarity(Opponent $a, Opponent $b): int
    {
        // 1. Pokud mají stejné external_id (pokud existuje)
        $extA = $a->metadata['external_id'] ?? null;
        $extB = $b->metadata['external_id'] ?? null;
        if ($extA && $extB && $extA == $extB) {
            return 100;
        }

        $nameA = Str::lower(trim($a->name));
        $nameB = Str::lower(trim($b->name));
        $cityA = $a->city ? Str::lower(trim($a->city)) : null;
        $cityB = $b->city ? Str::lower(trim($b->city)) : null;

        // 2. Přesná shoda jména a města
        if ($nameA === $nameB && $cityA === $cityB) {
            return 98;
        }

        // 3. Shoda jména, ale různá (ne prázdná) města - snížíme pravděpodobnost
        if ($nameA === $nameB && $cityA && $cityB && $cityA !== $cityB) {
            return 50;
        }

        // 4. Obsahuje jeden druhého? (např. "Tým" vs "Tým Praha")
        if (Str::contains($nameA, $nameB) || Str::contains($nameB, $nameA)) {
            // Pokud jeden z nich obsahuje jméno toho druhého, je to dobrý kandidát
            if (strlen($nameA) > 4 && strlen($nameB) > 4) {
                $score = 85;
                // Pokud souhlasí i město (nebo je u jednoho null), zvýšíme
                if ($cityA === $cityB || ! $cityA || ! $cityB) {
                    $score += 5;
                }
                return $score;
            }
        }

        // 4. Levenshteinova vzdálenost
        $lev = levenshtein($nameA, $nameB);
        $maxLen = max(strlen($nameA), strlen($nameB));
        if ($maxLen > 0) {
            $sim = (1 - ($lev / $maxLen)) * 100;
            if ($sim > 70) {
                return (int) $sim;
            }
        }

        return 0;
    }

    /**
     * Provede sloučení soupeřů.
     */
    public function merge(OpponentMergeSuggestion $suggestion, string $newName): bool
    {
        return DB::transaction(function () use ($suggestion, $newName) {
            $source = $suggestion->sourceOpponent;
            $target = $suggestion->targetOpponent;

            if (! $source || ! $target) {
                return false;
            }

            // 1. Aktualizace cílového soupeře (nový název a metadata)
            $targetMetadata = $target->metadata ?? [];
            $sourceMetadata = $source->metadata ?? [];

            // Sloučíme varianty jmen
            $variants = array_unique(array_merge(
                $targetMetadata['external_name_variants'] ?? [],
                $sourceMetadata['external_name_variants'] ?? [],
                [$source->name, $target->name]
            ));
            $targetMetadata['external_name_variants'] = $variants;

            // Pokud cílový nemá external_id a zdrojový ano, vezmeme ho
            if (empty($targetMetadata['external_id']) && ! empty($sourceMetadata['external_id'])) {
                $targetMetadata['external_id'] = $sourceMetadata['external_id'];
            }

            $target->update([
                'name' => $newName,
                'metadata' => $targetMetadata,
            ]);

            // 2. Aktualizace všech zápasů ze zdrojového na cílového
            BasketballMatch::where('opponent_id', $source->id)
                ->update(['opponent_id' => $target->id]);

            // 3. Aktualizace externích mapování
            ExternalEntityMapping::where('internal_type', 'Opponent')
                ->where('internal_id', $source->id)
                ->update(['internal_id' => $target->id]);

            // 4. Smazání zdrojového soupeře
            $source->delete();

            // 4. Označení návrhu za přijatý
            $suggestion->update(['status' => 'accepted']);

            return true;
        });
    }

    /**
     * Odmítne návrh na sloučení.
     */
    public function reject(OpponentMergeSuggestion $suggestion): void
    {
        $suggestion->update(['status' => 'rejected']);
    }
}
