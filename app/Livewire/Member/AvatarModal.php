<?php

namespace App\Livewire\Member;

use App\Models\MediaAsset;
use App\Support\Media\VirtualAvatarAsset;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class AvatarModal extends Component
{
    use WithFileUploads;

    public $isOpen = false;

    public $activeTab = 'gallery'; // 'upload' | 'gallery'

    public $avatarFile = []; // Nyní pole pro podporu hromadného importu

    public $previewUrl;

    public $cropData;
    // public $galleryAssets = []; // Odstraněno - nyní se načítá dynamicky v render() pro snížení zátěže Livewire stavu

    public $confirmingDelete = false;

    public $confirmingSystemDelete = null; // ID of system avatar to delete

    public $uploadAsSystem = false;

    public $userId;


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
            $relativeDir = str_replace($path.'/'.$mediaId.'/', '', $mainFile->getPath().'/');
            if ($relativeDir === $mainFile->getPath().'/') { // Fallback pokud str_replace selže
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

    public function updatedAvatarFile()
    {
        if (empty($this->avatarFile)) {
            return;
        }

        // Validace souborů hned po nahrání (před generováním preview)
        $this->validate([
            'avatarFile.*' => ['image', 'max:10240'], // Max 10MB
        ]);

        // Pokud je nahráváno jako systémový avatar a je vybráno více souborů, neřešíme ořez (automatický resize)
        if ($this->uploadAsSystem && is_array($this->avatarFile) && count($this->avatarFile) > 1) {
            $this->previewUrl = null; // Náhled u hromadného importu neřešíme (nebo jen indikaci)

            return;
        }

        // Jinak (jeden soubor) funguje standardní ořez
        $file = is_array($this->avatarFile) ? end($this->avatarFile) : $this->avatarFile;
        $this->previewUrl = $file->temporaryUrl();
    }

    #[On('openAvatarModal')]
    public function open($userId = null)
    {
        if ($userId) {
            $this->userId = $userId;
        }
        $this->isOpen = true;
        $this->activeTab = 'gallery';
        $this->confirmingDelete = false;
        $this->confirmingSystemDelete = null;
        $this->uploadAsSystem = false;
        // $this->loadGallery(); // Odstraněno, načítá se v render()
        $this->reset('avatarFile', 'cropData', 'previewUrl');
    }

    #[On('deleteAvatar')]
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
        if (! $user || ($user->id !== auth()->id() && ! auth()->user()?->canAccessAdmin())) {
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

    public function saveAvatar($croppedImageBase64 = null)
    {
        // 1. Hromadný import jako systémový avatar (pokud jsou vybrány soubory a skip ořezu)
        if ($this->uploadAsSystem && is_array($this->avatarFile) && count($this->avatarFile) > 1) {
            $this->saveSystemAvatarsBulk();

            return;
        }

        // 2. Standardní cesta s ořezem (jednotlivé nahrávání)
        if (! $croppedImageBase64) {
            return;
        }

        if ($this->uploadAsSystem) {
            $this->saveSystemAvatar($croppedImageBase64);

            return;
        }

        $user = \App\Models\User::find($this->userId) ?: auth()->user();
        if (! $user || ($user->id !== auth()->id() && ! auth()->user()?->canAccessAdmin())) {
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
        $fullPath = Storage::disk('local')->path($tempPath);

        $user->addMedia($fullPath)
            ->usingFileName('avatar-'.time().'.webp')
            ->toMediaCollection('avatar');

        // Cleanup
        Storage::disk('local')->delete($tempPath);

        $user->refresh();
        $avatarUrl = $user->getAvatarUrl();
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

    protected function saveSystemAvatarsBulk()
    {
        if (! auth()->user()?->canAccessAdmin() || empty($this->avatarFile)) {
            return;
        }

        $count = 0;
        foreach ($this->avatarFile as $file) {
            $this->processAndSaveSystemAvatar($file->getRealPath());
            $count++;
        }

        $this->reset(['avatarFile', 'uploadAsSystem', 'previewUrl']);
        $this->activeTab = 'gallery';

        session()->flash('status', __('member.profile.avatar.admin.flash.system_saved_bulk', ['count' => $count]));
    }

    protected function processAndSaveSystemAvatar($sourcePath)
    {
        // 1. Nejprve vytvoříme dočasný WebP soubor pro výpočet MD5 a kontrolu duplicity
        $tempFile = tempnam(sys_get_temp_dir(), 'avatar_').'.webp';
        $this->resizeToWebp($sourcePath, $tempFile, 1000, 1000); // Zvětšeno pro kvalitní hlavní avatar

        if (! file_exists($tempFile)) {
            return;
        }

        $newMd5 = md5_file($tempFile);
        $path = public_path('uploads/defaults');

        // 2. Kontrola duplicity proti existujícím souborům v galerii
        if (File::exists($path)) {
            $existingFiles = File::allFiles($path);
            foreach ($existingFiles as $file) {
                // Kontrolujeme jen hlavní avatary, ne konverze/thumb
                if (! str_contains($file->getRelativePathname(), 'conversions') && $file->getExtension() === 'webp') {
                    if (md5_file($file->getRealPath()) === $newMd5) {
                        @unlink($tempFile);

                        return; // Duplikát nalezen, přeskakujeme
                    }
                }
            }
        }

        // 3. Generujeme ID (složku)
        $directories = File::directories($path);
        $maxId = 0;
        foreach ($directories as $dir) {
            $id = basename($dir);
            if (is_numeric($id) && $id > $maxId) {
                $maxId = $id;
            }
        }
        $newId = $maxId + 1;
        $newPath = $path.'/'.$newId;
        $conversionsPath = $newPath.'/conversions';

        File::makeDirectory($conversionsPath, 0755, true);

        $fileName = 'avatar-'.time().'-'.uniqid().'.webp';
        $thumbName = str_replace('.webp', '-thumb.webp', $fileName);

        // 4. Přesuneme dočasný soubor na finální místo a vytvoříme thumb
        File::move($tempFile, $newPath.'/'.$fileName);
        $this->resizeToWebp($sourcePath, $conversionsPath.'/'.$thumbName, 200, 200); // Thumb ve 200x200
    }

    protected function resizeToWebp($sourcePath, $targetPath, $width, $height)
    {
        $info = getimagesize($sourcePath);
        if (! $info) {
            return;
        }

        $mime = $info['mime'];
        $src = match ($mime) {
            'image/jpeg' => imagecreatefromjpeg($sourcePath),
            'image/png' => imagecreatefrompng($sourcePath),
            'image/webp' => imagecreatefromwebp($sourcePath),
            'image/gif' => imagecreatefromgif($sourcePath),
            default => null,
        };

        if (! $src) {
            return;
        }

        $srcW = imagesx($src);
        $srcH = imagesy($src);

        // Square crop (center)
        $minSize = min($srcW, $srcH);
        $srcX = ($srcW - $minSize) / 2;
        $srcY = ($srcH - $minSize) / 2;

        $dst = imagecreatetruecolor($width, $height);

        // Preserve transparency for PNGs if needed, though we output WebP
        imagealphablending($dst, false);
        imagesavealpha($dst, true);
        $transparent = imagecolorallocatealpha($dst, 255, 255, 255, 127);
        imagefilledrectangle($dst, 0, 0, $width, $height, $transparent);
        imagealphablending($dst, true);

        imagecopyresampled($dst, $src, 0, 0, $srcX, $srcY, $width, $height, $minSize, $minSize);

        imagewebp($dst, $targetPath, 85);

        imagedestroy($src);
        imagedestroy($dst);
    }

    protected function saveSystemAvatar($croppedImageBase64)
    {
        if (! auth()->user()?->canAccessAdmin()) {
            abort(403);
        }

        // Base64 to file
        $imageData = explode(',', $croppedImageBase64);
        if (count($imageData) < 2) {
            return;
        }

        $decodedImage = base64_decode($imageData[1]);

        // 1. Kontrola duplicity (MD5) proti existujícím souborům v galerii
        $newMd5 = md5($decodedImage);
        $path = public_path('uploads/defaults');
        if (File::exists($path)) {
            $existingFiles = File::allFiles($path);
            foreach ($existingFiles as $file) {
                // Kontrolujeme jen hlavní avatary, ne konverze/thumb
                if (! str_contains($file->getRelativePathname(), 'conversions') && $file->getExtension() === 'webp') {
                    if (md5_file($file->getRealPath()) === $newMd5) {
                        return; // Duplikát nalezen, přeskakujeme
                    }
                }
            }
        }

        // 2. Generujeme ID (složku)
        $directories = File::directories($path);
        $maxId = 0;
        foreach ($directories as $dir) {
            $id = basename($dir);
            if (is_numeric($id) && $id > $maxId) {
                $maxId = $id;
            }
        }
        $newId = $maxId + 1;
        $newPath = $path.'/'.$newId;
        $conversionsPath = $newPath.'/conversions';

        File::makeDirectory($conversionsPath, 0755, true);

        $fileName = 'avatar-'.time().'.webp';
        $thumbName = 'avatar-'.time().'-thumb.webp';

        // 3. Uložíme hlavní obrázek (ořezaný originál z frontendu - cca 1200x1200px)
        // Omezíme jej v PHP na max 1000x1000 pro úsporu místa a dostatečnou kvalitu
        $tempCroppedFile = tempnam(sys_get_temp_dir(), 'cropped_').'.webp';
        File::put($tempCroppedFile, $decodedImage);

        // Uložíme jako hlavní (1000x1000) a jako thumb (200x200)
        $this->resizeToWebp($tempCroppedFile, $newPath.'/'.$fileName, 1000, 1000);
        $this->resizeToWebp($tempCroppedFile, $conversionsPath.'/'.$thumbName, 200, 200);

        @unlink($tempCroppedFile);

        $this->uploadAsSystem = false;
        $this->previewUrl = null;
        $this->activeTab = 'gallery';

        session()->flash('status', __('member.profile.avatar.admin.flash.system_saved'));
    }

    public function render()
    {
        return view('livewire.member.avatar-modal', [
            'galleryAssets' => $this->getGalleryAssets(),
        ]);
    }
}
