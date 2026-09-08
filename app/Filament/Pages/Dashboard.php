<?php

namespace App\Filament\Pages;

use App\Models\Attendance;
use App\Models\AuditLog;
use App\Models\BasketballMatch;
use App\Models\ChargePaymentAllocation;
use App\Models\ClubEvent;
use App\Models\CronLog;
use App\Models\FinanceCharge;
use App\Models\FinancePayment;
use App\Models\Lead;
use App\Models\PlayerProfile;
use App\Models\Post;
use App\Models\Season;
use App\Models\Team;
use App\Models\Training;
use App\Models\User;
use App\Models\UserSeasonConfig;
use App\Services\BackendSearchService;
use Filament\GlobalSearch\GlobalSearchResult;
use Filament\Notifications\Notification;
use Filament\Pages\Dashboard as BaseDashboard;
use Illuminate\Contracts\Support\Htmlable;

class Dashboard extends BaseDashboard
{
    protected string $view = 'filament.pages.dashboard';

    public static function getNavigationLabel(): string
    {
        return __('admin.navigation.pages.dashboard');
    }

    public function getTitle(): string|Htmlable
    {
        return __('admin.navigation.pages.dashboard');
    }

    public $contact_message = '';

    public $contact_subject = '';

    protected function getViewData(): array
    {
        $user = auth()->user();
        $userName = $user?->first_name ?: ($user?->name ?: 'Admin');
        $debug = request()->query('debug') == '1';

        // KPI Stats
        $stats = [
            'users' => [
                'total' => User::count(),
                'active' => User::where('is_active', true)->count(),
            ],
            'players' => PlayerProfile::count(),
            'teams' => Team::count(),
            'matches' => [
                'total' => BasketballMatch::count(),
                'upcoming' => BasketballMatch::where('scheduled_at', '>=', now())->count(),
            ],
            'trainings' => [
                'total' => Training::count(),
                'upcoming' => Training::where('starts_at', '>=', now())->count(),
            ],
            'attendance' => class_exists(Attendance::class) ? Attendance::count() : 0,
            'leads_pending' => Lead::whereIn('status', Lead::openStatuses())->count(),
            'posts_active' => Post::where('status', 'published')->count(),
            'events_upcoming' => ClubEvent::where('starts_at', '>=', now())->count(),
        ];

        // Finance Stats
        $totalReceivables = FinanceCharge::whereNotIn('status', ['cancelled'])
            ->sum('amount_total') - ChargePaymentAllocation::sum('amount');

        $overdueReceivables = FinanceCharge::whereNotIn('status', ['cancelled', 'paid'])
            ->where('due_date', '<', now())
            ->sum('amount_total'); // Simplified for now, real calculation would subtract allocations

        $paymentsThisMonth = FinancePayment::whereMonth('paid_at', now()->month)
            ->whereYear('paid_at', now()->year)
            ->sum('amount');

        $finance = [
            'total_receivables' => $totalReceivables,
            'overdue' => $overdueReceivables,
            'payments_month' => $paymentsThisMonth,
        ];

        // System Health
        $lastCronRun = CronLog::latest('started_at')->first()?->started_at;
        $cronOk = $lastCronRun && $lastCronRun->gt(now()->subMinutes(65));

        $mismatchesCount = Attendance::where('is_mismatch', true)
            ->where('updated_at', '>=', now()->subDays(30))
            ->count();

        $currentSeason = Season::where('is_active', true)->first();
        $usersWithoutConfig = 0;
        if ($currentSeason) {
            $usersWithoutConfig = User::where('is_active', true)
                ->whereDoesntHave('userSeasonConfigs', fn ($q) => $q->where('season_id', $currentSeason->id))
                ->count();
        }

        // Health & Season Renewal
        $expectedSeasonName = Season::getExpectedCurrentSeasonName();
        $configsExist = UserSeasonConfig::whereHas('season', fn ($q) => $q->where('name', $expectedSeasonName))->exists();

        $isSeptember = now()->month == 9;
        $showRenewalWarning = (! $configsExist && $isSeptember) || $debug;

        $health = [
            'cron_ok' => $cronOk,
            'last_cron' => $lastCronRun ? $lastCronRun->diffForHumans() : __('admin/dashboard.system.cron.unknown'),
            'mismatches' => $mismatchesCount,
            'missing_configs' => $usersWithoutConfig,
            'show_renewal' => $showRenewalWarning,
            'expected_season' => $expectedSeasonName,
            'renewal_url' => route('filament.admin.pages.season-renewal'),
        ];

        // Recent Activity
        $recentActivity = AuditLog::with('actor')
            ->latest('occurred_at')
            ->latest('created_at')
            ->limit(8)
            ->get()
            ->map(function ($log) {
                $actionLabel = match ($log->action) {
                    'created' => __('admin/dashboard.recent_activity.actions.created'),
                    'updated' => __('admin/dashboard.recent_activity.actions.updated'),
                    'deleted' => __('admin/dashboard.recent_activity.actions.deleted'),
                    'login' => __('admin/dashboard.recent_activity.actions.login'),
                    'password_reset' => __('admin/dashboard.recent_activity.actions.password_reset'),
                    default => ucfirst($log->action),
                };

                $icon = match ($log->action) {
                    'created' => 'fa-circle-plus',
                    'updated' => 'fa-pen-to-square',
                    'deleted' => 'fa-trash-xmark',
                    'login' => 'fa-right-to-bracket',
                    'password_reset' => 'fa-key',
                    default => 'fa-circle-dot',
                };

                $color = match ($log->action) {
                    'created' => 'success',
                    'updated' => 'warning',
                    'deleted' => 'danger',
                    'login' => 'info',
                    'password_reset' => 'primary',
                    default => 'gray',
                };

                // Build a detail string
                $details = null;
                if ($log->action === 'updated' && ! empty($log->changes)) {
                    $changedKeys = array_keys($log->changes['after'] ?? []);
                    if (! empty($changedKeys)) {
                        $details = 'Změněno: '.implode(', ', array_map(fn ($k) => __("fields.$k") !== "fields.$k" ? __("fields.$k") : $k, array_slice($changedKeys, 0, 3)));
                        if (count($changedKeys) > 3) {
                            $details .= '...';
                        }
                    }
                }

                $subject = $log->subject_label;
                if (! $subject && $log->subject_type) {
                    $subject = class_basename($log->subject_type);
                }

                return (object) [
                    'action' => $actionLabel,
                    'icon' => $icon,
                    'color' => $color,
                    'subject' => $subject,
                    'details' => $details,
                    'actor' => $log->actor?->name ?: ($log->is_system_event ? __('admin/dashboard.recent_activity.actor_system') : 'Host'),
                    'time' => $log->occurred_at ?: $log->created_at,
                ];
            });

        // Quick Actions
        $adminPath = config('filament.panels.admin.path', 'admin');
        $actions = [
            'new_match' => route('filament.admin.resources.basketball-matches.create'),
            'new_user' => route('filament.admin.resources.users.create'),
            'new_training' => route('filament.admin.resources.trainings.create'),
            'new_event' => route('filament.admin.resources.club-events.create'),
            'finance' => route('filament.admin.resources.finance-charges.index'),
            'media' => route('filament.admin.resources.media-assets.index'),
        ];

        // Upcoming Agenda
        $upcomingMatches = BasketballMatch::with(['opponent', 'team'])
            ->where('scheduled_at', '>=', now())
            ->orderBy('scheduled_at')
            ->limit(2)
            ->get();

        $upcomingTrainings = Training::with(['teams'])
            ->where('starts_at', '>=', now())
            ->orderBy('starts_at')
            ->limit(2)
            ->get();

        $upcomingEvents = ClubEvent::where('starts_at', '>=', now())
            ->orderBy('starts_at')
            ->limit(2)
            ->get();

        $dbInfo = [
            'database' => config('database.connections.'.config('database.default').'.database'),
            'table' => 'users',
            'prefix' => config('database.connections.'.config('database.default').'.prefix'),
            'connection' => config('database.default'),
        ];

        return [
            'userName' => $userName,
            'stats' => $stats,
            'finance' => $finance,
            'health' => $health,
            'recentActivity' => $recentActivity,
            'upcomingMatches' => $upcomingMatches,
            'upcomingTrainings' => $upcomingTrainings,
            'upcomingEvents' => $upcomingEvents,
            'actions' => $actions,
            'isNejedly' => $user?->email === 'nejedlymi@gmail.com',
            'debug' => $debug,
            'dbInfo' => $dbInfo,
        ];
    }

    public function submitContactForm(): void
    {
        $this->validate([
            'contact_message' => 'required|min:5',
            'contact_subject' => 'required',
        ]);

        Lead::create([
            'type' => 'admin_contact',
            'status' => 'pending',
            'name' => auth()->user()->name,
            'email' => auth()->user()->email,
            'subject' => $this->contact_subject,
            'message' => $this->contact_message,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        // Clear form
        $this->contact_message = '';
        $this->contact_subject = '';

        Notification::make()
            ->title(__('admin/dashboard.contact_admin.success_title'))
            ->success()
            ->send();
    }

    /**
     * Rozšíření globálního vyhledávání o AI navigaci ve Filamentu.
     */
    public static function getGlobalSearchResults(string $search): array
    {
        /** @var BackendSearchService $backendSearch */
        $backendSearch = app(BackendSearchService::class);
        $results = $backendSearch->search($search, 'admin');

        $output = [];
        foreach ($results as $result) {
            $output[] = new GlobalSearchResult(
                title: $result->title,
                url: $result->url,
                details: [
                    'AI Návrh' => $result->snippet,
                ],
            );
        }

        return $output;
    }
}
