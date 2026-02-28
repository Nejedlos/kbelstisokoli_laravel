<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\Gender;
use App\Enums\MembershipStatus;
use App\Enums\MembershipType;
use App\Enums\PaymentMethod;
use App\Traits\Auditable;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasAvatar;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use App\Notifications\Auth\ResetPasswordNotification;
use Propaganistas\LaravelPhone\Casts\E164PhoneNumberCast;
use Propaganistas\LaravelPhone\PhoneNumber;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser, HasMedia, HasAvatar
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, TwoFactorAuthenticatable, HasRoles, InteractsWithMedia, Auditable;

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
        'metadata',
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
            'metadata' => 'array',
            'two_factor_confirmed_at' => 'datetime',
            'date_of_birth' => 'date',
            'membership_started_at' => 'date',
            'membership_ended_at' => 'date',
            'finance_ok' => 'boolean',
            'gender' => Gender::class,
            'membership_status' => MembershipStatus::class,
            'membership_type' => MembershipType::class,
            'payment_method' => PaymentMethod::class,
            'phone' => E164PhoneNumberCast::class.':CZ',
            'phone_secondary' => E164PhoneNumberCast::class.':CZ',
            'emergency_contact_phone' => E164PhoneNumberCast::class.':CZ',
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

            // Pojistka proti přepsání klubového ID a variabilního symbolu
            // Jednou vygenerované údaje se nesmí změnit
            if ($user->exists) {
                if ($user->isDirty('club_member_id') && !empty($user->getOriginal('club_member_id'))) {
                    $user->club_member_id = $user->getOriginal('club_member_id');
                }

                if ($user->isDirty('payment_vs') && !empty($user->getOriginal('payment_vs'))) {
                    $user->payment_vs = $user->getOriginal('payment_vs');
                }
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
     * Hráčské profily uživatele (historie).
     */
    public function playerProfiles(): HasMany
    {
        return $this->hasMany(PlayerProfile::class);
    }

    /**
     * Aktuálně aktivní hráčský profil.
     */
    public function activePlayerProfile(): HasOne
    {
        return $this->hasOne(PlayerProfile::class)
            ->where('is_active', true)
            ->where(function ($query) {
                $query->whereNull('valid_to')
                    ->orWhere('valid_to', '>=', now());
            })
            ->latest('valid_from');
    }

    /**
     * Zpětná kompatibilita pro kód, který očekává jeden profil.
     */
    public function playerProfile(): HasOne
    {
        return $this->activePlayerProfile();
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
     * Týmy, které uživatel trénuje.
     */
    public function teams(): BelongsToMany
    {
        return $this->belongsToMany(Team::class, 'coach_team')
            ->withPivot(['email', 'phone'])
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
     * Předpisy plateb uživatele.
     */
    public function financeCharges(): HasMany
    {
        return $this->hasMany(FinanceCharge::class);
    }

    /**
     * Skutečné platby uživatele.
     */
    public function financePayments(): HasMany
    {
        return $this->hasMany(FinancePayment::class);
    }

    /**
     * Sezónní konfigurace uživatele.
     */
    public function userSeasonConfigs(): HasMany
    {
        return $this->hasMany(UserSeasonConfig::class);
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
        return $this->canAccessAdmin();
    }

    /**
     * Zkontroluje, zda má uživatel přístup k administraci (Filament nebo custom).
     */
    public function canAccessAdmin(): bool
    {
        // Povolíme přístup, pokud je uživatel aktivní a má roli/oprávnění
        // NEBO pokud je uživatel právě impersonován adminem (aby se mohl admin dívat na jeho účet v adminu, pokud je to potřeba)
        if (session()->has('impersonated_by')) {
            return true;
        }

        return $this->is_active &&
               ($this->can('access_admin') || $this->hasAnyRole(['admin', 'editor', 'coach']));
    }

    /**
     * Get the URL to the user's avatar.
     */
    public function getFilamentAvatarUrl(): string
    {
        return $this->getAvatarUrl('thumb');
    }

    /**
     * Helper pro získání URL k avataru s fallbackem.
     */
    public function getAvatarUrl(string $conversion = ''): string
    {
        // Spatie Media Library getFirstMediaUrl by měl vracit fallbackURL, pokud je definována v registerMediaCollections
        $url = $this->getFirstMediaUrl('avatar', $conversion);

        if (! $url) {
            $fallback = $conversion === 'thumb' ? 'default-avatar-thumb.webp' : 'default-avatar.webp';
            return asset("images/{$fallback}");
        }

        return $url;
    }

    /**
     * Registrace media kolekcí.
     */
    public function registerMediaCollections(): void
    {
        $disk = config('media-library.disk_name', 'public_path');

        $this->addMediaCollection('avatar')
            ->useDisk($disk)
            ->singleFile()
            ->useFallbackUrl(asset('images/default-avatar.webp'))
            ->useFallbackUrl(asset('images/default-avatar-thumb.webp'), 'thumb')
            ->useFallbackPath(public_path('images/default-avatar.webp'))
            ->useFallbackPath(public_path('images/default-avatar-thumb.webp'), 'thumb');

        $this->addMediaCollection('player_photos')
            ->useDisk($disk);
    }

    /**
     * Zaregistruje konverze médií.
     */
    public function registerMediaConversions(?\Spatie\MediaLibrary\MediaCollections\Models\Media $media = null): void
    {
        // Společná miniatura pro avatar i hráčské fotky
        $this->addMediaConversion('thumb')
            ->width(100)
            ->height(100)
            ->format('webp')
            ->sharpen(10)
            ->performOnCollections('avatar', 'player_photos');

        // Čtvercová varianta pro náhledy v soupiskách a galerii
        $this->addMediaConversion('square')
            ->width(800)
            ->height(800)
            ->format('webp')
            ->sharpen(10)
            ->performOnCollections('player_photos');

        // Portrétní varianta pro soupisku
        $this->addMediaConversion('roster')
            ->width(1200)
            ->height(1600)
            ->format('webp')
            ->optimize()
            ->performOnCollections('player_photos');
    }

    /**
     * Get display name for the user.
     */
    public function getDisplayNameAttribute(): string
    {
        return $this->first_name.' '.$this->last_name;
    }

    /**
     * Helper pro formátované zobrazení telefonu.
     */
    public function getFormattedPhoneAttribute(): ?string
    {
        return $this->phone?->formatInternational();
    }

    /**
     * Odeslání oznámení o resetu hesla v jazyce uživatele.
     */
    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new ResetPasswordNotification($token));
    }
}
