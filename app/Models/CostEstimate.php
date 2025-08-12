<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * App\Models\CostEstimate
 *
 * @property int $id
 * @property int $purchase_order_id
 * @property string $ce_number
 * @property string $title
 * @property string|null $description
 * @property string $type
 * @property float $total_amount
 * @property string $status
 * @property int $created_by
 * @property int|null $approved_by
 * @property \Illuminate\Support\Carbon|null $approved_at
 * @property string|null $approval_notes
 * @property string|null $rejection_notes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * 
 * @property-read \App\Models\PurchaseOrder $purchaseOrder
 * @property-read \App\Models\User $createdBy
 * @property-read \App\Models\User|null $approvedBy
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\CostEstimateItem> $items
 * 
 * @method static \Illuminate\Database\Eloquent\Builder|CostEstimate newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CostEstimate newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CostEstimate query()
 * @method static \Illuminate\Database\Eloquent\Builder|CostEstimate whereApprovedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CostEstimate whereApprovedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CostEstimate whereApprovalNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CostEstimate whereCeNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CostEstimate whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CostEstimate whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CostEstimate whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CostEstimate whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CostEstimate wherePurchaseOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CostEstimate whereRejectionNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CostEstimate whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CostEstimate whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CostEstimate whereTotalAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CostEstimate whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CostEstimate whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CostEstimate withStatus($status)
 * @method static \Illuminate\Database\Eloquent\Builder|CostEstimate withType($type)
 * @method static \Database\Factories\CostEstimateFactory factory($count = null, $state = [])
 * 
 * @mixin \Eloquent
 */
class CostEstimate extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'purchase_order_id',
        'ce_number',
        'title',
        'description',
        'type',
        'total_amount',
        'status',
        'created_by',
        'approved_by',
        'approved_at',
        'approval_notes',
        'rejection_notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'total_amount' => 'decimal:2',
        'approved_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the purchase order this cost estimate belongs to.
     */
    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    /**
     * Get the user who created this cost estimate.
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who approved this cost estimate.
     */
    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Get all items for this cost estimate.
     */
    public function items(): HasMany
    {
        return $this->hasMany(CostEstimateItem::class)->orderBy('sort_order');
    }

    /**
     * Scope a query to filter by status.
     */
    public function scopeWithStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope a query to filter by type.
     */
    public function scopeWithType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Get status display name with color.
     */
    public function getStatusDisplayAttribute(): array
    {
        return match($this->status) {
            'draft' => ['name' => 'Draft', 'color' => 'gray'],
            'pending_approval' => ['name' => 'Pending Approval', 'color' => 'yellow'],
            'approved' => ['name' => 'Approved', 'color' => 'green'],
            'rejected' => ['name' => 'Rejected', 'color' => 'red'],
            default => ['name' => 'Unknown', 'color' => 'gray'],
        };
    }

    /**
     * Get type display name.
     */
    public function getTypeDisplayAttribute(): string
    {
        return match($this->type) {
            'cost_estimate' => 'Cost Estimate',
            'bill_of_quantities' => 'Bill of Quantities',
            default => 'Unknown',
        };
    }

    /**
     * Generate the next CE number.
     */
    public static function generateCeNumber(): string
    {
        $year = now()->year;
        $lastCe = static::where('ce_number', 'like', "CE-{$year}-%")
                       ->orderBy('ce_number', 'desc')
                       ->first();

        if (!$lastCe) {
            return "CE-{$year}-0001";
        }

        $lastNumber = (int) substr($lastCe->ce_number, -4);
        $newNumber = str_pad((string)($lastNumber + 1), 4, '0', STR_PAD_LEFT);

        return "CE-{$year}-{$newNumber}";
    }

    /**
     * Calculate total amount from items.
     */
    public function calculateTotalAmount(): void
    {
        $this->total_amount = $this->items()->sum('total_price');
        $this->save();
    }

    /**
     * Check if cost estimate can be approved.
     */
    public function canBeApproved(): bool
    {
        return in_array($this->status, ['draft', 'pending_approval']);
    }

    /**
     * Check if cost estimate can be rejected.
     */
    public function canBeRejected(): bool
    {
        return in_array($this->status, ['draft', 'pending_approval']);
    }
}