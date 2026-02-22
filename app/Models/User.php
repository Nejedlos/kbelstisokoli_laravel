<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\Gender;
use App\Enums\MembershipStatus;
use App\Enums\MembershipType;
use App\Enums\PaymentMethod;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Permission\Traits\HasRoles;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class User extends Authenticatable implements FilamentUser, HasMedia
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, TwoFactorAuthenticatable, HasRoles, InteractsWithMedia;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'first_name',
        'last_name',
        'display_name',
        'email',
        'password',
        'is_active',
        'phone',
        'phone_secondary',
        'date_of_birth',
        'gender',
        'preferred_locale',
        'nationality',
        'club_member_id',
        'payment_vs',
        'membership_status',
        'membership_type',
        'membership_started_at',
        'membership_ended_at',
        'finance_ok',
        'payment_method',
        'payment_note',
        'address_street',
        'address_city',
        'address_zip',
        'address_country',
        'emergency_contact_name',
        'emergency_contact_phone',
        'public_contact_note',
        'last_login_at',
        'admin_note',
        'notification_preferences',
        'onboarding_completed_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'last_login_at' => 'datetime',
            'notification_preferences' => 'array',
            'onboarding_completed_at' => 'datetime',
            'two_factor_confirmed_at' => 'datetime',
            'date_of_birth' => 'date',
            'membership_started_at' => 'date',
            'membership_ended_at' => 'date',
            'finance_ok' => 'boolean',
            'gender' => Gender::class,
            'membership_status' => MembershipStatus::class,
            'membership_type' => MembershipType::class,
            'payment_method' => PaymentMethod::class,
        ];
    }

    /**
     * Automatické skládání jména.
     */
    protected static function booted()
    {
        static::saving(function ($user) {
            if ($user->first_name && $user->last_name) {
                $user->name = $user->first_name . ' ' . $user->last_name;
            }
        });
    }

    /**
     * Docházka uživatele k různým akcím.
     */
    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    /**
     * Hráčský profil uživatele (volitelný).
     */
    public function playerProfile(): HasOne
    {
        return $this->hasOne(PlayerProfile::class);
    }

    /**
     * Rodiče uživatele.
     */
    public function parents(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_relationships', 'child_id', 'parent_id')
            ->using(UserRelationship::class)
            ->withPivot(['relationship_type', 'is_emergency_contact', 'is_billing_contact', 'preferred_communication_channel'])
            ->withTimestamps();
    }

    /**
     * Děti uživatele.
     */
    public function children(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_relationships', 'parent_id', 'child_id')
            ->using(UserRelationship::class)
            ->withPivot(['relationship_type', 'is_emergency_contact', 'is_billing_contact', 'preferred_communication_channel'])
            ->withTimestamps();
    }

    /**
     * Souhlasy uživatele.
     */
    public function consents(): HasMany
    {
        return $this->hasMany(UserConsent::class);
    }

    /**
     * Scope pro aktivní uživatele.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Zkontroluje, zda má uživatel zapnutý daný typ notifikace.
     */
    public function prefersNotification(string $type, string $channel = 'mail'): bool
    {
        $prefs = $this->notification_preferences ?? [];

        // Pokud preference neexistují, defaultně posíláme (pokud není řečeno jinak)
        return (bool) data_get($prefs, "{$type}.{$channel}", true);
    }

    /**
     * Determine if the user can access the Filament admin panel.
     */
    public function canAccessPanel(Panel $panel): bool
    {
        return $this->is_active && $this->hasAnyRole(['admin', 'editor', 'coach']);
    }

    /**
     * Registrace media kolekcí.
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('avatar')
            ->singleFile();
    }

    /**
     * Get display name for the user.
     */
    public function getDisplayNameAttribute(): string
    {
        return $this->attributes['display_name'] ?? $this->name;
    }
}
