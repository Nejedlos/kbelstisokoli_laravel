<?php

namespace App\Livewire;

use App\Models\Team;
use App\Services\LeadWorkflowService;
use App\Services\RecaptchaV3;
use Illuminate\Support\Facades\Log;
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

    public function submit(RecaptchaV3 $recaptchaService, LeadWorkflowService $leadWorkflow): void
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

            // Uložení a okamžité oznámení odpovědné osobě/fallbacku.
            $leadWorkflow->create([
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

            $this->success = true;
            $this->reset(['name', 'email', 'message', 'recaptchaToken', 'height', 'position', 'level', 'age']);

        } catch (\Exception $e) {
            Log::error('Chyba při odesílání náborového formuláře: '.$e->getMessage());
            $this->errorMessage = __('recruitment.form.error_sending');
        }
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
