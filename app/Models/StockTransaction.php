<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockTransaction extends Model
{
    use HasFactory;

    protected $table = 'stock_transactions';

    // Immutable: no updated_at column
    const UPDATED_AT = null;

    protected $fillable = [
        'sppg_id',
        'stock_item_id',
        'ingredient_id',
        'transaction_type',
        'quantity',
        'quantity_before',
        'quantity_after',
        'reference_type',
        'reference_id',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'quantity' => 'float',
        'quantity_before' => 'float',
        'quantity_after' => 'float',
        'created_at' => 'datetime',
    ];

    public function sppg()
    {
        return $this->belongsTo(SPPG::class, 'sppg_id');
    }

    public function stockItem()
    {
        return $this->belongsTo(StockItem::class, 'stock_item_id');
    }

    public function ingredient()
    {
        return $this->belongsTo(Ingredient::class, 'ingredient_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
