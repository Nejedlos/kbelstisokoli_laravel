<?php

namespace App\Livewire\Member;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\MediaAsset;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class AvatarModal extends Component
{
    use WithFileUploads;

    public $isOpen = false;
    public $activeTab = 'gallery'; // 'upload' | 'gallery'
    public $avatarFile;
    public $cropData;
    public $galleryAssets = [];

    public $confirmingDelete = false;
    public $userId;

    protected $listeners = [
        'openAvatarModal' => 'open',
        'deleteAvatar' => 'confirmDelete'
    ];

    public function mount($userId = null)
    {
        $this->userId = $userId ?: auth()->id();
        $this->loadGallery();
    }

    public function loadGallery()
    {
        $this->galleryAssets = MediaAsset::query()
            ->where('is_public', true)
            ->latest('id')
            ->limit(200)
            ->get();
    }

    public function open($userId = null)
    {
        if ($userId) {
            $this->userId = $userId;
        }
        $this->isOpen = true;
        $this->activeTab = 'gallery';
        $this->confirmingDelete = false;
        $this->loadGallery();
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
        $avatarUrl = $user->getFirstMediaUrl('avatar', 'thumb');
        if ($avatarUrl) {
            $avatarUrl .= (strpos($avatarUrl, '?') === false ? '?' : '&') . 'v=' . time();
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
        return view('livewire.member.avatar-modal');
    }
}
