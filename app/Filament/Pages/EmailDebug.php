<?php

namespace App\Filament\Pages;

use App\Mail\ErrorMail;
use App\Mail\TestMail;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\HtmlString;

class EmailDebug extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-envelope-open';

    protected static string|\UnitEnum|null $navigationGroup = null;

    protected static ?string $navigationLabel = null;

    protected static ?string $title = null;

    protected string $view = 'filament.pages.email-debug';

    public static function canAccess(): bool
    {
        return auth()->user()->can('manage_advanced_settings');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('admin.navigation.groups.system');
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.email_debug.navigation_label');
    }

    public function getTitle(): string
    {
        return __('admin.email_debug.title');
    }

    public function getMailConfig(): array
    {
        $config = config('mail.mailers.smtp');

        // GIT info
        $gitHash = 'unknown';
        try {
            $gitHash = trim(shell_exec('git log -1 --format=%h'));
            $gitDate = trim(shell_exec('git log -1 --format=%cd --date=iso'));
            $gitHash = $gitHash . " (" . $gitDate . ")";
        } catch (\Throwable $e) {}

        // Mask password
        if (isset($config['password'])) {
            $len = strlen($config['password']);
            $config['password'] = $len > 4
                ? substr($config['password'], 0, 2).str_repeat('*', $len - 4).substr($config['password'], -2)
                : '****';
        }

        return [
            'App Version' => '1.0.0',
            'Git Commit' => $gitHash,
            'Mailer' => config('mail.default'),
            'Host' => $config['host'] ?? '-',
            'Port' => $config['port'] ?? '-',
            'Encryption' => $config['encryption'] ?? 'null',
            'Username' => $config['username'] ?? '-',
            'Password' => $config['password'] ?? '-',
            'From Address' => config('mail.from.address'),
            'From Name' => config('mail.from.name'),
            'Error Recipient' => config('mail.error_reporting.email'),
        ];
    }

    public function getRecentLogs(): array
    {
        $logFile = storage_path('logs/laravel.log');
        if (! file_exists($logFile)) {
            return [];
        }

        $lines = [];
        $fp = fopen($logFile, 'r');
        fseek($fp, 0, SEEK_END);
        $pos = ftell($fp);
        $buffer = '';
        $count = 0;

        while ($pos > 0 && $count < 20) {
            fseek($fp, --$pos);
            $char = fgetc($fp);
            if ($char === "\n") {
                if ($buffer !== '') {
                    $lines[] = strrev($buffer);
                    $count++;
                }
                $buffer = '';
            } else {
                $buffer .= $char;
            }
        }
        fclose($fp);

        return array_filter($lines, function ($line) {
            return str_contains(strtolower($line), 'mail') || str_contains(strtolower($line), 'error');
        });
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('sendTestMail')
                ->label(__('admin.email_debug.actions.send_test'))
                ->icon(new HtmlString('<i class="fa-light fa-paper-plane"></i>'))
                ->color('info')
                ->form([
                    TextInput::make('email')
                        ->label(__('admin.email_debug.fields.recipient'))
                        ->email()
                        ->required()
                        ->default(auth()->user()->email),
                    TextInput::make('message')
                        ->label(__('admin.email_debug.fields.message'))
                        ->default(__('admin.email_debug.fields.test_message_default')),
                ])
                ->action(function (array $data) {
                    try {
                        Mail::to($data['email'])->send(new TestMail($data['message']));

                        Notification::make()
                            ->title(__('admin.email_debug.notifications.sent'))
                            ->success()
                            ->send();
                    } catch (\Throwable $e) {
                        Log::error('Email Debug Test Failed: '.$e->getMessage(), [
                            'exception' => get_class($e),
                            'trace' => $e->getTraceAsString(),
                        ]);

                        Notification::make()
                            ->title(__('admin.email_debug.notifications.error'))
                            ->body($e->getMessage())
                            ->danger()
                            ->persistent()
                            ->send();
                    }
                }),

            Action::make('sendErrorReport')
                ->label(__('admin.email_debug.actions.send_error'))
                ->icon(new HtmlString('<i class="fa-light fa-bug"></i>'))
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('Odeslat simulovaný report chyby 500')
                ->modalDescription('Tato akce nasimuluje chybu 500 a pokusí se odeslat report na adresu '.config('mail.error_reporting.email').'. Tato logika přesně odpovídá tomu, co se děje při pádu aplikace.')
                ->action(function () {
                    try {
                        $to = config('mail.error_reporting.email');
                        if (! $to) {
                            throw new \Exception('V konfiguraci chybí ERROR_REPORT_EMAIL.');
                        }

                        // Simulovaný report
                        $report = [
                            'timestamp' => now()->toIso8601String(),
                            'app' => [
                                'name' => config('app.name'),
                                'env' => config('app.env'),
                                'url' => config('app.url'),
                            ],
                            'exception' => [
                                'class' => 'App\\Exceptions\\ManualDebugTestException',
                                'message' => 'Tento report byl vygenerován ručně z admin sekce pro testovací účely.',
                                'code' => 0,
                                'file' => __FILE__,
                                'line' => __LINE__,
                                'trace' => '#0 Manual Test from Admin',
                            ],
                            'request' => [
                                'url' => request()->fullUrl(),
                                'method' => request()->method(),
                                'ip' => request()->ip(),
                            ],
                            'server' => [
                                'php' => PHP_VERSION,
                                'sapi' => PHP_SAPI,
                            ],
                            'user' => [
                                'id' => auth()->id(),
                                'email' => auth()->user()->email,
                                'name' => auth()->user()->name,
                            ],
                        ];

                        $from = config('mail.error_reporting.sender', config('mail.from.address'));

                        Mail::to($to)
                            ->send((new ErrorMail($report))->from($from, config('mail.from.name')));

                        Notification::make()
                            ->title('Error report byl odeslán na '.$to)
                            ->success()
                            ->send();

                    } catch (\Throwable $e) {
                        Log::error('Email Debug Error Report Failed: '.$e->getMessage());

                        Notification::make()
                            ->title('Chyba při odesílání reportu')
                            ->body($e->getMessage())
                            ->danger()
                            ->persistent()
                            ->send();
                    }
                }),
        ];
    }
}
