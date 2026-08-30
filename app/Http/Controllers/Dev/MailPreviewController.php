<?php

namespace App\Http\Controllers\Dev;

use App\Http\Controllers\Controller;
use App\Mail\AttendanceReminderMail;
use App\Mail\AttendanceSummaryMail;
use App\Mail\ContactFormMail;
use App\Mail\ErrorMail;
use App\Mail\FeedbackConfirmation;
use App\Mail\FeedbackMessage;
use App\Mail\FeedbackReportNotification;
use App\Mail\RecruitmentFormMail;
use App\Mail\TestMail;
use App\Models\AttendanceEmailDelivery;
use App\Models\ClubEvent;
use App\Models\FeedbackReport;
use App\Models\FinanceCharge;
use App\Models\Team;
use App\Models\User;
use App\Notifications\NewChargeNotification;
use App\Notifications\UserInvitationNotification;

class MailPreviewController extends Controller
{
    public function index()
    {
        // Povolíme pouze v local env nebo pro uživatele s právem manage_advanced_settings
        if (! app()->environment('local') && ! (auth()->check() && auth()->user()->can('manage_advanced_settings'))) {
            abort(403);
        }

        return view('dev.mail-preview.index', [
            'mailables' => [
                'Test Mail' => 'test-mail',
                'Contact Form' => 'contact-form',
                'Recruitment Form' => 'recruitment-form',
                'Feedback Report (Bug)' => 'feedback-report',
                'Feedback Confirmation' => 'feedback-confirmation',
                'Feedback Message' => 'feedback-message',
                'Error Report' => 'error-report',
                'User Invitation' => 'user-invitation',
                'New Charge Notification' => 'new-charge',
                'Attendance Reminder (CS/EN by user)' => 'attendance-reminder',
                'Attendance Summary for Coach' => 'attendance-summary',
            ],
        ]);
    }

