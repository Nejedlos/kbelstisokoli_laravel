<?php

namespace App\Filament\Pages;

use App\Models\AiRequestLog;
use App\Models\AiSetting;
use App\Services\AiSettingsService;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Concerns\InteractsWithFormActions;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\HtmlString;

class AiSettings extends Page implements HasForms
{
    use InteractsWithFormActions;
    use InteractsWithForms;

    public static function canAccess(): bool
    {
        return auth()->user()->can('manage_ai_settings');
    }

    public static function getNavigationIcon(): ?string
    {
        return \App\Support\IconHelper::get(\App\Support\IconHelper::AI);
    }

    public static function getNavigationGroup(): ?string
    {
        return __('admin.navigation.groups.admin_tools');
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.navigation.pages.ai_settings');
    }

    public static function getNavigationSort(): ?int
    {
        return 2;
    }

    public function getTitle(): string|\Illuminate\Contracts\Support\Htmlable
    {
        return __('admin.navigation.pages.ai_settings');
    }

    protected string $view = 'filament.pages.ai-settings';

    public ?array $data = [];

    public function mount(): void
    {
        $settings = AiSetting::first();

        $defaults = [
            'enabled' => true,
            'use_database_settings' => false,
            'provider' => 'openai',
            'openai_base_url' => 'https://api.openai.com/v1',
            'openai_timeout_seconds' => 90,
            'openai_max_retries' => 3,
            'openai_verify_ssl' => true,
            'default_chat_model' => 'gpt-4o-mini',
            'analyze_model' => 'gpt-4o',
            'fast_model' => 'gpt-4o-mini',
            'embeddings_model' => 'text-embedding-3-small',
            'temperature' => 0.7,
            'top_p' => 1.0,
            'max_output_tokens' => 2000,
            'cache_enabled' => true,
            'cache_ttl_seconds' => 3600,
            'retention_days' => 30,
        ];

        $this->data = array_merge($defaults, $settings ? $settings->toArray() : []);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                Tabs::make('Settings')
                    ->tabs([
                        Tab::make('General')
                            ->label(new HtmlString('<i class="fa-light fa-gears fa-fw mr-1"></i> '.__('admin/ai-settings.tabs.general')))
                            ->schema([
                                Section::make(__('admin/ai-settings.sections.status'))
                                    ->description(__('admin/ai-settings.sections.status_desc'))
                                    ->schema([
                                        Grid::make(2)
                                            ->schema([
                                                Toggle::make('enabled')
                                                    ->label(__('admin/ai-settings.fields.enabled'))
                                                    ->helperText(__('admin/ai-settings.fields.enabled_help'))
                                                    ->default(true)
                                                    ->inline(false),
                                                Toggle::make('use_database_settings')
                                                    ->label(__('admin/ai-settings.fields.use_database_settings'))
                                                    ->helperText(__('admin/ai-settings.fields.use_database_settings_help'))
                                                    ->live()
                                                    ->inline(false),
                                            ]),
                                        Select::make('provider')
                                            ->label(__('admin/ai-settings.fields.provider'))
                                            ->options([
                                                'openai' => 'OpenAI',
                                                'anthropic' => 'Anthropic (brzy)',
                                                'google' => 'Google Gemini (brzy)',
                                            ])
                                            ->default('openai'),
                                    ]),
                            ]),

                        Tab::make('OpenAI')
                            ->label(new HtmlString('<i class="fa-light fa-bolt fa-fw mr-1"></i> '.__('admin/ai-settings.tabs.openai')))
                            ->schema([
                                Section::make(__('admin/ai-settings.sections.connection'))
                                    ->description(__('admin/ai-settings.sections.connection_desc'))
                                    ->schema([
                                        TextInput::make('openai_api_key')
                                            ->label(__('admin/ai-settings.fields.api_key'))
                                            ->password()
                                            ->revealable()
                                            ->required(fn ($get) => $get('use_database_settings'))
                                            ->placeholder('sk-...')
                                            ->helperText(__('admin/ai-settings.fields.api_key_help')),

                                        Grid::make(3)
                                            ->schema([
                                                TextInput::make('openai_base_url')
                                                    ->label(__('admin/ai-settings.fields.base_url'))
                                                    ->default('https://api.openai.com/v1'),
                                                TextInput::make('openai_organization')
                                                    ->label(__('admin/ai-settings.fields.organization'))
                                                    ->placeholder('org-...'),
                                                TextInput::make('openai_project')
                                                    ->label(__('admin/ai-settings.fields.project'))
                                                    ->placeholder('proj-...'),
                                            ]),

                                        Grid::make(3)
                                            ->schema([
                                                TextInput::make('openai_timeout_seconds')
                                                    ->label(__('admin/ai-settings.fields.timeout'))
                                                    ->numeric()
                                                    ->default(90),
                                                TextInput::make('openai_max_retries')
                                                    ->label(__('admin/ai-settings.fields.max_retries'))
                                                    ->numeric()
                                                    ->default(3),
                                                Toggle::make('openai_verify_ssl')
                                                    ->label(__('admin/ai-settings.fields.verify_ssl'))
                                                    ->default(true)
                                                    ->inline(false),
                                            ]),
                                    ]),
                            ]),

                        Tab::make('Models')
                            ->label(new HtmlString('<i class="fa-light fa-brain fa-fw mr-1"></i> '.__('admin/ai-settings.tabs.models')))
                            ->schema([
                                Section::make(__('admin/ai-settings.sections.active_models'))
                                    ->description(__('admin/ai-settings.sections.active_models_desc'))
                                    ->schema([
                                        Grid::make(2)
                                            ->schema([
                                                TextInput::make('default_chat_model')
                                                    ->label(__('admin/ai-settings.fields.default_chat_model'))
                                                    ->datalist([
                                                        'gpt-4o-mini',
                                                        'gpt-4o',
                                                        'o1-mini',
                                                        'o1-preview',
                                                    ])
                                                    ->default('gpt-4o-mini'),
                                                TextInput::make('analyze_model')
                                                    ->label(__('admin/ai-settings.fields.analyze_model'))
                                                    ->datalist([
                                                        'gpt-4o',
                                                        'gpt-4-turbo',
                                                    ])
                                                    ->default('gpt-4o'),
                                                TextInput::make('fast_model')
                                                    ->label(__('admin/ai-settings.fields.fast_model'))
                                                    ->default('gpt-4o-mini'),
                                                TextInput::make('embeddings_model')
                                                    ->label(__('admin/ai-settings.fields.embeddings_model'))
                                                    ->default('text-embedding-3-small'),
                                            ]),
                                    ]),

                                Section::make(__('admin/ai-settings.sections.model_presets'))
                                    ->description(__('admin/ai-settings.sections.model_presets_desc'))
                                    ->schema([
                                        Repeater::make('model_presets')
                                            ->label(__('admin/ai-settings.fields.model_list'))
                                            ->schema([
                                                TextInput::make('name')->required(),
                                                TextInput::make('id')->required(),
                                                Select::make('purpose')
                                                    ->options([
                                                        'chat' => 'Chat',
                                                        'search' => 'Search',
                                                        'analyze' => 'Analyze',
                                                        'embedding' => 'Embedding',
                                                    ]),
                                                TextInput::make('temp')->numeric()->default(0.7),
                                            ])
                                            ->columns(4)
                                            ->collapsible(),
                                    ]),
                            ]),

                        Tab::make('Inference')
                            ->label(new HtmlString('<i class="fa-light fa-sliders fa-fw mr-1"></i> '.__('admin/ai-settings.tabs.inference')))
                            ->schema([
                                Section::make(__('admin/ai-settings.sections.inference_params'))
                                    ->schema([
                                        Grid::make(3)
                                            ->schema([
                                                TextInput::make('temperature')
                                                    ->label(__('admin/ai-settings.fields.temperature'))
                                                    ->numeric()
                                                    ->step(0.1)
                                                    ->default(0.7),
                                                TextInput::make('top_p')
                                                    ->label(__('admin/ai-settings.fields.top_p'))
                                                    ->numeric()
                                                    ->step(0.1)
                                                    ->default(1.0),
                                                TextInput::make('max_output_tokens')
                                                    ->label(__('admin/ai-settings.fields.max_output_tokens'))
                                                    ->numeric()
                                                    ->default(2000),
                                            ]),
                                    ]),

                                Section::make(__('admin/ai-settings.sections.system_prompts'))
                                    ->schema([
                                        Textarea::make('system_prompt_default')
                                            ->label(__('admin/ai-settings.fields.global_system_prompt'))
                                            ->rows(3),
                                        Textarea::make('system_prompt_search')
                                            ->label(__('admin/ai-settings.fields.search_system_prompt'))
                                            ->rows(5),
                                    ]),
                            ]),

                        Tab::make('Performance')
                            ->label(new HtmlString('<i class="fa-light fa-gauge-high fa-fw mr-1"></i> '.__('admin/ai-settings.tabs.performance')))
                            ->schema([
                                Section::make(__('admin/ai-settings.sections.caching'))
                                    ->schema([
                                        Grid::make(2)
                                            ->schema([
                                                Toggle::make('cache_enabled')
                                                    ->label(__('admin/ai-settings.fields.cache_enabled'))
                                                    ->default(true)
                                                    ->inline(false),
                                                TextInput::make('cache_ttl_seconds')
                                                    ->label(__('admin/ai-settings.fields.cache_ttl'))
                                                    ->numeric()
                                                    ->default(3600),
                                            ]),
                                    ]),
                            ]),

                        Tab::make('Debug')
                            ->label(new HtmlString('<i class="fa-light fa-bug fa-fw mr-1"></i> '.__('admin/ai-settings.tabs.debug')))
                            ->schema([
                                Section::make(__('admin/ai-settings.sections.debug_config'))
                                    ->description(__('admin/ai-settings.sections.debug_config_desc'))
                                    ->schema([
                                        Grid::make(2)
                                            ->schema([
                                                Toggle::make('debug_enabled')
                                                    ->label(__('admin/ai-settings.fields.debug_mode'))
                                                    ->helperText(__('admin/ai-settings.fields.debug_mode_help'))
                                                    ->default(false)
                                                    ->inline(false),
                                                Toggle::make('debug_log_to_database')
                                                    ->label(__('admin/ai-settings.fields.log_to_db'))
                                                    ->helperText(__('admin/ai-settings.fields.log_to_db_help'))
                                                    ->default(true)
                                                    ->inline(false),
                                            ]),
                                        Grid::make(2)
                                            ->schema([
                                                Toggle::make('debug_log_requests')
                                                    ->label(__('admin/ai-settings.fields.log_requests'))
                                                    ->inline(false),
                                                Toggle::make('debug_log_responses')
                                                    ->label(__('admin/ai-settings.fields.log_responses'))
                                                    ->inline(false),
                                            ]),
                                        TextInput::make('retention_days')
                                            ->label(__('admin/ai-settings.fields.retention_days'))
                                            ->helperText(__('admin/ai-settings.fields.retention_days_help'))
                                            ->numeric()
                                            ->default(30)
                                            ->maxWidth('xs'),
                                    ]),
                            ]),
                    ])
                    ->columnSpanFull(),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $this->validate();

        $settings = AiSetting::firstOrNew([]);
        $settings->fill($this->data);
        $settings->save();

        app(AiSettingsService::class)->clearCache();

        Notification::make()
            ->title(__('admin/ai-settings.notifications.saved'))
            ->success()
            ->seconds(3)
            ->send();

        // Místo redirectu refreshneme data a pošleme event pro UI indikátor
        $this->mount();
        $this->dispatch('settings-saved');
    }

