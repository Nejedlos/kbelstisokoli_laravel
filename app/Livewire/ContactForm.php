<?php

namespace App\Livewire;

use App\Mail\ContactFormMail;
use App\Services\RecaptchaV3;
use App\Support\EmailObfuscator;
use Illuminate\Support\Facades\Mail;
use Livewire\Component;
use Livewire\WithFileUploads;

class ContactForm extends Component
{
    use WithFileUploads;

    public string $toEncoded = '';
    public string $toEmail = '';
    public string $name = '';
    public string $email = '';
    public string $subject = 'Zpráva z webu';
    public string $message = '';
    public $attachment;
    public ?string $recaptchaToken = null;

    public bool $success = false;
    public ?string $errorMessage = null;

    protected function rules(): array
    {
        return [
            'name' => 'required|string|min:2|max:255',
            'email' => 'required|email|max:255',
            'subject' => 'required|string|max:255',
            'message' => 'required|string|min:10',
            'attachment' => 'nullable|file|max:10240', // 10MB
        ];
    }

    public function mount(string $to = ''): void
    {
        $this->toEncoded = $to;
        $this->toEmail = EmailObfuscator::decode($to) ?? '';

        if (empty($this->toEmail)) {
            $this->errorMessage = 'Neplatný příjemce e-mailu.';
        }
    }

    public function submit(RecaptchaV3 $recaptchaService): void
    {
        if ($this->success) return;

        $this->validate();

        if (config('recaptcha.enabled')) {
            $result = $recaptchaService->verify($this->recaptchaToken ?? '', 'contact_form', request()->ip());
            if (!$result->passed) {
                $this->errorMessage = ($result->score !== null && $result->score < config('recaptcha.score_threshold'))
                    ? trans('recaptcha.low_score')
                    : trans('recaptcha.failed');
                return;
            }
        }

        if (empty($this->toEmail)) {
            $this->errorMessage = 'Příjemce e-mailu není nastaven.';
            return;
        }

        try {
            $attachmentPath = null;
            $attachmentName = null;
            $attachmentMime = null;

            if ($this->attachment) {
                $attachmentPath = $this->attachment->getRealPath();
                $attachmentName = $this->attachment->getClientOriginalName();
                $attachmentMime = $this->attachment->getMimeType();
            }

            Mail::to($this->toEmail)->send(new ContactFormMail(
                senderName: $this->name,
                senderEmail: $this->email,
                recipientEmail: $this->toEmail,
                messageBody: $this->message,
                subjectText: $this->subject,
                attachmentPath: $attachmentPath,
                attachmentName: $attachmentName,
                attachmentMime: $attachmentMime,
            ));

            $this->success = true;
            $this->reset(['name', 'email', 'message', 'attachment', 'recaptchaToken']);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Chyba při odesílání kontaktního formuláře: ' . $e->getMessage());
            $this->errorMessage = 'Při odesílání e-mailu došlo k chybě. Zkuste to prosím později.';
        }
    }

    public function render()
    {
        return view('livewire.contact-form');
    }
}
