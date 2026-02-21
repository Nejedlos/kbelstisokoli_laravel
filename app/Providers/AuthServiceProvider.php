<?php

namespace App\Providers;

use App\Models\PlayerProfile;
use App\Models\User;
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
        \App\Models\FinanceCharge::class => \App\Policies\FinanceChargePolicy::class,
        \App\Models\FinancePayment::class => \App\Policies\FinancePaymentPolicy::class,
        \App\Models\ChargePaymentAllocation::class => \App\Policies\ChargePaymentAllocationPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();
    }
}
