<div x-data="{
    isOpen: @entangle('isOpen'),
    confirmingDelete: @entangle('confirmingDelete'),
    activeTab: @entangle('activeTab'),
    cropper: null,
    previewUrl: null,
    initCropper() {
        if (!this.previewUrl) return;
        if (this.cropper) {
            this.cropper.destroy();
            this.cropper = null;
        }
        this.$nextTick(() => {
            const image = document.getElementById('cropper-image');
            if (!image) return;

            // Wait for image to load before init cropper
            if (image.complete) {
                this.setupCropper(image);
            } else {
                image.onload = () => this.setupCropper(image);
            }
        });
    },
    setupCropper(image) {
        if (typeof Cropper === 'undefined') {
            console.error('Cropper.js is not loaded yet.');
            return;
        }

        if (this.cropper) this.cropper.destroy();

        this.cropper = new Cropper(image, {
            aspectRatio: 1,
            viewMode: 1,
            dragMode: 'move',
            responsive: true,
            restore: false,
            guides: true,
            center: true,
            highlight: true,
            cropBoxMovable: true,
            cropBoxResizable: true,
            toggleDragModeOnDblclick: false,
            checkCrossOrigin: true,
            background: false,
            modal: true,
            autoCropArea: 1,
            zoomable: true,
            scalable: true,
            ready: () => {
                console.log('Cropper is ready and active');
                if (this.cropper) this.cropper.crop();
            }
        });
    },
    saveCrop() {
        if (!this.cropper) return;
        const canvas = this.cropper.getCroppedCanvas({
            width: 400,
            height: 400,
            imageSmoothingEnabled: true,
            imageSmoothingQuality: 'high',
        });
        const base64 = canvas.toDataURL('image/webp', 0.9);
        $wire.saveAvatar(base64);
    },
    selectFromGallery(url) {
        this.previewUrl = url;
        this.$nextTick(() => this.initCropper());
    }
}
"
x-show="isOpen"
x-cloak
class="fixed inset-0 z-50 overflow-y-auto"
@open-avatar-modal.window="isOpen = true"
@avatar-updated.window="
    const data = $event.detail;
    console.log('Avatar updated event received:', data);
    const topAvatar = document.getElementById('top-bar-avatar');
    const profileAvatar = document.getElementById('avatarPreview');
    const deleteBtn = document.getElementById('avatar-delete-btn');

    if (topAvatar) {
        if (data.url) {
            topAvatar.innerHTML = `<img src='${data.url}' class='w-full h-full object-cover rounded-full'>`;
        } else {
            topAvatar.innerHTML = `<div class='w-full h-full flex items-center justify-center bg-primary text-white font-black'>${data.initials}</div>`;
        }
    }

    if (profileAvatar) {
        profileAvatar.src = data.url || 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///ywAAAAAAQABAAACAUwAOw==';
    }

    if (deleteBtn) {
        if (data.url) {
            deleteBtn.classList.remove('hidden');
        } else {
            deleteBtn.classList.add('hidden');
        }
    }