    public function testConnection(): void
    {
        $this->validate();

        $apiKey = $this->data['openai_api_key'] ?? config('ai.openai.api_key');
        $baseUrl = $this->data['openai_base_url'] ?? config('ai.openai.base_url');
        $model = $this->data['default_chat_model'] ?? 'gpt-4o-mini';

        if (! $apiKey) {
            Notification::make()
                ->title(__('admin/ai-settings.notifications.missing_api_key'))
                ->danger()
                ->seconds(3)
                ->send();

            return;
        }

        try {
            $response = Http::timeout(10)
                ->withToken($apiKey)
                ->post($baseUrl.'/chat/completions', [
                    'model' => $model,
                    'messages' => [['role' => 'user', 'content' => 'ping']],
                    'max_tokens' => 5,
                ]);

            if ($response->successful()) {
                Notification::make()
                    ->title(__('admin/ai-settings.notifications.connection_success'))
                    ->body(__('admin/ai-settings.notifications.connection_success_body'))
                    ->success()
                    ->seconds(3)
                    ->send();
                $this->dispatch('test-connection-success');
            } else {
                Notification::make()
                    ->title(__('admin/ai-settings.notifications.connection_error'))
                    ->body($response->json('error.message') ?? 'Neznámá chyba API.')
                    ->danger()
                    ->seconds(4)
                    ->send();
                $this->dispatch('test-connection-error');
            }
        } catch (\Throwable $e) {
            Notification::make()
                ->title(__('admin/ai-settings.notifications.communication_error'))
                ->body($e->getMessage())
                ->danger()
                ->seconds(4)
                ->send();
            $this->dispatch('test-connection-error');
        }
    }

