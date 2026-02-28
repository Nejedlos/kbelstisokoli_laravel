<?php

namespace App\Http\Controllers;

use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class MediaDownloadController extends Controller
{
    /**
     * Zabezpečené stahování média.
     */
    public function download(string $uuid): BinaryFileResponse
    {
        $media = Media::where('uuid', $uuid)->firstOrFail();
        $model = $media->model;

        // Kontrola oprávnění u MediaAsset
        if ($model instanceof \App\Models\MediaAsset) {
            $this->authorizeAccess($model);
        }

        if (! file_exists($media->getPath())) {
            abort(404, 'Soubor neexistuje.');
        }

        return response()->download($media->getPath(), $media->file_name);
    }

    /**
     * Ověření oprávnění pro přístup k médiu.
     */
    protected function authorizeAccess(\App\Models\MediaAsset $asset): void
    {
        if ($asset->access_level === 'public') {
            return;
        }

        if (! auth()->check()) {
            abort(401);
        }

        $user = auth()->user();

        // Admin má přístup ke všemu
        if (method_exists($user, 'hasRole') && $user->hasRole('super_admin')) {
            return;
        }

        // Kontrola přístupu pro členy
        if ($asset->access_level === 'member') {
            if (! $user->can('view_member_media')) {
                abort(403, 'Nemáte oprávnění pro zobrazení členských souborů.');
            }
        }

        // Kontrola přístupu pro soukromé soubory
        if ($asset->access_level === 'private') {
            if (! $user->can('view_private_media')) {
                abort(403, 'Nemáte oprávnění pro zobrazení soukromých souborů.');
            }
        }
    }
}
