<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * App\Models\User
 *
 * @property int $id
 * @property string $name
 * @property string $email
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property string $password
 * @property string $role
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $last_login_at
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * 
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PurchaseOrder> $purchaseOrders
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PurchaseOrder> $validatedPurchaseOrders
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PurchaseOrder> $completedPurchaseOrders
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\CostEstimate> $costEstimates
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\CostEstimate> $approvedCostEstimates
 * 
 * @method static \Illuminate\Database\Eloquent\Builder|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|User query()
 * @method static \Illuminate\Database\Eloquent\Builder|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereLastLoginAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereRole($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User active()
 * @method static \Illuminate\Database\Eloquent\Builder|User withRole($role)
 * @method static \Database\Factories\UserFactory factory($count = null, $state = [])
 * 
 * @mixin \Eloquent
 */
class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'is_active',
        'last_login_at',
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
        ];
    }

    /**
     * Get the purchase orders created by this user.
     */
    public function purchaseOrders(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class, 'created_by');
    }

    /**
     * Get the purchase orders validated by this user.
     */
    public function validatedPurchaseOrders(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class, 'validated_by');
    }

    /**
     * Get the purchase orders completed by this user.
     */
    public function completedPurchaseOrders(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class, 'completed_by');
    }

    /**
     * Get the cost estimates created by this user.
     */
    public function costEstimates(): HasMany
    {
        return $this->hasMany(CostEstimate::class, 'created_by');
    }

    /**
     * Get the cost estimates approved by this user.
     */
    public function approvedCostEstimates(): HasMany
    {
        return $this->hasMany(CostEstimate::class, 'approved_by');
    }

    /**
     * Scope a query to only include active users.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to filter users by role.
     */
    public function scopeWithRole($query, $role)
    {
        return $query->where('role', $role);
    }

    /**
     * Check if user has a specific role.
     */
    public function hasRole(string $role): bool
    {
        return $this->role === $role;
    }

    /**
     * Check if user has any of the specified roles.
     */
    public function hasAnyRole(array $roles): bool
    {
        return in_array($this->role, $roles);
    }

    /**
     * Check if user can manage users (CRUD operations).
     */
    public function canManageUsers(): bool
    {
        return $this->hasRole('superadmin');
    }

    /**
     * Check if user can validate purchase orders.
     */
    public function canValidatePurchaseOrders(): bool
    {
        return $this->hasAnyRole(['superadmin', 'admin', 'bsp']);
    }

    /**
     * Check if user can approve cost estimates.
     */
    public function canApproveCostEstimates(): bool
    {
        return $this->hasAnyRole(['superadmin', 'admin', 'dau']);
    }

    /**
     * Check if user can create cost estimates.
     */
    public function canCreateCostEstimates(): bool
    {
        return $this->hasAnyRole(['superadmin', 'admin', 'bsp']);
    }

    /**
     * Check if user can complete purchase orders.
     */
    public function canCompletePurchaseOrders(): bool
    {
        return $this->hasAnyRole(['superadmin', 'admin']);
    }

    /**
     * Get role display name.
     */
    public function getRoleDisplayName(): string
    {
        return match($this->role) {
            'superadmin' => 'Super Administrator',
            'admin' => 'Administrator',
            'unit_kerja' => 'Unit Kerja',
            'bsp' => 'BSP',
            'kkf' => 'KKF',
            'dau' => 'DAU',
            default => 'Unknown',
        };
    }
}