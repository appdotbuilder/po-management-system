<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * App\Models\PurchaseOrder
 *
 * @property int $id
 * @property string $po_number
 * @property string $title
 * @property string|null $description
 * @property float|null $estimated_value
 * @property string $status
 * @property string $priority
 * @property \Illuminate\Support\Carbon|null $required_by
 * @property int $created_by
 * @property int|null $validated_by
 * @property \Illuminate\Support\Carbon|null $validated_at
 * @property int|null $completed_by
 * @property \Illuminate\Support\Carbon|null $completed_at
 * @property string|null $validation_notes
 * @property string|null $completion_notes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * 
 * @property-read \App\Models\User $createdBy
 * @property-read \App\Models\User|null $validatedBy
 * @property-read \App\Models\User|null $completedBy
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\CostEstimate> $costEstimates
 * @property-read \App\Models\CostEstimate|null $activeCostEstimate
 * 
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseOrder newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseOrder newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseOrder query()
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseOrder whereCompletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseOrder whereCompletedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseOrder whereCompletionNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseOrder whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseOrder whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseOrder whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseOrder whereEstimatedValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseOrder whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseOrder wherePoNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseOrder wherePriority($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseOrder whereRequiredBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseOrder whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseOrder whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseOrder whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseOrder whereValidatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseOrder whereValidatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseOrder whereValidationNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseOrder withStatus($status)
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseOrder withPriority($priority)
 * @method static \Database\Factories\PurchaseOrderFactory factory($count = null, $state = [])
 * 
 * @mixin \Eloquent
 */
class PurchaseOrder extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'po_number',
        'title',
        'description',
        'estimated_value',
        'status',
        'priority',
        'required_by',
        'created_by',
        'validated_by',
        'validated_at',
        'completed_by',
        'completed_at',
        'validation_notes',
        'completion_notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'estimated_value' => 'decimal:2',
        'required_by' => 'date',
        'validated_at' => 'datetime',
        'completed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user who created this purchase order.
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who validated this purchase order.
     */
    public function validatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validated_by');
    }

    /**
     * Get the user who completed this purchase order.
     */
    public function completedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completed_by');
    }

    /**
     * Get all cost estimates for this purchase order.
     */
    public function costEstimates(): HasMany
    {
        return $this->hasMany(CostEstimate::class);
    }

    /**
     * Get the active (latest approved) cost estimate.
     */
    public function activeCostEstimate()
    {
        return $this->hasOne(CostEstimate::class)->where('status', 'approved')->latest();
    }

    /**
     * Scope a query to filter by status.
     */
    public function scopeWithStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope a query to filter by priority.
     */
    public function scopeWithPriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }

    /**
     * Get status display name with color.
     */
    public function getStatusDisplayAttribute(): array
    {
        return match($this->status) {
            'draft' => ['name' => 'Draft', 'color' => 'gray'],
            'pending_validation' => ['name' => 'Pending Validation', 'color' => 'yellow'],
            'validated' => ['name' => 'Validated', 'color' => 'blue'],
            'pending_ce_boq' => ['name' => 'Pending CE/BOQ', 'color' => 'orange'],
            'ce_boq_created' => ['name' => 'CE/BOQ Created', 'color' => 'indigo'],
            'ce_boq_approved' => ['name' => 'CE/BOQ Approved', 'color' => 'purple'],
            'in_progress' => ['name' => 'In Progress', 'color' => 'blue'],
            'completed' => ['name' => 'Completed', 'color' => 'green'],
            'cancelled' => ['name' => 'Cancelled', 'color' => 'red'],
            default => ['name' => 'Unknown', 'color' => 'gray'],
        };
    }

    /**
     * Get priority display name with color.
     */
    public function getPriorityDisplayAttribute(): array
    {
        return match($this->priority) {
            'low' => ['name' => 'Low', 'color' => 'green'],
            'medium' => ['name' => 'Medium', 'color' => 'yellow'],
            'high' => ['name' => 'High', 'color' => 'orange'],
            'urgent' => ['name' => 'Urgent', 'color' => 'red'],
            default => ['name' => 'Unknown', 'color' => 'gray'],
        };
    }

    /**
     * Generate the next PO number.
     */
    public static function generatePoNumber(): string
    {
        $year = now()->year;
        $lastPo = static::where('po_number', 'like', "PO-{$year}-%")
                       ->orderBy('po_number', 'desc')
                       ->first();

        if (!$lastPo) {
            return "PO-{$year}-0001";
        }

        $lastNumber = (int) substr($lastPo->po_number, -4);
        $newNumber = str_pad((string)($lastNumber + 1), 4, '0', STR_PAD_LEFT);

        return "PO-{$year}-{$newNumber}";
    }

    /**
     * Check if PO can be validated.
     */
    public function canBeValidated(): bool
    {
        return in_array($this->status, ['draft', 'pending_validation']);
    }

    /**
     * Check if PO can have cost estimates created.
     */
    public function canHaveCostEstimate(): bool
    {
        return $this->status === 'validated';
    }

    /**
     * Check if PO can be completed.
     */
    public function canBeCompleted(): bool
    {
        return $this->status === 'in_progress';
    }
}