<?php

namespace App\Providers;

use App\Models\ChargePaymentAllocation;
use App\Models\Dmarc\DmarcIncident;
use App\Models\Dmarc\DmarcMailbox;
use App\Models\Dmarc\DmarcReport;
use App\Models\FinanceCharge;
use App\Models\FinancePayment;
use App\Models\PlayerProfile;
use App\Models\User;
use App\Policies\ChargePaymentAllocationPolicy;
use App\Policies\DmarcIncidentPolicy;
use App\Policies\DmarcMailboxPolicy;
use App\Policies\DmarcReportPolicy;
use App\Policies\FinanceChargePolicy;
use App\Policies\FinancePaymentPolicy;
use App\Policies\PermissionPolicy;
use App\Policies\PlayerProfilePolicy;
use App\Policies\RolePolicy;
use App\Policies\UserPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        User::class => UserPolicy::class,
        PlayerProfile::class => PlayerProfilePolicy::class,
        Role::class => RolePolicy::class,
        Permission::class => PermissionPolicy::class,
        FinanceCharge::class => FinanceChargePolicy::class,
        FinancePayment::class => FinancePaymentPolicy::class,
        ChargePaymentAllocation::class => ChargePaymentAllocationPolicy::class,
        DmarcMailbox::class => DmarcMailboxPolicy::class,
        DmarcReport::class => DmarcReportPolicy::class,
        DmarcIncident::class => DmarcIncidentPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        Gate::before(function ($user, $ability) {
            return $user->hasAnyRole(['admin', 'super_admin']) ? true : null;
        });
    }
}
