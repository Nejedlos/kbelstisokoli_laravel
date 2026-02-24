@props([
    'name' => 'attachment',
    'id' => null,
    'accept' => '.pdf,.jpg,.jpeg,.png,.doc,.docx,.xls,.xlsx',
    'maxSizeMb' => 10,
    'multiple' => false,
])

@php
    $inputId = $id ?: $name.'-input';
@endphp

<div x-data="dropzoneComponent({ inputId: '{{ $inputId }}', maxSizeMb: {{ (int) $maxSizeMb }}, accept: '{{ $accept }}', multiple: {{ $multiple ? 'true' : 'false' }} })"
     x-init="init()"
     class="w-full">
    <input id="{{ $inputId }}" type="file" name="{{ $name }}{{ $multiple ? '[]' : '' }}"
           x-ref="fileInput"
           {{ $multiple ? 'multiple' : '' }}
           accept="{{ $accept }}"
           class="hidden" />

    <div @click.prevent="$refs.fileInput.click()"
         @dragover.prevent="dragOver = true"
         @dragleave.prevent="dragOver = false"
         @drop.prevent="handleDrop($event)"
         :class="dragOver ? 'border-primary bg-primary/5' : 'border-slate-200 bg-slate-50'"
         class="rounded-club border-2 border-dashed transition-colors cursor-pointer p-5 md:p-6">
        <div class="flex flex-col sm:flex-row sm:items-center gap-4">
            <div class="flex items-center gap-4 flex-1 min-w-0">
                <div class="w-10 h-10 rounded-full bg-white border border-slate-200 flex items-center justify-center text-slate-500 shrink-0">
                    <i class="fa-light fa-cloud-arrow-up"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="text-sm font-bold text-secondary truncate">
                        {{ __('member.feedback.attachment') }}
                    </div>
                    <div class="text-[11px] text-slate-500 font-medium leading-tight">
                        {{ __('member.feedback.attachment_help') }}
                    </div>
                </div>
            </div>
            <div class="w-full sm:w-auto shrink-0">
                <span class="btn btn-outline py-2 px-4 text-xs w-full sm:w-auto justify-center">
                    <i class="fa-light fa-file-plus mr-1.5"></i> {{ __('general.choose') ?? 'Vybrat' }}
                </span>
            </div>
        </div>

        <template x-if="files.length > 0">
            <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-3">
                <template x-for="(file, idx) in files" :key="idx">
                    <div class="flex items-center gap-3 p-3 rounded-xl bg-white border border-slate-200">
                        <template x-if="file.isImage">
                            <img :src="file.preview" alt="preview" class="w-10 h-10 rounded object-cover border border-slate-200" />
                        </template>
                        <template x-if="!file.isImage">
                            <div class="w-10 h-10 rounded bg-slate-100 border border-slate-200 flex items-center justify-center text-slate-500">
                                <i class="fa-light fa-file"></i>
                            </div>
                        </template>
                        <div class="flex-1 min-w-0">
                            <div class="truncate text-sm font-bold text-secondary" x-text="file.name"></div>
                            <div class="text-[10px] text-slate-400" x-text="file.sizeLabel"></div>
                        </div>
                        <button type="button" @click.stop="remove(idx)" class="text-slate-400 hover:text-danger-600">
                            <i class="fa-light fa-xmark"></i>
                        </button>
                    </div>
                </template>
            </div>
        </template>

        <template x-if="error">
            <div class="mt-3 text-danger-600 text-xs font-bold" x-text="error"></div>
        </template>
    </div>
</div>

@push('scripts')
<script>
    window.dropzoneComponent = function ({ inputId, maxSizeMb = 10, accept = '', multiple = false }) {
        return {
            dragOver: false,
            files: [],
            error: '',
            init() {
                const input = this.$refs.fileInput;
                input.addEventListener('change', (e) => {
                    this.setFiles(e.target.files);
                });
            },
            handleDrop(e) {
                this.dragOver = false;
                const dt = e.dataTransfer;
                if (!dt || !dt.files) return;
                this.setFiles(dt.files);
            },
            setFiles(fileList) {
                this.error = '';
                const valid = [];
                const max = maxSizeMb * 1024 * 1024;
                const acceptList = accept.split(',').map(s => s.trim().toLowerCase()).filter(Boolean);

                for (const f of fileList) {
                    if (f.size > max) {
                        this.error = `Soubor "${f.name}" přesahuje povolenou velikost ${maxSizeMb} MB.`;
                        continue;
                    }
                    if (acceptList.length) {
                        const ext = '.' + (f.name.split('.').pop() || '').toLowerCase();
                        const typeOk = acceptList.includes(ext) || acceptList.includes(f.type.toLowerCase());
                        if (!typeOk) {
                            this.error = `Soubor "${f.name}" není v povoleném formátu.`;
                            continue;
                        }
                    }
                    valid.push(f);
                    if (!multiple) break; // jen jeden soubor
                }

                if (!valid.length) {
                    this.files = [];
                    this.$refs.fileInput.value = '';
                    return;
                }

                // Naplnit nativní input programově (pro submit formuláře)
                const dt = new DataTransfer();
                valid.forEach(v => dt.items.add(v));
                this.$refs.fileInput.files = dt.files;

                // Připravit metadata pro UI
                this.files = valid.map((f) => ({
                    name: f.name,
                    sizeLabel: this.humanSize(f.size),
                    isImage: f.type.startsWith('image/'),
                    preview: f.type.startsWith('image/') ? URL.createObjectURL(f) : null,
                }));
            },
            remove(idx) {
                if (idx < 0 || idx >= this.files.length) return;
                const current = Array.from(this.$refs.fileInput.files);
                current.splice(idx, 1);

                const dt = new DataTransfer();
                current.forEach(v => dt.items.add(v));
                this.$refs.fileInput.files = dt.files;

                this.files.splice(idx, 1);
                if (this.files.length === 0) this.$refs.fileInput.value = '';
            },
            humanSize(bytes) {
                const thresh = 1024;
                if (Math.abs(bytes) < thresh) {
                    return bytes + ' B';
                }
                const units = ['KB','MB','GB','TB'];
                let u = -1;
                do {
                    bytes /= thresh;
                    ++u;
                } while (Math.abs(bytes) >= thresh && u < units.length - 1);
                return bytes.toFixed(1)+' '+units[u];
            }
        }
    }
</script>
@endpush
