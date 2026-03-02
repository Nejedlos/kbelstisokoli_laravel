<?php

namespace App\Livewire\Member;

use App\Models\MediaAsset;
use App\Support\Media\VirtualAvatarAsset;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class AvatarModal extends Component
{
    use WithFileUploads;

    public $isOpen = false;

    public $activeTab = 'gallery'; // 'upload' | 'gallery'

    public $avatarFile;

    public $previewUrl;

    public $cropData;
    // public $galleryAssets = []; // Odstraněno - nyní se načítá dynamicky v render() pro snížení zátěže Livewire stavu

    public $confirmingDelete = false;

    public $userId;

    protected $listeners = [
        'openAvatarModal' => 'open',
        'deleteAvatar' => 'confirmDelete',
    ];

    public function mount($userId = null)
    {
        $this->userId = $userId ?: auth()->id();
        // $this->loadGallery(); // Odstraněno, načítá se v render()
    }

    public function getGalleryAssets()
    {
        if (! $this->isOpen) {
            return collect();
        }

        // 1. Preferovat načtení přímo ze složky (robustní bez závislosti na DB)
        $assets = $this->loadGalleryFromDisk();

        // 2. Fallback: Pokud složka neobsahuje žádné položky, zkusíme DB (systémové avatary)
        if ($assets->isEmpty()) {
            $query = MediaAsset::query()
                ->where(function ($query) {
                    // Pouze ty, co mají v názvu "Avatar", abychom odfiltrovali např. fotky z importu photo poolů
                    $query->where('title', 'like', '%Avatar%')
                        ->orWhere('title', 'like', 'Default%');
                })
                ->whereNull('uploaded_by_id')
                ->where('is_public', true);

            $assets = $query
                ->latest('id')
                ->limit(100) // Stačí 100, ne tisíce
                ->get();
        }

        // 3. Logování, pokud je galerie stále prázdná
        if ($assets->isEmpty()) {
            \Illuminate\Support\Facades\Log::warning('AvatarModal: Galerie je prázdná i po pokusu o načtení z disku i DB.');
        }

        return $assets;
    }

    /**
     * Načte avatary přímo ze složky public/uploads/defaults, pokud chybí DB záznamy.
     */
    protected function loadGalleryFromDisk()
    {
        $path = public_path('uploads/defaults');
        if (! is_dir($path)) {
            return collect();
        }

        $assets = collect();
        // Získáme složky, které jsou pojmenované čísly (ID media)
        $directories = File::directories($path);

        foreach ($directories as $dir) {
            $mediaId = basename($dir);
            if (! is_numeric($mediaId)) {
                continue;
            }

            // Hledáme soubory rekurzivně, abychom našli i ty v conversions, pokud chybí v rootu
            $allFiles = File::allFiles($dir);
            if (empty($allFiles)) {
                continue;
            }

            // Najdeme hlavní soubor (preferujeme root, pak conversions)
            $mainFile = null;

            // 1. Zkusíme soubory přímo v adresáři
            $rootFiles = File::files($dir);
            foreach ($rootFiles as $file) {
                if (in_array(strtolower($file->getExtension()), ['jpg', 'jpeg', 'png', 'webp'])) {
                    $mainFile = $file;
                    break;
                }
            }

            // 2. Fallback na conversions (hledáme originál nebo aspoň něco)
            if (! $mainFile) {
                foreach ($allFiles as $file) {
                    $fName = $file->getFilename();
                    if (str_contains($fName, 'original') || str_contains($fName, 'optimized')) {
                        $mainFile = $file;
                        break;
                    }
                }
            }

            // 3. Poslední pokus - jakýkoliv obrázek kdekoli v adresáři
            if (! $mainFile) {
                foreach ($allFiles as $file) {
                    if (in_array(strtolower($file->getExtension()), ['jpg', 'jpeg', 'png', 'webp'])) {
                        $mainFile = $file;
                        break;
                    }
                }
            }

            if (! $mainFile) {
                continue;
            }

            $fileName = $mainFile->getFilename();
            // Pokud je soubor v podadresáři (např. conversions), musíme to zohlednit v URL
            $relativeDir = str_replace($path . '/' . $mediaId . '/', '', $mainFile->getPath() . '/');
            if ($relativeDir === $mainFile->getPath() . '/') { // Fallback pokud str_replace selže
                 $relativeDir = str_contains($mainFile->getPathname(), 'conversions') ? 'conversions/' : '';
            }

            $mainUrl = asset('uploads/defaults/'.$mediaId.'/'.$relativeDir.$fileName);

            // Náhled (zkusíme najít v podadresáři conversions)
            $thumbUrl = $mainUrl; // Fallback na originál
            $conversionsPath = $dir.'/conversions';
            if (is_dir($conversionsPath)) {
                $thumbFiles = File::files($conversionsPath);
                foreach ($thumbFiles as $thumb) {
                    $tName = $thumb->getFilename();
                    if (str_contains($tName, 'thumb') || str_contains($tName, 'optimized') || str_contains($tName, 'preview')) {
                        $thumbUrl = asset('uploads/defaults/'.$mediaId.'/conversions/'.$tName);
                        break;
                    }
                }
            }

            // Vytvoříme regulérní objekt simulující MediaAsset model
            $assets->push(new VirtualAvatarAsset($mediaId, $mainUrl, $thumbUrl));
        }

        // Seřadíme podle ID sestupně (aby nové byly nahoře)
        return $assets->sortByDesc('id')->values();
    }

    public function open($userId = null)
    {
        if ($userId) {
            $this->userId = $userId;
        }
        $this->isOpen = true;
        $this->activeTab = 'gallery';
        $this->confirmingDelete = false;
        // $this->loadGallery(); // Odstraněno, načítá se v render()
        $this->reset('avatarFile', 'cropData', 'previewUrl');
    }

    public function close()
    {
        $this->isOpen = false;
        $this->confirmingDelete = false;
    }

    public function confirmDelete($userId = null)
    {
        if ($userId) {
            $this->userId = $userId;
        }
        $this->isOpen = true;
        $this->confirmingDelete = true;
    }

    public function deleteAvatar()
    {
        $user = \App\Models\User::find($this->userId) ?: auth()->user();
        if ($user->id !== auth()->id() && ! auth()->user()?->canAccessAdmin()) {
            abort(403);
        }
        $user->clearMediaCollection('avatar');
        $user->refresh();

        $this->dispatch('avatarUpdated',
            url: null,
            initials: $this->getInitials($user->name),
            userId: $user->id
        );

        $this->close();
        session()->flash('status', __('member.profile.avatar.flash.deleted'));
    }

    protected function getInitials($name)
    {
        $words = explode(' ', $name);
        if (count($words) >= 2) {
            return mb_strtoupper(mb_substr($words[0], 0, 1).mb_substr($words[count($words) - 1], 0, 1));
        }

        return mb_strtoupper(mb_substr($name, 0, 2));
    }

    public function saveAvatar($croppedImageBase64)
    {
        if (! $croppedImageBase64) {
            return;
        }

        $user = \App\Models\User::find($this->userId) ?: auth()->user();
        if ($user->id !== auth()->id() && ! auth()->user()?->canAccessAdmin()) {
            abort(403);
        }

        // Base64 to file
        $imageData = explode(',', $croppedImageBase64);
        if (count($imageData) < 2) {
            return;
        }

        $decodedImage = base64_decode($imageData[1]);
        $tempPath = 'temp/'.$this->userId.'_avatar_'.time().'.webp';
        Storage::disk('local')->put($tempPath, $decodedImage);
        $fullPath = storage_path('app/private/'.$tempPath);

        $user->addMedia($fullPath)
            ->usingFileName('avatar-'.time().'.webp')
            ->toMediaCollection('avatar');

        // Cleanup
        Storage::disk('local')->delete($tempPath);

        $user->refresh();
        $avatarUrl = $user->getAvatarUrl('thumb');
        if ($avatarUrl && ! str_contains($avatarUrl, 'default-avatar')) {
            $avatarUrl .= (str_contains($avatarUrl, '?') ? '&' : '?').'v='.time();
        }

        $this->dispatch('avatarUpdated',
            url: $avatarUrl,
            initials: $this->getInitials($user->name),
            userId: $user->id
        );

        $this->close();
        session()->flash('status', __('member.profile.avatar.flash.saved'));
    }

    public function render()
    {
        return view('livewire.member.avatar-modal', [
            'galleryAssets' => $this->getGalleryAssets(),
        ]);
    }
}