    public function clearAiCache(): void
    {
        DB::table('cache')->where('key', 'like', 'ai:%')->delete();
        app(AiSettingsService::class)->clearCache();

        Notification::make()
            ->title(__('admin/ai-settings.notifications.cache_cleared'))
            ->success()
            ->seconds(3)
            ->send();

        $this->dispatch('cache-cleared');
    }

    public function resetDebugLogs(): void
    {
        AiRequestLog::truncate();

        Notification::make()
            ->title(__('admin/ai-settings.notifications.logs_truncated'))
            ->success()
            ->seconds(3)
            ->send();

        $this->dispatch('logs-reset');
    }

    public function resetToDefaults(): void
    {
        $this->form->fill([
            'enabled' => true,
            'use_database_settings' => false,
            'provider' => 'openai',
            'openai_base_url' => 'https://api.openai.com/v1',
            'openai_timeout_seconds' => 90,
            'openai_max_retries' => 3,
            'openai_verify_ssl' => true,
            'default_chat_model' => 'gpt-4o-mini',
            'analyze_model' => 'gpt-4o',
            'fast_model' => 'gpt-4o-mini',
            'embeddings_model' => 'text-embedding-3-small',
            'temperature' => 0.7,
            'top_p' => 1.0,
            'max_output_tokens' => 2000,
            'cache_enabled' => true,
            'cache_ttl_seconds' => 3600,
            'retention_days' => 30,
        ]);

        Notification::make()
            ->title(__('admin/ai-settings.notifications.defaults_restored'))
            ->body(__('admin/ai-settings.notifications.defaults_restored_body'))
            ->info()
            ->seconds(3)
            ->send();

        $this->dispatch('defaults-reset');
    }

