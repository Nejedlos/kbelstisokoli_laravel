<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('users')) {
            return;
        }

        if (! Schema::hasColumn('users', 'membership_types')) {
            Schema::table('users', function (Blueprint $table): void {
                $table->longText('membership_types')->nullable()->after('membership_type');
            });
        }

        DB::table('users')
            ->select(['id', 'membership_type'])
            ->whereNull('membership_types')
            ->orderBy('id')
            ->chunkById(200, function ($users): void {
                $rolesByUser = DB::table('model_has_roles')
                    ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
                    ->where('model_has_roles.model_type', User::class)
                    ->whereIn('model_has_roles.model_id', $users->pluck('id'))
                    ->whereIn('roles.name', ['player', 'coach', 'parent', 'admin', 'super_admin', 'editor'])
                    ->get(['model_has_roles.model_id', 'roles.name'])
                    ->groupBy('model_id');

                foreach ($users as $user) {
                    $roleNames = $rolesByUser->get($user->id, collect())->pluck('name');
                    $types = collect([(string) $user->membership_type])
                        ->merge($roleNames->intersect(['player', 'coach', 'parent']))
                        ->filter(fn (string $type) => in_array($type, ['player', 'coach', 'parent', 'staff', 'fan', 'honorary'], true))
                        ->unique()
                        ->values();

                    if ($types->isEmpty() && $roleNames->intersect(['admin', 'super_admin', 'editor'])->isNotEmpty()) {
                        $types->push('staff');
                    }

                    DB::table('users')
                        ->where('id', $user->id)
                        ->update(['membership_types' => json_encode($types->all(), JSON_THROW_ON_ERROR)]);
                }
            });
    }

    public function down(): void
    {
        if (Schema::hasTable('users') && Schema::hasColumn('users', 'membership_types')) {
            Schema::table('users', function (Blueprint $table): void {
                $table->dropColumn('membership_types');
            });
        }
    }
};
