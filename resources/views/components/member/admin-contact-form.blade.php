<?php

use App\Models\Setting;
use App\Mail\FeedbackMessage;
use App\Mail\FeedbackConfirmation;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Mail;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;

new class extends Component {
    use WithFileUploads;

    public $subject = '';
    public $message = '';
    public $attachment;
    public $loading = false;
    public $success = false;

    public function getAdminContactProperty()
    {
        $settings = Setting::pluck('value', 'key')->toArray();
        return [
            'name' => $settings['admin_contact_name'] ?? __('member.feedback.contact_card.admin_name_default'),
            'email' => $settings['admin_contact_email'] ?? config('mail.error_reporting.email'),
            'phone' => $settings['admin_contact_phone'] ?? ($settings['contact_phone'] ?? null),
            'photo' => $settings['admin_contact_photo_path'] ?? null,
        ];
    }

    public function send()
    {
        $user = auth()->user();
        $this->validate([
            'subject' => 'required|string|min:5|max:200',
            'message' => 'required|string|min:10',
            'attachment' => 'nullable|file|max:10240|mimes:pdf,jpg,jpeg,png,doc,docx,xls,xlsx',
        ]);

        $this->loading = true;

        try {
            $adminEmail = Setting::where('key', 'admin_contact_email')->value('value') ?: config('mail.error_reporting.email');

            // Uložení přílohy
            $storedPath = null;
            if ($this->attachment) {
                $disk = config('filesystems.uploads.disk', 'public');
                $dir = trim(config('filesystems.uploads.dir', 'uploads'), '/').'/feedback';
                $storedPath = $this->attachment->store($dir, $disk);
            }

            $locale = App::getLocale();

            // Odeslání e-mailu adminovi
            if ($adminEmail) {
                Mail::to($adminEmail)
                    ->send(new FeedbackMessage(
                        type: 'admin',
                        user: $user,
                        subject: $this->subject,
                        message: $this->message,
                        attachmentDisk: $storedPath ? config('filesystems.uploads.disk', 'public') : null,
                        attachmentPath: $storedPath,
                        locale: $locale,
                    ));
            }

            // Potvrzení uživateli
            Mail::to($user->email)->send(new FeedbackConfirmation(
                type: 'admin',
                user: $user,
                subject: $this->subject,
                message: $this->message,
                locale: $locale,
            ));

            $this->success = true;
            $this->reset(['subject', 'message', 'attachment']);
        } catch (\Exception $e) {
            session()->flash('error', __('general.error_occurred') . ': ' . $e->getMessage());
        } finally {
            $this->loading = false;
        }
    }
};
?>

