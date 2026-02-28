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
            console.error('Cropper.js is not loaded yet. Retrying in 100ms...');
            setTimeout(() => this.setupCropper(image), 100);
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
            autoCropArea: 0.8,
            zoomable: true,
            scalable: true,
            ready: () => {
                console.log('Cropper is ready and active');
                if (this.cropper) {
                    this.cropper.crop();
                    // Zkusíme ještě vynutit zobrazení ořezového boxu
                    this.cropper.setCropBoxData({
                        left: 0,
                        top: 0,
                        width: 400,
                        height: 400
                    });
                }
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
        this.scrollToEditor();
        this.$nextTick(() => this.initCropper());
    },
    scrollToEditor() {
        this.$nextTick(() => {
            const editor = document.getElementById('avatar-editor-section');
            if (editor) {
                editor.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    }
}
"
x-show="isOpen"
x-cloak
class="fixed inset-0 z-50 overflow-y-auto"
@open-avatar-modal.window="isOpen = true; if($event.detail && $event.detail.userId) $wire.set('userId', $event.detail.userId)"
@openAvatarModal.window="isOpen = true; if($event.detail && $event.detail.userId) $wire.set('userId', $event.detail.userId)"
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
    <div class="relative min-h-screen flex items-center justify-center p-2 sm:p-4">
        <div class="relative w-full max-w-2xl bg-white rounded-[2rem] sm:rounded-[2.5rem] shadow-2xl overflow-hidden animate-in fade-in zoom-in duration-300 border border-slate-200/60" @click.stop>

            <!-- Header -->
            <div x-show="!confirmingDelete" class="p-5 sm:p-8 border-b border-slate-100 flex items-center justify-between bg-slate-50/30">
                <div>
                    <h3 class="text-xl sm:text-2xl font-black uppercase tracking-tight text-secondary leading-none">{{ __('member.profile.avatar.modal_title') }}</h3>
                    <p class="text-[10px] sm:text-[11px] text-slate-400 font-bold uppercase tracking-widest mt-2">{{ __('member.profile.avatar.modal_subtitle') }}</p>
                </div>
                <button @click="isOpen = false" class="w-10 h-10 rounded-xl bg-white border border-slate-200 text-slate-400 hover:text-rose-500 hover:border-rose-200 transition-all flex items-center justify-center shadow-sm shrink-0 ml-4">
                    <i class="fa-light fa-xmark text-xl"></i>
                </button>
            </div>

            <div x-show="!confirmingDelete" class="flex flex-col">
                <!-- 1. SECTION: EDITOR (Top) -->
                <div id="avatar-editor-section" class="p-5 sm:p-8 bg-slate-50/50 border-b border-slate-100 min-h-[300px] sm:min-h-[350px] flex flex-col items-center justify-center relative">
                    <div x-show="!previewUrl" class="text-center py-10 sm:py-16">
                        <div class="w-20 h-20 sm:w-28 sm:h-28 bg-white rounded-[1.5rem] sm:rounded-[2rem] flex items-center justify-center mx-auto mb-6 border border-slate-200 shadow-xl shadow-slate-200/50 transform rotate-3 group-hover:rotate-0 transition-transform">
                            <i class="fa-light fa-image-slash text-3xl sm:text-4xl text-slate-300"></i>
                        </div>
                        <p class="text-xs sm:text-sm font-black text-secondary uppercase tracking-[0.2em]">{{ __('member.profile.avatar.no_image_selected') }}</p>
                        <p class="text-[9px] sm:text-[10px] text-slate-400 mt-2 font-bold italic">{{ __('member.profile.avatar.no_image_hint') }}</p>
                    </div>

                    <div x-show="previewUrl" class="w-full space-y-4 sm:space-y-6 animate-in fade-in duration-500">
                        <div class="relative w-full rounded-[1.5rem] sm:rounded-[2rem] bg-slate-200 shadow-inner border border-slate-200 flex items-center justify-center p-2 overflow-hidden" style="min-height: 300px; max-height: 70vh;">
                            <div class="w-full max-h-[400px] sm:max-h-[500px] overflow-hidden flex items-center justify-center">
                                <img :src="previewUrl" id="cropper-image" class="max-w-full block">
                            </div>
                        </div>

                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 sm:gap-6">
                            <button @click="previewUrl = null; if(cropper) cropper.destroy(); cropper = null;"
                                    class="flex items-center justify-center sm:justify-start gap-2 px-5 py-2.5 text-[10px] sm:text-[11px] font-black uppercase tracking-widest text-rose-500 hover:bg-rose-50 rounded-xl transition-all">
                                <i class="fa-light fa-trash-can"></i> {{ __('member.profile.avatar.cancel_selection') }}
                            </button>

                            <div class="flex flex-col xs:flex-row gap-3 w-full sm:w-auto">
                                <button @click="initCropper()" class="btn btn-outline py-2.5 px-5 text-[9px] sm:text-[10px] uppercase tracking-widest w-full sm:w-auto bg-white border-slate-200">
                                    <i class="fa-light fa-crop-simple mr-2 text-primary"></i> {{ __('member.profile.avatar.reset_crop') }}
                                </button>
                                <button @click="saveCrop()" wire:loading.attr="disabled" wire:target="saveAvatar"
                                        class="btn btn-primary py-3 px-6 sm:px-10 text-[10px] sm:text-[11px] uppercase tracking-[0.15em] disabled:opacity-50 shadow-xl shadow-primary/30 w-full sm:w-auto">
                                    <i class="fa-light fa-spinner-third animate-spin mr-2" wire:loading wire:target="saveAvatar"></i>
                                    <i class="fa-light fa-check-double mr-2" wire:loading.remove wire:target="saveAvatar"></i>
                                    {{ __('member.profile.avatar.save_and_set') }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 2. SECTION: TABS (Middle) -->
                <div class="flex border-b border-slate-100 bg-white sticky top-0 z-10 p-2">
                    <div class="flex-1 flex bg-slate-100/80 rounded-2xl p-1 gap-1">
                        <button @click="activeTab = 'gallery'"
                                :class="activeTab === 'gallery' ? 'bg-white text-secondary shadow-sm ring-1 ring-slate-200' : 'text-slate-400 hover:text-slate-600'"
                                class="flex-1 py-3 text-[10px] font-black uppercase tracking-[0.2em] rounded-xl transition-all flex items-center justify-center">
                            <i class="fa-light fa-images mr-2"></i> {{ __('member.profile.avatar.tab_gallery') }}
                        </button>
                        <button @click="activeTab = 'upload'"
                                :class="activeTab === 'upload' ? 'bg-white text-secondary shadow-sm ring-1 ring-slate-200' : 'text-slate-400 hover:text-slate-600'"
                                class="flex-1 py-3 text-[10px] font-black uppercase tracking-[0.2em] rounded-xl transition-all flex items-center justify-center">
                            <i class="fa-light fa-cloud-arrow-up mr-2"></i> {{ __('member.profile.avatar.tab_upload') }}
                        </button>
                    </div>
                </div>

                <!-- 3. SECTION: SELECTION (Bottom) -->
                <div class="p-6 bg-white overflow-hidden">
                    <!-- Gallery Selection -->
                    <div x-show="activeTab === 'gallery'" class="space-y-4">
                        <div class="grid grid-cols-4 sm:grid-cols-5 gap-3 max-h-[450px] overflow-y-auto pr-2 custom-scrollbar">
                            @foreach($galleryAssets as $asset)
                                <button wire:key="asset-{{ $asset->id }}" @click="selectFromGallery('{{ $asset->getUrl() }}')"
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
                                <p class="text-[10px] text-slate-500 font-bold uppercase tracking-widest">{{ __('member.profile.avatar.gallery_empty') }}</p>
                            </div>
                        @endif
                    </div>

                    <!-- Upload Area -->
                    <div x-show="activeTab === 'upload'" class="space-y-4">
                        <div class="border-2 border-dashed border-slate-200 rounded-[2rem] p-12 text-center hover:border-primary/30 hover:bg-primary/5 transition-all cursor-pointer group relative overflow-hidden"
                             onclick="document.getElementById('avatar-upload-input').click()">
                            <div class="relative z-10">
                                <div class="w-16 h-16 bg-white text-primary rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-xl border border-slate-100 group-hover:scale-110 transition-transform duration-500">
                                    <i class="fa-light fa-camera-retro text-2xl"></i>
                                </div>
                                <h4 class="text-sm font-black text-secondary mb-2 uppercase tracking-widest">{{ __('member.profile.avatar.upload_click') }}</h4>
                                <p class="text-[10px] text-slate-400 font-bold italic">{{ __('member.profile.avatar.formats') }}</p>
                            </div>
                            <input type="file" id="avatar-upload-input" class="hidden" accept="image/*" @change="
                                const file = $event.target.files[0];
                                if (file) {
                                    const reader = new FileReader();
                                    reader.onload = (e) => {
                                        this.previewUrl = e.target.result;
                                        this.scrollToEditor();
                                        this.$nextTick(() => this.initCropper());
                                    };
                                    reader.readAsDataURL(file);
                                }
                            ">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Confirmation Delete (Sexy Basketball Spirit) -->
            <div x-show="confirmingDelete" x-cloak class="p-6 sm:p-10 text-center animate-in fade-in slide-in-from-bottom-8 duration-500">
                <div class="relative inline-block mb-8 sm:mb-12">
                    <div class="absolute inset-0 bg-primary/20 blur-3xl rounded-full animate-pulse"></div>
                    <div class="relative w-24 h-24 sm:w-32 sm:h-32 bg-white rounded-[2rem] sm:rounded-[2.5rem] flex items-center justify-center border border-slate-100 shadow-2xl transform -rotate-3">
                        <i class="fa-light fa-basketball text-5xl sm:text-7xl text-primary animate-bounce"></i>
                    </div>
                    <div class="absolute -top-3 -right-3 sm:-top-4 sm:-right-4 w-10 h-10 sm:w-14 sm:h-14 bg-rose-500 text-white rounded-xl sm:rounded-2xl flex items-center justify-center border-4 border-white shadow-xl transform rotate-12 z-10">
                        <i class="fa-light fa-hand text-xl sm:text-2xl"></i>
                    </div>
                </div>

                <h3 class="text-2xl sm:text-3xl font-black uppercase tracking-tight text-secondary mb-3 sm:mb-4 leading-none">{{ __('member.profile.avatar.timeout_title') }}</h3>
                <p class="text-xs sm:text-sm text-slate-500 mb-8 sm:mb-10 max-w-sm mx-auto font-bold italic leading-relaxed opacity-80">
                    {!! __('member.profile.avatar.timeout_text') !!}
                </p>

                <div class="flex flex-col xs:flex-row items-center justify-center gap-3 sm:gap-4">
                    <button @click="confirmingDelete = false" class="w-full xs:w-auto px-8 sm:px-10 py-3 sm:py-4 rounded-xl sm:rounded-2xl bg-slate-100 hover:bg-slate-200 text-secondary text-[10px] sm:text-[11px] font-black uppercase tracking-[0.2em] transition-all">
                        {{ __('member.profile.avatar.keep_in_game') }}
                    </button>
                    <button wire:click="deleteAvatar" wire:loading.attr="disabled"
                            class="w-full xs:w-auto px-10 sm:px-12 py-3 sm:py-4 rounded-xl sm:rounded-2xl bg-rose-500 hover:bg-rose-600 text-white text-[10px] sm:text-[11px] font-black uppercase tracking-[0.2em] transition-all shadow-xl shadow-rose-500/30 disabled:opacity-50 flex items-center justify-center">
                        <i class="fa-light fa-spinner-third animate-spin mr-2" wire:loading wire:target="deleteAvatar"></i>
                        {{ __('member.profile.avatar.send_to_bench') }}
                    </button>
                </div>
            </div>

            <!-- Footer info -->
            <div x-show="!confirmingDelete" class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex items-center gap-3">
                <i class="fa-light fa-circle-info text-primary"></i>
                <p class="text-[10px] text-slate-500 font-medium leading-tight">
                    {{ __('member.profile.avatar.info_footer') }}
                </p>
            </div>
        </div>
    </div>
</div>
