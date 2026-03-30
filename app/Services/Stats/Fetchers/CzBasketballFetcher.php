<?php

namespace App\Services\Stats\Fetchers;

use App\Models\ExternalImportRun;
use App\Services\Stats\Contracts\StatFetcherInterface;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CzBasketballFetcher implements StatFetcherInterface
{
    protected ?ExternalImportRun $currentRun = null;

    /**
     * User-Agent pro obcházení bot-blocků.
     */
    protected string $userAgent = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36';

    /**
     * Timeout v sekundách.
     */
    protected int $timeout;

    /**
     * Počet pokusů o stažení.
     */
    protected int $retryCount;

    /**
     * Prodleva mezi pokusy v milisekundách (exponenciální backoff).
     */
    protected int $retryDelay;

    public function __construct()
    {
        $this->timeout = config('external_sources.czbasketball.fetcher.timeout', 60);
        $this->retryCount = config('external_sources.czbasketball.fetcher.retry_count', 3);
        $this->retryDelay = config('external_sources.czbasketball.fetcher.retry_delay', 3000);
    }

    /**
     * Nastaví aktuální běh pro účely logování a snapshotů.
     */
    public function setCurrentRun(ExternalImportRun $run): self
    {
        $this->currentRun = $run;

        return $this;
    }

    /**
     * Stáhne obsah z dané URL.
     */
    public function fetch(string $url, ?ExternalImportRun $run = null): string
    {
        if ($run) {
            $this->setCurrentRun($run);
        }

        Log::info("CzBasketballFetcher: Stahuji URL: {$url}");

        try {
            $response = Http::withHeaders([
                'User-Agent' => $this->userAgent,
            ])
                ->timeout($this->timeout)
                ->retry($this->retryCount, $this->retryDelay, function (\Exception $exception, $request) {
                    Log::warning("CzBasketballFetcher: Pokus o stažení selhal, zkouším znovu. Chyba: {$exception->getMessage()}");

                    return true;
                }, true) // true pro exponenciální backoff
                ->withOptions([
                    'allow_redirects' => true,
                    'verify' => false,
                ])
                ->get($url);

            Log::info("CzBasketballFetcher: Staženo. Status: {$response->status()}, Final URL: {$response->effectiveUri()}");

            $html = $response->body();

            // Oprava encodingu (pokud by bylo potřeba, cz.basketball bývá v UTF-8, ale pro jistotu)
            $html = $this->ensureUtf8($html, $response);

            // Uložit snapshot pokud je to potřeba (vždy při úspěchu, pokud máme kontext běhu)
            $this->saveSnapshot($html, $url, $response);

            if ($response->failed()) {
                throw new \Exception("Stažení URL {$url} selhalo se statusem {$response->status()}");
            }

            return $html;

        } catch (\Exception $e) {
            Log::error("CzBasketballFetcher: Kritická chyba při stahování {$url}: {$e->getMessage()}");

            // Při chybě se pokusíme uložit snapshot (pokud máme alespoň nějaký response)
            if (isset($response)) {
                $this->saveSnapshot($response->body() ?: '', $url, $response, true);
            }

            throw $e;
        }
    }

    /**
     * Zajistí, že obsah je v UTF-8.
     */
    protected function ensureUtf8(string $html, Response $response): string
    {
        $contentType = $response->header('Content-Type');

        if (Str::contains(Str::lower($contentType), 'windows-1250')) {
            return iconv('Windows-1250', 'UTF-8', $html);
        }

        return $html;
    }

    /**
     * Uloží syrové HTML do storage jako snapshot.
     */
    protected function saveSnapshot(string $html, string $url, ?Response $response = null, bool $isError = false): void
    {
        if (! $this->currentRun) {
            return;
        }

        $season = $this->currentRun->season?->name ?? 'unknown-season';
        $season = Str::slug($season);
        $type = $this->currentRun->run_type ?? 'general';
        $externalId = $this->currentRun->target_external_id ?? 'no-id';
        $timestamp = now()->format('Y-m-d-H-i-s');
        $suffix = $isError ? '-FAILED' : '';

        $filename = "{$externalId}-{$timestamp}{$suffix}.html";
        $path = "external/czbasketball/{$season}/{$type}/{$filename}";

        try {
            Storage::disk('local')->put($path, $html);

            // Uložit cestu do metadat běhu
            $metadata = $this->currentRun->metadata ?? [];
            $metadata['snapshot_path'] = $path;
            $metadata['http_status'] = $response?->status();
            $metadata['final_url'] = (string) $response?->effectiveUri();

            $this->currentRun->update(['metadata' => $metadata]);

            Log::debug("CzBasketballFetcher: Snapshot uložen do {$path}");
        } catch (\Exception $e) {
            Log::warning("CzBasketballFetcher: Nepodařilo se uložit snapshot: {$e->getMessage()}");
        }
    }
}
