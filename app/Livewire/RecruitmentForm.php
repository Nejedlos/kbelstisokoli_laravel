<?php

namespace App\Livewire;

use App\Mail\RecruitmentFormMail;
use App\Models\Setting;
use App\Models\Team;
use App\Services\RecaptchaV3;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Livewire\Attributes\Url;
use Livewire\Component;

class RecruitmentForm extends Component
{
    public string $name = '';
    public string $email = '';

    #[Url(as: 'team')]
    public string $selectedTeam = 'muzi-c'; // Výchozí tým

    public ?int $height = null;
    public string $position = '';
    public string $level = '';
    public ?int $age = null;

    public string $message = '';
    public ?string $recaptchaToken = null;

    public bool $success = false;
    public ?string $errorMessage = null;

    protected function rules(): array
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
        $this->message = "Dobrý den,\n\nměl bych zájem o hraní ve vašem týmu. Mám za sebou zkušenosti z...";
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
            'selectedTeam' => 'tým',
            'height' => __('user.fields.height_cm'),
            'position' => __('user.fields.position'),
            'level' => 'úroveň',
            'age' => 'věk',
            'message' => 'zpráva',
        ];
    }

    public function submit(RecaptchaV3 $recaptchaService): void
    {
        if ($this->success) return;

        $this->validate();

        if (config('recaptcha.enabled')) {
            $result = $recaptchaService->verify($this->recaptchaToken ?? '', 'recruitment_form', request()->ip());
            if (!$result->passed) {
                $this->errorMessage = ($result->score !== null && $result->score < config('recaptcha.score_threshold'))
                    ? trans('recaptcha.low_score')
                    : trans('recaptcha.failed');
                return;
            }
        }

        try {
            $team = Team::where('slug', $this->selectedTeam)->first();
            $recipientEmail = $this->getRecipientEmail($team);

            $teamName = $team ? $team->getTranslation('name', app()->getLocale()) : strtoupper($this->selectedTeam);

            Mail::to($recipientEmail)->send(new RecruitmentFormMail(
                senderName: $this->name,
                senderEmail: $this->email,
                teamName: $teamName,
                messageBody: $this->message,
                subjectText: "Nábor do týmu {$teamName}: {$this->name}",
                extraData: [
                    'height' => $this->height,
                    'position' => $this->position,
                    'level' => $this->level,
                    'age' => $this->age,
                ]
            ));

            $this->success = true;
            $this->reset(['name', 'email', 'message', 'recaptchaToken', 'height', 'position', 'level', 'age']);

        } catch (\Exception $e) {
            Log::error('Chyba při odesílání náborového formuláře: ' . $e->getMessage());
            $this->errorMessage = 'Při odesílání žádosti došlo k chybě. Zkuste to prosím později.';
        }
    }

    protected function getRecipientEmail(?Team $team): string
    {
        // 1. Trenér daného týmu (pokud má nastavený pivot email nebo email v profilu)
        if ($team) {
            $coach = $team->coaches()->first();
            if ($coach) {
                // Zkusíme pivot 'email'
                if ($coach->pivot && $coach->pivot->email) {
                    return $coach->pivot->email;
                }
                // Pak email uživatele
                if ($coach->email) {
                    return $coach->email;
                }
            }
        }

        // 2. Admin email z nastavení
        $adminEmail = Setting::where('key', 'admin_contact_email')->value('value');
        if (!$adminEmail) {
            $adminEmail = Setting::where('key', 'contact_email')->value('value');
        }

        if ($adminEmail) {
            // Setting je translatable, tak musíme vytáhnout řetězec
            if (is_array($adminEmail)) {
                return $adminEmail[app()->getLocale()] ?? reset($adminEmail);
            }
            return (string) $adminEmail;
        }

        // 3. Fallback
        return 'nejedlymi@gmail.com';
    }

    public function render()
    {
        return view('livewire.recruitment-form', [
            'teams' => Team::where('category', 'senior')
                ->orderBy('slug')
                ->get()
                ->mapWithKeys(fn ($team) => [$team->slug => $team->getTranslation('name', app()->getLocale())])
                ->toArray()
        ]);
    }
}
