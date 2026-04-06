<?php

namespace App\Livewire;

use App\Mail\RecruitmentFormMail;
use App\Models\Lead;
use App\Models\Setting;
use App\Models\Team;
use App\Services\RecaptchaV3;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Livewire\Attributes\Url;
use Livewire\Attributes\Validate;
use Livewire\Component;

class RecruitmentForm extends Component
{
    #[Validate]
    public string $name = '';

    #[Validate]
    public string $email = '';

    #[Url(as: 'team')]
    #[Validate]
    public string $selectedTeam = 'muzi-c'; // Výchozí tým

    #[Validate]
    public ?int $height = null;

    #[Validate]
    public string $position = '';

    #[Validate]
    public string $level = '';

    #[Validate]
    public ?int $age = null;

    #[Validate]
    public string $message = '';

    public ?string $recaptchaToken = null;

    public bool $success = false;

    public ?string $errorMessage = null;

    public function rules(): array
    {
        return [
            'name' => 'required|string|min:2|max:255',
            'email' => 'required|email|max:255',
            'selectedTeam' => 'required|string|exists:teams,slug',
            'height' => 'nullable|integer|min:100|max:250',
            'position' => 'nullable|string|max:50',
            'level' => 'nullable|string|max:255',
            'age' => 'nullable|integer|min:15|max:99',
            'message' => 'required|string|min:10',
        ];
    }

    public function mount(?string $team = null): void
    {
        if ($team && Team::where('slug', $team)->exists()) {
            $this->selectedTeam = $team;
        }

        // Předvyplnění zprávy pro snazší vyplnění (user request: "formulář nějak předvyplněný")
        $this->message = __('recruitment.form.default_message');
    }

    public function updated($propertyName): void
    {
        $this->validateOnly($propertyName);
    }

    protected function validationAttributes(): array
    {
        return [
            'name' => __('user.fields.first_name'),
            'email' => __('user.fields.email'),
            'selectedTeam' => __('recruitment.form.fields.team'),
            'height' => __('user.fields.height_cm'),
            'position' => __('user.fields.position'),
            'level' => __('recruitment.form.fields.level'),
            'age' => __('recruitment.form.fields.age'),
            'message' => __('recruitment.form.fields.message'),
        ];
    }

    public function submit(RecaptchaV3 $recaptchaService): void
    {
        if ($this->success) {
            return;
        }

        $this->validate();

        if (config('recaptcha.enabled')) {
            $result = $recaptchaService->verify($this->recaptchaToken ?? '', 'recruitment_form', request()->ip());
            if (! $result->passed) {
                $this->errorMessage = ($result->score !== null && $result->score < config('recaptcha.score_threshold'))
                    ? trans('recaptcha.low_score')
                    : trans('recaptcha.failed');

                return;
            }
        }

        try {
            $team = Team::where('slug', $this->selectedTeam)->first();

            // 1. Uložit do databáze jako Lead
            $lead = Lead::create([
                'type' => 'recruitment',
                'name' => $this->name,
                'email' => $this->email,
                'message' => $this->message,
                'payload' => [
                    'team_slug' => $this->selectedTeam,
                    'team_name' => $team?->getTranslation('name', 'cs'),
                    'height' => $this->height,
                    'position' => $this->position,
                    'level' => $this->level,
                    'age' => $this->age,
                ],
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            // 2. Připravit e-mail
            $teamName = $team ? $team->getTranslation('name', app()->getLocale()) : strtoupper($this->selectedTeam);
            $mailable = new RecruitmentFormMail(
                senderName: $this->name,
                senderEmail: $this->email,
                teamName: $teamName,
                messageBody: $this->message,
                subjectText: __('recruitment.form.email_subject', ['team' => $teamName, 'name' => $this->name]),
                extraData: [
                    'height' => $this->height,
                    'position' => $this->position,
                    'level' => $this->level,
                    'age' => $this->age,
                ],
                leadId: $lead->id
            );

            // 3. Odeslat příjemcům (Admin + Trenéři)
            $recipients = $this->getRecipients($team);
            if (! empty($recipients)) {
                Mail::to($recipients)->send($mailable);
            }

            $this->success = true;
            $this->reset(['name', 'email', 'message', 'recaptchaToken', 'height', 'position', 'level', 'age']);

        } catch (\Exception $e) {
            Log::error('Chyba při odesílání náborového formuláře: '.$e->getMessage());
            $this->errorMessage = __('recruitment.form.error_sending');
        }
    }

    protected function getRecipients(?Team $team): array
    {
        $emails = [];

        // 1. Admin e-maily
        $adminEmail = Setting::where('key', 'admin_contact_email')->value('value');
        if (! $adminEmail) {
            $adminEmail = Setting::where('key', 'contact_email')->value('value');
        }

        if ($adminEmail) {
            if (is_array($adminEmail)) {
                $emails[] = $adminEmail[app()->getLocale()] ?? reset($adminEmail);
            } else {
                $emails[] = (string) $adminEmail;
            }
        }

        // 2. Trenéři týmu
        if ($team) {
            foreach ($team->coaches as $coach) {
                // Pivot email
                if ($coach->pivot && $coach->pivot->email) {
                    $emails[] = $coach->pivot->email;
                } elseif ($coach->email) {
                    $emails[] = $coach->email;
                }
            }
        }

        // Unikátní a pročištěné e-maily
        $emails = array_unique(array_filter($emails));

        // Fallback pokud je pole prázdné
        if (empty($emails)) {
            $emails[] = 'nejedlymi@gmail.com';
        }

        return $emails;
    }

    public function render()
    {
        return view('livewire.recruitment-form', [
            'teams' => Team::where('category', 'senior')
                ->orderBy('slug')
                ->get()
                ->mapWithKeys(fn ($team) => [$team->slug => $team->getTranslation('name', app()->getLocale())])
                ->toArray(),
        ]);
    }
}