    public function getHeaderWidgets(): array
    {
        return [
            // Zde by mohl být malý widget se statistikou
        ];
    }

    public function getFormActions(): array
    {
        return [
            \Filament\Actions\Action::make('save')
                ->label(new HtmlString('<span x-data="{ success: false }" x-on:settings-saved.window="success = true; setTimeout(() => success = false, 2500)">
                    <span x-show="!success"><i class="fa-light fa-floppy-disk mr-1.5"></i> '.__('admin/ai-settings.actions.save').'</span>
                    <span x-show="success" x-cloak class="text-white dark:text-white"><i class="fa-solid fa-circle-check mr-1.5 animate-bounce"></i> '.__('admin/ai-settings.actions.saved').'</span>
                </span>'))
                ->submit('save')
                ->successNotification(null)
                ->failureNotification(null),

            \Filament\Actions\Action::make('test_connection')
                ->label(new HtmlString('<span x-data="{ state: \'idle\' }" x-on:test-connection-success.window="state = \'success\'; setTimeout(() => state = \'idle\', 2500)" x-on:test-connection-error.window="state = \'error\'; setTimeout(() => state = \'idle\', 2500)">
                    <span x-show="state === \'idle\'"><i class="fa-light fa-plug-circle-check mr-1.5"></i> '.__('admin/ai-settings.actions.test_connection').'</span>
                    <span x-show="state === \'success\'" x-cloak class="text-white"><i class="fa-solid fa-circle-check mr-1.5 animate-bounce"></i> '.__('admin/ai-settings.actions.test_success').'</span>
                    <span x-show="state === \'error\'" x-cloak class="text-white"><i class="fa-solid fa-circle-xmark mr-1.5 animate-pulse"></i> '.__('admin/ai-settings.actions.test_error').'</span>
                </span>'))
                ->color('info')
                ->action('testConnection')
                ->successNotification(null)
                ->failureNotification(null),

            \Filament\Actions\Action::make('clear_cache')
                ->label(new HtmlString('<span x-data="{ success: false }" x-on:cache-cleared.window="success = true; setTimeout(() => success = false, 2500)">
                    <span x-show="!success"><i class="fa-light fa-broom mr-1.5"></i> '.__('admin/ai-settings.actions.clear_cache').'</span>
                    <span x-show="success" x-cloak class="text-white"><i class="fa-solid fa-circle-check mr-1.5 animate-bounce"></i> '.__('admin/ai-settings.actions.cache_cleared').'</span>
                </span>'))
                ->color('warning')
                ->requiresConfirmation()
                ->action('clearAiCache')
                ->successNotification(null)
                ->failureNotification(null),

            \Filament\Actions\Action::make('reset_logs')
                ->label(new HtmlString('<span x-data="{ success: false }" x-on:logs-reset.window="success = true; setTimeout(() => success = false, 2500)">
                    <span x-show="!success"><i class="fa-light fa-trash-can mr-1.5"></i> '.__('admin/ai-settings.actions.reset_logs').'</span>
                    <span x-show="success" x-cloak class="text-white"><i class="fa-solid fa-circle-check mr-1.5 animate-bounce"></i> '.__('admin/ai-settings.actions.logs_reset').'</span>
                </span>'))
                ->color('danger')
                ->requiresConfirmation()
                ->action('resetDebugLogs')
                ->successNotification(null)
                ->failureNotification(null),

            \Filament\Actions\Action::make('reset_defaults')
                ->label(new HtmlString('<span x-data="{ success: false }" x-on:defaults-reset.window="success = true; setTimeout(() => success = false, 2500)">
                    <span x-show="!success"><i class="fa-light fa-arrows-rotate mr-1.5"></i> '.__('admin/ai-settings.actions.defaults').'</span>
                    <span x-show="success" x-cloak class="text-white"><i class="fa-solid fa-circle-check mr-1.5 animate-bounce"></i> '.__('admin/ai-settings.actions.defaults_reset').'</span>
                </span>'))
                ->color('gray')
                ->requiresConfirmation()
                ->action('resetToDefaults')
                ->successNotification(null)
                ->failureNotification(null),
        ];
    }
}