"
>
    <style>[x-cloak] { display: none !important; }</style>
    <style>
        .cropper-line, .cropper-point {
            background-color: #e11d48 !important;
        }
        .cropper-view-box {
            outline: 3px solid #e11d48 !important;
            outline-color: rgba(225, 29, 72, 0.75) !important;
        }
        .cropper-face {
            background-color: transparent !important;
        }
    </style>
    <!-- Overlay -->
    <div class="fixed inset-0 bg-slate-900/80 backdrop-blur-sm transition-opacity" @click="isOpen = false"></div>

    <!-- Modal Content -->
    <div class="relative min-h-screen flex items-center justify-center p-4">
        <div class="relative w-full max-w-2xl bg-white rounded-3xl shadow-2xl overflow-hidden animate-in fade-in zoom-in duration-200" @click.stop>

            <!-- Header -->
            <div x-show="!confirmingDelete" class="p-6 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h3 class="text-xl font-black uppercase tracking-tight text-secondary">Správa avataru</h3>
                    <p class="text-xs text-slate-500 font-medium">Upravte svůj stávající nebo si vyberte zcela nový vzhled.</p>
                </div>
                <button @click="isOpen = false" class="p-2 text-slate-400 hover:text-secondary transition-colors">
                    <i class="fa-light fa-xmark text-xl"></i>
                </button>
            </div>

            <div x-show="!confirmingDelete" class="flex flex-col">
                <!-- 1. SECTION: EDITOR (Top) -->
                <div class="p-6 bg-slate-50/50 border-b border-slate-100 min-h-[300px] flex flex-col items-center justify-center">
                    <div x-show="!previewUrl" class="text-center py-12">
                        <div class="w-24 h-24 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-4 border-4 border-white shadow-sm">
                            <i class="fa-light fa-image-slash text-3xl text-slate-300"></i>
                        </div>
                        <p class="text-sm font-bold text-slate-400 uppercase tracking-widest">Zatím nebyl vybrán žádný obrázek</p>
                        <p class="text-[10px] text-slate-400 mt-1">Vyberte fotku z galerie nebo nahrajte vlastní níže.</p>
                    </div>

                    <div x-show="previewUrl" class="w-full space-y-4 animate-in fade-in duration-300">
                        <div class="relative w-full rounded-2xl bg-slate-200 shadow-inner border border-slate-200 flex items-center justify-center p-2" style="min-height: 350px;">
                            <div class="w-full max-h-[450px] overflow-hidden flex items-center justify-center">
                                <img :src="previewUrl" id="cropper-image" class="max-w-full block" crossorigin="anonymous" style="display: block; max-width: 100%;">
                            </div>
                        </div>

                        <div class="flex items-center justify-between gap-4">
                            <button @click="previewUrl = null; if(cropper) cropper.destroy(); cropper = null;"
                                    class="flex items-center gap-2 px-4 py-2 text-xs font-bold text-danger-600 hover:bg-danger-50 rounded-xl transition-colors">
                                <i class="fa-light fa-trash-can"></i> Zrušit výběr
                            </button>

                            <div class="flex gap-2">
                                <button @click="initCropper()" class="btn btn-outline py-2 px-4 text-[10px] uppercase tracking-widest">
                                    <i class="fa-light fa-crop-simple mr-2"></i> Reset ořezu
                                </button>
                                <button @click="saveCrop()" wire:loading.attr="disabled" wire:target="saveAvatar"
                                        class="btn btn-primary py-2 px-8 text-[10px] uppercase tracking-widest disabled:opacity-50 shadow-lg shadow-primary/20">
                                    <i class="fa-light fa-spinner-third animate-spin mr-2" wire:loading wire:target="saveAvatar"></i>
                                    <i class="fa-light fa-check-double mr-2" wire:loading.remove wire:target="saveAvatar"></i>
                                    Uložit a nastavit
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 2. SECTION: TABS (Middle) -->
                <div class="flex border-b border-slate-100 bg-white sticky top-0 z-10">
                    <button @click="activeTab = 'gallery'"
                            :class="activeTab === 'gallery' ? 'border-primary text-secondary' : 'border-transparent text-slate-400 hover:text-secondary'"
                            class="flex-1 py-4 text-[10px] font-black uppercase tracking-[0.2em] border-b-2 transition-all">
                        <i class="fa-light fa-images mr-2"></i> Klubová galerie
                    </button>
                    <button @click="activeTab = 'upload'"
                            :class="activeTab === 'upload' ? 'border-primary text-secondary' : 'border-transparent text-slate-400 hover:text-secondary'"
                            class="flex-1 py-4 text-[10px] font-black uppercase tracking-[0.2em] border-b-2 transition-all">
                        <i class="fa-light fa-cloud-arrow-up mr-2"></i> Nahrát vlastní
                    </button>
                </div>

                <!-- 3. SECTION: SELECTION (Bottom) -->
                <div class="p-6 bg-white overflow-hidden">
                    <!-- Gallery Selection -->
                    <div x-show="activeTab === 'gallery'" class="space-y-4">
                        <div class="grid grid-cols-4 sm:grid-cols-5 gap-3 max-h-[280px] overflow-y-auto pr-2 custom-scrollbar">
                            @foreach($galleryAssets as $asset)
                                <button @click="selectFromGallery('{{ $asset->getUrl() }}')"
                                        class="group relative aspect-square rounded-xl overflow-hidden border-2 transition-all"
                                        :class="previewUrl === '{{ $asset->getUrl() }}' ? 'border-primary ring-2 ring-primary/20' : 'border-slate-100 hover:border-primary/50'">
                                    <img src="{{ $asset->getUrl('thumb') }}" alt="" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                                    <div class="absolute inset-0 bg-secondary/40 flex items-center justify-center transition-opacity"
                                         :class="previewUrl === '{{ $asset->getUrl() }}' ? 'opacity-100' : 'opacity-0 group-hover:opacity-100'">
                                        <i class="fa-light fa-check text-white text-xl"></i>
                                    </div>
                                </button>
                            @endforeach
                        </div>

                        @if(count($galleryAssets) === 0)
                            <div class="py-8 text-center bg-slate-50 rounded-2xl border border-dashed border-slate-200">
                                <i class="fa-light fa-face-sad-sweat text-3xl text-slate-300 mb-3"></i>
                                <p class="text-[10px] text-slate-500 font-bold uppercase tracking-widest">Galerie je momentálně prázdná</p>
                            </div>
                        @endif
                    </div>

                    <!-- Upload Area -->
                    <div x-show="activeTab === 'upload'" class="space-y-4">
                        <div class="border-2 border-dashed border-slate-200 rounded-2xl p-10 text-center hover:border-primary/50 transition-colors cursor-pointer group bg-slate-50 hover:bg-white"
                             onclick="document.getElementById('avatar-upload-input').click()">
                            <div class="w-12 h-12 bg-primary/10 text-primary rounded-full flex items-center justify-center mx-auto mb-3 group-hover:scale-110 transition-transform">
                                <i class="fa-light fa-camera-retro text-xl"></i>
                            </div>
                            <h4 class="text-xs font-bold text-secondary mb-1 uppercase tracking-widest">Klikněte pro výběr fotky</h4>
                            <p class="text-[10px] text-slate-400">Podporujeme JPG, PNG, WebP (max 5MB)</p>
                            <input type="file" id="avatar-upload-input" class="hidden" accept="image/*" @change="
                                const file = $event.target.files[0];
                                if (file) {
                                    const reader = new FileReader();
                                    reader.onload = (e) => {
                                        previewUrl = e.target.result;
                                        $nextTick(() => initCropper());
                                    };
                                    reader.readAsDataURL(file);
                                }
                            ">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Confirmation Delete (Sexy Basketball Spirit) -->
            <div x-show="confirmingDelete" x-cloak class="p-8 text-center animate-in fade-in slide-in-from-bottom-4 duration-300">
                <div class="relative inline-block mb-12">
                    <div class="absolute inset-0 bg-primary/20 blur-2xl rounded-full animate-pulse"></div>
                    <div class="relative w-28 h-28 bg-white rounded-full flex items-center justify-center border-4 border-primary shadow-2xl">
                        <i class="fa-light fa-basketball text-6xl text-primary animate-bounce"></i>
                    </div>
                    <div class="absolute -top-3 -right-3 w-12 h-12 bg-rose-500 text-white rounded-full flex items-center justify-center border-4 border-white shadow-lg transform -rotate-12 z-10">
                        <i class="fa-light fa-hand text-xl"></i>
                    </div>
                </div>

                <h3 class="text-2xl font-black uppercase tracking-tight text-secondary mb-3">Time-out! Střídáme?</h3>
                <p class="text-sm text-slate-600 mb-8 max-w-sm mx-auto font-medium">
                    Opravdu chceš poslat svůj aktuální avatar <span class="text-primary font-black uppercase tracking-wider">na lavičku</span>? Po smazání uvidíš u svého jména pouze iniciály.
                </p>

                <div class="flex flex-col sm:flex-row items-center justify-center gap-3">
                    <button @click="confirmingDelete = false" class="w-full sm:w-auto px-8 py-3 rounded-2xl bg-slate-100 hover:bg-slate-200 text-secondary text-xs font-black uppercase tracking-widest transition-all">
                        Zůstává ve hře
                    </button>
                    <button wire:click="deleteAvatar" wire:loading.attr="disabled"
                            class="w-full sm:w-auto px-10 py-3 rounded-2xl bg-rose-500 hover:bg-rose-600 text-white text-xs font-black uppercase tracking-widest transition-all shadow-lg shadow-rose-500/20 disabled:opacity-50 flex items-center justify-center">
                        <i class="fa-light fa-spinner-third animate-spin mr-2" wire:loading wire:target="deleteAvatar"></i>
                        Poslat na střídačku
                    </button>
                </div>
            </div>

            <!-- Footer info -->
            <div x-show="!confirmingDelete" class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex items-center gap-3">
                <i class="fa-light fa-circle-info text-primary"></i>
                <p class="text-[10px] text-slate-500 font-medium leading-tight">
                    Avatar je vaše tvář v systému. Uvidí ho trenéři na soupisce a spoluhráči v přehledu docházky.
                </p>
            </div>
        </div>
    </div>
</div>
