<?php

namespace App\Models;

use App\Enums\UserRole;
use App\Notifications\Auth\EmailVerificationNotification;
use App\Notifications\Auth\PasswordResetNotification;
use App\Notifications\User\UserBannedNotification;
use App\Notifications\User\UserUnbannedNotification;
use App\Observers\User\UserObserver;
use Database\Factories\UserFactory;
use Eloquent;
use GeneaLabs\LaravelPivotEvents\Traits\PivotEventTrait;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Notifications\DatabaseNotificationCollection;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Laravel\Cashier\Billable;
use Laravel\Cashier\Subscription;
use Laravel\Sanctum\HasApiTokens;
use Laravel\Sanctum\PersonalAccessToken;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Traits\HasRoles;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/**
 * @property int $id
 * @property string $name
 * @property string|null $slug
 * @property string $email
 * @property Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $remember_token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property Carbon|null $banned_at
 * @property string|null $avatar_path
 * @property string|null $stripe_id
 * @property string|null $pm_type
 * @property string|null $pm_last_four
 * @property string|null $trial_ends_at
 * @property UserRole $role
 * @property-read Collection<int, \App\Models\Course> $courses
 * @property-read int|null $courses_count
 * @property-read Collection<int, \App\Models\Course> $enrolledCourses
 * @property-read int|null $enrolled_courses_count
 * @property-read DatabaseNotificationCollection<int, DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read Collection<int, Permission> $permissions
 * @property-read int|null $permissions_count
 * @property-read Collection<int, Role> $roles
 * @property-read int|null $roles_count
 * @property-read Collection<int, Subscription> $subscriptions
 * @property-read int|null $subscriptions_count
 * @property-read Collection<int, PersonalAccessToken> $tokens
 * @property-read int|null $tokens_count
 * @method static Builder<static>|User active()
 * @method static \Database\Factories\UserFactory factory($count = null, $state = [])
 * @method static Builder<static>|User hasExpiredGenericTrial()
 * @method static Builder<static>|User newModelQuery()
 * @method static Builder<static>|User newQuery()
 * @method static Builder<static>|User onGenericTrial()
 * @method static Builder<static>|User onlyTrashed()
 * @method static Builder<static>|User permission($permissions, bool $without = false)
 * @method static Builder<static>|User query()
 * @method static Builder<static>|User role($roles, ?string $guard = null, bool $without = false)
 * @method static Builder<static>|User whereAvatarPath($value)
 * @method static Builder<static>|User whereBannedAt($value)
 * @method static Builder<static>|User whereCreatedAt($value)
 * @method static Builder<static>|User whereDeletedAt($value)
 * @method static Builder<static>|User whereEmail($value)
 * @method static Builder<static>|User whereEmailVerifiedAt($value)
 * @method static Builder<static>|User whereId($value)
 * @method static Builder<static>|User whereName($value)
 * @method static Builder<static>|User wherePassword($value)
 * @method static Builder<static>|User wherePmLastFour($value)
 * @method static Builder<static>|User wherePmType($value)
 * @method static Builder<static>|User whereRememberToken($value)
 * @method static Builder<static>|User whereSlug($value)
 * @method static Builder<static>|User whereStripeId($value)
 * @method static Builder<static>|User whereTrialEndsAt($value)
 * @method static Builder<static>|User whereUpdatedAt($value)
 * @method static Builder<static>|User withTrashed(bool $withTrashed = true)
 * @method static Builder<static>|User withoutPermission($permissions)
 * @method static Builder<static>|User withoutRole($roles, ?string $guard = null)
 * @method static Builder<static>|User withoutTrashed()
 * @mixin Eloquent
 */
class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasApiTokens, Notifiable, HasSlug, HasRoles, SoftDeletes, PivotEventTrait, Billable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'slug',
        'email',
        'password',
        'avatar_path',
        'banned_at',
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
     * The guard name used for Spatie permissions.
     *
     * @var string
     */
    protected string $guard_name = 'api';

    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function booted(): void
    {
        static::observe(UserObserver::class);
    }

    /**
     * Get the options for generating the slug.
     *
     * @return SlugOptions
     */
    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name')
            ->saveSlugsTo('slug')
            ->doNotGenerateSlugsOnUpdate();
    }

    /**
     * Get the route key name for the model.
     *
     * @return string
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * Check if the user is enrolled in a given course.
     */
    public function isEnrolledIn(Course $course): bool
    {
        return $this->enrolledCourses()->where('course_id', $course->id)->exists();
    }

    /**
     * Get the courses enrolled by the user.
     *
     * @return BelongsToMany<Course, $this>
     */
    public function enrolledCourses(): BelongsToMany
    {
        return $this->belongsToMany(Course::class, 'course_user')
            ->withPivot('enrolled_at');
    }

    /**
     * Send verify email notification.
     *
     * @return void
     */
    public function sendEmailVerificationNotification(): void
    {
        $this->notify(new EmailVerificationNotification());
    }

    /**
     * Send the password reset notification.
     *
     * @param string $token
     * @return void
     */
    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new PasswordResetNotification($token));
    }

    /**
     * Send user banned notification.
     *
     * @return void
     */
    public function sendBanNotification(): void
    {
        $this->notify(new UserBannedNotification());
    }

    /**
     * Send user unbanned notification.
     *
     * @return void
     */
    public function sendUnbanNotification(): void
    {
        $this->notify(new UserUnbannedNotification());
    }

    /**
     * Get the courses authored by the user.
     *
     * @return HasMany
     */
    public function courses(): HasMany
    {
        return $this->hasMany(Course::class, 'author_id');
    }

    /**
     * Check if the user is banned.
     *
     * @return bool
     */
    public function isBanned(): bool
    {
        return $this->banned_at !== null;
    }

    /**
     * Ban the user.
     *
     * @return User
     */
    public function ban(): static
    {
        $this->banned_at = $this->freshTimestamp();
        return $this;
    }

    /**
     * Unban the user.
     *
     * @return User
     */
    public function unban(): static
    {
        $this->banned_at = null;
        return $this;
    }

    /**
     * Scope a query to only include active users.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNull('banned_at');
    }

    /**
     * Set the avatar path.
     *
     * @return $this
     */
    public function setAvatar(string $path): static
    {
        $this->avatar_path = $path;
        return $this;
    }

    /**
     * Remove the avatar path.
     *
     * @return $this
     */
    public function removeAvatar(): static
    {
        $this->avatar_path = null;
        return $this;
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'role' => UserRole::class,
            'email_verified_at' => 'datetime',
            'banned_at' => 'datetime',
        ];
    }
}