<div>
    @if($success)
        <div class="card p-10 text-center space-y-6 sport-card-accent">
            <div class="w-20 h-20 rounded-full bg-success-100 text-success-600 flex items-center justify-center mx-auto mb-4">
                <i class="fa-light fa-check text-4xl"></i>
            </div>
            <h2 class="text-2xl font-black text-secondary uppercase tracking-tight">{{ __('member.feedback.sent_success_title') ?? 'Zpráva odeslána' }}</h2>
            <p class="text-slate-600 max-w-md mx-auto">{{ __('member.feedback.sent_success') }}</p>
            <div class="pt-4">
                <a href="{{ route('member.dashboard') }}" class="btn btn-primary">
                    <i class="fa-light fa-house mr-2"></i> {{ __('nav.dashboard') }}
                </a>
            </div>
        </div>
    @else
        <div class="card sport-card-accent p-6 md:p-10">
            <form wire:submit.prevent="send" class="space-y-8">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div class="space-y-6">
                        <div class="space-y-2">
                            <label for="subject" class="text-xs font-black uppercase tracking-widest text-slate-400">{{ __('member.feedback.subject') }}</label>
                            <input id="subject" type="text" wire:model="subject"
                                   class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-club focus:ring-2 focus:ring-primary focus:border-primary transition-all font-bold text-secondary"
                                   placeholder="Např. Nefunkční tlačítko, problém s přihlášením..."
                                   required>
                            @error('subject')
                                <div class="text-danger-600 text-xs font-bold mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="space-y-2">
                            <div class="w-full" x-data="{ dragOver: false }">
                                <input id="attachment-input" type="file" wire:model="attachment" class="hidden" x-ref="fileInput" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx,.xls,.xlsx" />

                                <div @click.prevent="$refs.fileInput.click()"
                                     @dragover.prevent="dragOver = true"
                                     @dragleave.prevent="dragOver = false"
                                     @drop.prevent="dragOver = false; $refs.fileInput.files = $event.dataTransfer.files; $refs.fileInput.dispatchEvent(new Event('change', { bubbles: true }))"
                                     :class="dragOver ? 'border-primary bg-primary/5' : 'border-slate-200 bg-slate-50'"
                                     class="rounded-club border-2 border-dashed transition-colors cursor-pointer p-5 md:p-6 hover:border-primary/50">
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

                                    @if($attachment && !is_string($attachment))
                                        <div class="mt-4">
                                            <div class="flex items-center gap-3 p-3 rounded-xl bg-white border border-slate-200">
                                                <div class="w-10 h-10 rounded bg-slate-100 border border-slate-200 flex items-center justify-center text-slate-500">
                                                    <i class="fa-light fa-file"></i>
                                                </div>
                                                <div class="flex-1 min-w-0">
                                                    <div class="truncate text-sm font-bold text-secondary">{{ $attachment->getClientOriginalName() }}</div>
                                                    <div class="text-[10px] text-slate-400">{{ round($attachment->getSize() / 1024) }} KB</div>
                                                </div>
                                                <button type="button" wire:click="$set('attachment', null)" class="text-slate-400 hover:text-danger-600">
                                                    <i class="fa-light fa-xmark"></i>
                                                </button>
                                            </div>
                                        </div>
                                    @endif

                                    <div wire:loading wire:target="attachment" class="mt-2 text-xs text-primary font-bold">
                                        <i class="fa-light fa-spinner-third fa-spin mr-1"></i> Nahrávání...
                                    </div>
                                </div>
                            </div>
                            @error('attachment')
                                <div class="text-danger-600 text-xs font-bold mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="p-6 rounded-club bg-slate-50 border border-slate-100 space-y-3">
                            <div class="flex items-center gap-2 text-primary">
                                <i class="fa-light fa-circle-info"></i>
                                <span class="text-xs font-black uppercase tracking-widest">Kdy kontaktovat admina?</span>
                            </div>
                            <p class="text-[11px] text-slate-500 leading-relaxed font-medium">
                                Administrátora kontaktujte v případě technických potíží, chyb v systému nebo pokud potřebujete změnit nastavení svého účtu, které sami nemůžete upravit.
                            </p>
                        </div>
                    </div>

                    <div class="space-y-6">
                        @php $admin = $this->adminContact; @endphp
                        <div class="card p-4 border border-slate-200">
                            <div class="flex items-center gap-4">
                                <div class="w-16 h-16 rounded-xl bg-white border border-slate-200 flex items-center justify-center overflow-hidden shrink-0">
                                    @if(!empty($admin['photo']))
                                        <img src="{{ web_asset($admin['photo']) }}" alt="admin" class="w-full h-full object-contain bg-white">
                                    @else
                                        <i class="fa-light fa-user-gear text-2xl text-slate-400"></i>
                                    @endif
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="text-xs font-black uppercase tracking-widest text-slate-400">{{ __('member.feedback.contact_card.admin_title') }}</div>
                                    <div class="font-bold text-secondary">{{ $admin['name'] ?? __('member.feedback.contact_card.admin_name_default') }}</div>
                                    <div class="mt-1 text-sm text-slate-600 space-y-0.5">
                                        <div>
                                            <i class="fa-light fa-envelope text-primary mr-1.5"></i>
                                            @if(!empty($admin['email']))
                                                <x-mailto :email="$admin['email']" class="font-bold hover:underline" />
                                            @else
                                                <span class="text-slate-400">{{ __('member.feedback.contact_card.not_available') }}</span>
                                            @endif
                                        </div>
                                        <div>
                                            <i class="fa-light fa-phone text-primary mr-1.5"></i>
                                            @if(!empty($admin['phone']))
                                                <a href="tel:{{ $admin['phone'] }}" class="font-bold hover:underline">{{ $admin['phone'] }}</a>
                                            @else
                                                <span class="text-slate-400">{{ __('member.feedback.contact_card.not_available') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <label for="message" class="text-xs font-black uppercase tracking-widest text-slate-400">{{ __('member.feedback.message') }}</label>
                            <textarea id="message" wire:model="message" rows="10"
                                      class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-club focus:ring-2 focus:ring-primary focus:border-primary transition-all font-medium text-secondary"
                                      placeholder="Zde podrobně popište svůj požadavek nebo problém..."
                                      required></textarea>
                            @error('message')
                                <div class="text-danger-600 text-xs font-bold mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="pt-6 border-t border-slate-100 flex flex-col lg:flex-row items-center justify-between gap-6">
                    <div class="flex flex-col sm:flex-row items-center gap-4 w-full lg:w-auto">
                        <button type="submit" class="btn btn-primary w-full sm:w-auto sm:min-w-[200px]" wire:loading.class="is-loading" wire:target="send">
                            <i class="fa-light fa-paper-plane mr-2"></i> {{ __('member.feedback.send_to_admin') }}
                        </button>
                        <a href="{{ route('member.dashboard') }}" class="btn btn-outline py-3 px-6 text-sm w-full sm:w-auto">
                            <i class="fa-light fa-arrow-left mr-2"></i> {{ __('general.back') }}
                        </a>
                    </div>

                    <div class="flex items-center gap-2 text-slate-400">
                        <i class="fa-light fa-shield-check text-success-500"></i>
                        <span class="text-[10px] font-black uppercase tracking-widest italic">Zpráva bude bezpečně doručena</span>
                    </div>
                </div>
            </form>
        </div>
    @endif
</div>
