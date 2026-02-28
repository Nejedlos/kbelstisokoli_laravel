<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class AvatarsSyncCommand extends Command
{
    protected $signature = 'avatars:sync {--force : Přepíše existující avatar/galerii u uživatelů} {--base= : Základní cesta ke složce uploads (např. /path/to/nextai/public/uploads)}';

    protected $description = 'Synchronizuje avatary a hráčské fotografie z externího NextAI úložiště do tohoto systému.';

    public function handle(): int
    {
        $base = $this->option('base')
            ?: config('services.nextai.uploads_base')
            ?: env('SYNC_NEXTAI_UPLOADS_PATH', base_path('../nextai.localhost/public/uploads'));

        if (! $base || ! is_dir($base)) {
            $this->error("Neplatná cesta k uploads: {$base}");
            return self::FAILURE;
        }

        $this->info('Zdroj: ' . $base);
        $force = (bool) $this->option('force');

        $count = 0;
        User::query()
            ->orderBy('id')
            ->chunk(100, function ($users) use (&$count, $base, $force) {
                foreach ($users as $user) {
                    $userDir = rtrim($base, DIRECTORY_SEPARATOR) . '/users/' . $user->id;

                    // Avatar
                    $avatarDir = $userDir . '/avatar';
                    if (is_dir($avatarDir)) {
                        $avatarFile = $this->findLatestImage($avatarDir);
                        if ($avatarFile) {
                            if ($force || ! $user->hasMedia('avatar')) {
                                $user->clearMediaCollection('avatar');
                                $user->addMedia($avatarFile)
                                    ->usingFileName('avatar-imported-' . time() . '.' . pathinfo($avatarFile, PATHINFO_EXTENSION))
                                    ->toMediaCollection('avatar');
                                $this->line("[User #{$user->id}] Avatar importován.");
                            }
                        }
                    }

                    // Hráčské fotky (galerie)
                    $galleryDir = $userDir . '/gallery';
                    if (is_dir($galleryDir)) {
                        // Pokud není force a už existují fotky, přeskočit
                        if (! $force && $user->getMedia('player_photos')->count() > 0) {
                            continue;
                        }

                        if ($force) {
                            $user->clearMediaCollection('player_photos');
                        }

                        $images = $this->findAllImages($galleryDir);
                        foreach ($images as $img) {
                            $user->addMedia($img)
                                ->usingFileName('player-photo-' . Str::random(6) . '.' . pathinfo($img, PATHINFO_EXTENSION))
                                ->toMediaCollection('player_photos');
                        }

                        if (count($images)) {
                            $this->line("[User #{$user->id}] Načteno hráčských fotek: " . count($images));
                        }
                    }

                    $count++;
                }
            });

        $this->info("Hotovo. Zpracováno uživatelů: {$count}");
        return self::SUCCESS;
    }

    protected function findLatestImage(string $dir): ?string
    {
        $files = glob(rtrim($dir, DIRECTORY_SEPARATOR) . '/*.{jpg,jpeg,png,webp}', GLOB_BRACE) ?: [];
        if (empty($files)) {
            return null;
        }
        usort($files, fn($a, $b) => filemtime($b) <=> filemtime($a));
        return $files[0] ?? null;
    }

    protected function findAllImages(string $dir): array
    {
        $files = glob(rtrim($dir, DIRECTORY_SEPARATOR) . '/*.{jpg,jpeg,png,webp}', GLOB_BRACE) ?: [];
        sort($files); // deterministické pořadí
        return $files;
    }
}