    public function show($type)
    {
        // Povolíme pouze v local env nebo pro uživatele s právem manage_advanced_settings
        if (! app()->environment('local') && ! (auth()->check() && auth()->user()->can('manage_advanced_settings'))) {
            abort(403);
        }

        $user = User::first() ?? User::factory()->make();
        $team = Team::whereNotNull('name')->first() ?? Team::factory()->make(['name' => 'Tým Muži A']);
        $previewEvent = ClubEvent::where('starts_at', '>', now())->first() ?? new ClubEvent([
            'title' => ['cs' => 'Večerní trénink', 'en' => 'Evening practice'],
            'location' => 'Sportovní hala Kbely',
            'starts_at' => now()->addDays(3)->setTime(19, 30),
            'rsvp_enabled' => true,
        ]);
        if (! $previewEvent->getKey()) {
            $previewEvent->id = 999999;
        }
        $previewEvent->setRelation('teams', collect([$team]));
        $attendanceDelivery = new AttendanceEmailDelivery(['kind' => 'reminder', 'stage' => 'three_days']);
        $attendanceDelivery->user_id = $user->id;
        $attendanceDelivery->setRelation('user', $user);
        $attendanceDelivery->setRelation('attendable', $previewEvent);

        return match ($type) {
            'test-mail' => new TestMail('Toto je testovací zpráva pro ověření e-mailového design systému. Musí fungovat správně v Outlooku i Gmailu.'),

            'contact-form' => new ContactFormMail(
                'Jan Novák',
                'jan@novak.cz',
                'info@kbelstisokoli.cz',
                "Dobrý den,\n\nměl bych zájem o trénink basketbalu pro mého syna (10 let).\nKdy se konají nábory?\n\nS pozdravem,\nJan Novák",
                'Zájem o trénink'
            ),

            'recruitment-form' => new RecruitmentFormMail(
                'Petr Malý',
                'petr@maly.cz',
                $team->name,
                'Mám zájem o nábor do týmu a chtěl bych se zeptat na detaily.',
                'Nábor do týmu '.$team->name,
                ['age' => 12, 'height' => 155, 'position' => 'Rozehrávač', 'level' => 'Začátečník']
            ),

            'feedback-report' => new FeedbackReportNotification(
                FeedbackReport::whereNotNull('title')->first() ?? new FeedbackReport([
                    'id' => 123,
                    'title' => 'Chyba v zobrazení kalendáře',
                    'description' => 'Kalendář se na mobilu nezobrazuje správně, některé dny jsou mimo obrazovku a nelze na ně kliknout.',
                    'steps' => "1. Otevřít kalendář na iPhonu\n2. Přepnout na zobrazení měsíce\n3. Zkusit kliknout na poslední den",
                    'type' => 'bug',
                    'severity' => 'medium',
                    'source_area' => 'calendar',
                    'url' => 'https://kbelstisokoli.cz/kalendar',
                    'user_id' => $user->id,
                    'user_agent' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.0 Mobile/15E148 Safari/604.1',
                    'app_version' => '1.0.0',
                ])
            ),

            'feedback-confirmation' => new FeedbackConfirmation(
                'coach',
                $team,
                'Děkuji za vaši zprávu ohledně včerejšího tréninku. Rozhodně se na to podívám a dám vědět.'
            ),

            'feedback-message' => new FeedbackMessage(
                'admin',
                $user,
                'Potřeboval bych poradit se zaplacením příspěvků. V systému vidím, že mám dlužit, ale platil jsem včera.',
                null
            ),

            'error-report' => new ErrorMail([
                'app' => ['name' => 'Kbelští sokoli', 'env' => 'production'],
                'timestamp' => now()->toDateTimeString(),
                'request' => [
                    'url' => 'https://kbelstisokoli.cz/admin/users',
                    'method' => 'GET',
                    'ip' => '82.113.12.33',
                ],
                'exception' => [
                    'class' => 'RuntimeException',
                    'message' => 'Something went wrong while fetching users from database. Connection lost.',
                    'code' => 500,
                    'file' => '/app/Services/UserService.php',
                    'line' => 42,
                    'trace' => "#0 /app/Http/Controllers/UserController.php(15): App\Services\UserService->getUsers()\n#1 [internal function]: App\Http\Controllers\UserController->index()\n#2 /vendor/laravel/framework/src/Illuminate/Routing/ControllerDispatcher.php(48): call_user_func_array(Array, Array)\n#3 /vendor/laravel/framework/src/Illuminate/Routing/Route.php(261): Illuminate\Routing\ControllerDispatcher->dispatch(Object(Illuminate\Routing\Route), Object(App\Http\Controllers\UserController), 'index')",
                ],
                'user' => [
                    'id' => $user->id,
                    'email' => $user->email,
                    'name' => $user->name,
                ],
                'headers' => [
                    'user-agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36',
                    'accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8',
                ],
            ]),

            'user-invitation' => (new UserInvitationNotification('dummy-token'))->toMail($user),

            'new-charge' => (new NewChargeNotification(
                FinanceCharge::first() ?? new FinanceCharge([
                    'title' => 'Členské příspěvky 2024',
                    'amount_total' => 5000,
                    'due_date' => now()->addDays(14),
                ])
            ))->toMail($user),

            'attendance-reminder' => new AttendanceReminderMail($attendanceDelivery),
            'attendance-summary' => new AttendanceSummaryMail(
                tap(clone $attendanceDelivery, fn ($delivery) => $delivery->kind = 'summary'),
                [
                    'confirmed' => collect([$user, new User(['name' => 'Jan Novák']), new User(['name' => 'Petr Dvořák']), new User(['name' => 'David Horák'])]),
                    'declined' => collect([new User(['name' => 'Martin Procházka'])]),
                    'maybe' => collect([new User(['name' => 'Filip Černý'])]),
                    'pending' => collect([new User(['name' => 'Lukáš Svoboda']), new User(['name' => 'Adam Kučera'])]),
                ]
            ),

            default => abort(404),
        };
    }
}
