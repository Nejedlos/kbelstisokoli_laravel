<?php

namespace App\Livewire\Member;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\MediaAsset;
use App\Support\Media\VirtualAvatarAsset;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class AvatarModal extends Component
{
    use WithFileUploads;

    public $isOpen = false;
    public $activeTab = 'gallery'; // 'upload' | 'gallery'
    public $avatarFile;
    public $cropData;
    // public $galleryAssets = []; // Odstraněno - nyní se načítá dynamicky v render() pro snížení zátěže Livewire stavu

    public $confirmingDelete = false;
    public $userId;

    protected $listeners = [
        'openAvatarModal' => 'open',
        'deleteAvatar' => 'confirmDelete'
    ];

    public function mount($userId = null)
    {
        $this->userId = $userId ?: auth()->id();
        // $this->loadGallery(); // Odstraněno, načítá se v render()
    }

    public function getGalleryAssets()
    {
        // 1. Pokus o načtení z databáze (systémové avatary i photo pooly)
        $query = MediaAsset::query()
            ->where(function($query) {
                $query->whereNull('uploaded_by_id')
                    ->orWhere('title', 'like', 'Default Avatar%');
            })
            ->where('is_public', true);

        $assets = $query
            ->latest('id')
            ->limit(1000)
            ->get();

        // 2. Fallback: Pokud je DB prázdná, prohledáme přímo disk (pro případ, že jsou soubory synchronizovány bez DB)
        if ($assets->isEmpty()) {
            $assets = $this->loadGalleryFromDisk();
        }

        // 3. Logování, pokud je galerie stále prázdná
        if ($assets->isEmpty()) {
            \Illuminate\Support\Facades\Log::warning("AvatarModal: Galerie je prázdná i po pokusu o načtení z disku.");
        }

        return $assets;
    }

    /**
     * Načte avatary přímo ze složky public/uploads/defaults, pokud chybí DB záznamy.
     */
    protected function loadGalleryFromDisk()
    {
        $path = public_path('uploads/defaults');
        if (!is_dir($path)) {
            return collect();
        }

        $assets = collect();
        // Získáme složky, které jsou pojmenované čísly (ID media)
        $directories = File::directories($path);

        foreach ($directories as $dir) {
            $mediaId = basename($dir);
            if (!is_numeric($mediaId)) continue;

            $files = File::files($dir);
            if (empty($files)) continue;

            // Najdeme hlavní soubor (první obrázek v rootu složky ID)
            $mainFile = null;
            foreach ($files as $file) {
                if (in_array(strtolower($file->getExtension()), ['jpg', 'jpeg', 'png', 'webp'])) {
                    $mainFile = $file;
                    break;
                }
            }
            if (!$mainFile) continue;

            $fileName = $mainFile->getFilename();
            $mainUrl = asset('uploads/defaults/' . $mediaId . '/' . $fileName);

            // Náhled (zkusíme najít v podadresáři conversions)
            $thumbUrl = $mainUrl; // Fallback na originál
            $conversionsPath = $dir . '/conversions';
            if (is_dir($conversionsPath)) {
                $thumbFiles = File::files($conversionsPath);
                foreach ($thumbFiles as $thumb) {
                    // Spatie obvykle přidává suffix -thumb nebo -optimized
                    $tName = $thumb->getFilename();
                    if (str_contains($tName, 'thumb') || str_contains($tName, 'optimized') || str_contains($tName, 'preview')) {
                        $thumbUrl = asset('uploads/defaults/' . $mediaId . '/conversions/' . $tName);
                        break;
                    }
                }
            }

            // Vytvoříme regulérní objekt simulující MediaAsset model (aby šel serializovat Livewirem)
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
        $this->reset('avatarFile', 'cropData');
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
            return mb_strtoupper(mb_substr($words[0], 0, 1) . mb_substr($words[count($words) - 1], 0, 1));
        }
        return mb_strtoupper(mb_substr($name, 0, 2));
    }

    public function saveAvatar($croppedImageBase64)
    {
        if (!$croppedImageBase64) return;

        // Base64 to file
        $imageData = explode(',', $croppedImageBase64);
        if (count($imageData) < 2) return;

        $decodedImage = base64_decode($imageData[1]);
        $tempPath = 'temp/' . $this->userId . '_avatar_' . time() . '.webp';
        Storage::disk('local')->put($tempPath, $decodedImage);
        $fullPath = storage_path('app/private/' . $tempPath);

        $user = \App\Models\User::find($this->userId) ?: auth()->user();
        $user->addMedia($fullPath)
            ->usingFileName('avatar-' . time() . '.webp')
            ->toMediaCollection('avatar');

        // Cleanup
        Storage::disk('local')->delete($tempPath);

        $user->refresh();
        $avatarUrl = $user->getAvatarUrl('thumb');
        if ($avatarUrl && !str_contains($avatarUrl, 'default-avatar')) {
            $avatarUrl .= (str_contains($avatarUrl, '?') ? '&' : '?') . 'v=' . time();
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
            'galleryAssets' => $this->getGalleryAssets()
        ]);
    }
}
