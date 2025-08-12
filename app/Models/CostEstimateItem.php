<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\CostEstimateItem
 *
 * @property int $id
 * @property int $cost_estimate_id
 * @property string|null $item_code
 * @property string $description
 * @property string $unit
 * @property float $quantity
 * @property float $unit_price
 * @property float $total_price
 * @property string|null $notes
 * @property int $sort_order
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * 
 * @property-read \App\Models\CostEstimate $costEstimate
 * 
 * @method static \Illuminate\Database\Eloquent\Builder|CostEstimateItem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CostEstimateItem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CostEstimateItem query()
 * @method static \Illuminate\Database\Eloquent\Builder|CostEstimateItem whereCostEstimateId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CostEstimateItem whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CostEstimateItem whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CostEstimateItem whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CostEstimateItem whereItemCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CostEstimateItem whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CostEstimateItem whereQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CostEstimateItem whereSortOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CostEstimateItem whereTotalPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CostEstimateItem whereUnit($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CostEstimateItem whereUnitPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CostEstimateItem whereUpdatedAt($value)
 * @method static \Database\Factories\CostEstimateItemFactory factory($count = null, $state = [])
 * 
 * @mixin \Eloquent
 */
class CostEstimateItem extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'cost_estimate_id',
        'item_code',
        'description',
        'unit',
        'quantity',
        'unit_price',
        'total_price',
        'notes',
        'sort_order',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
        'sort_order' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the cost estimate this item belongs to.
     */
    public function costEstimate(): BelongsTo
    {
        return $this->belongsTo(CostEstimate::class);
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        // Automatically calculate total price when saving
        static::saving(function ($item) {
            $item->total_price = $item->quantity * $item->unit_price;
        });

        // Recalculate cost estimate total when item is saved or deleted
        static::saved(function ($item) {
            $item->costEstimate->calculateTotalAmount();
        });

        static::deleted(function ($item) {
            $item->costEstimate->calculateTotalAmount();
        });
    }
}