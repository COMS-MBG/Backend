<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StockItem extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'stock_items';

    protected $fillable = [
        'sppg_id',
        'ingredient_id',
        'batch_number',
        'quantity',
        'unit',
        'price_per_unit',
        'purchase_date',
        'expiry_date',
        'supplier',
        'storage_type',
        'storage_location',
        'sku',
        'notes',
        'status',
        'approved_by',
        'approved_at',
        'proof_document',
        'created_by',
    ];

    protected $casts = [
        'quantity' => 'float',
        'price_per_unit' => 'float',
        'purchase_date' => 'date',
        'expiry_date' => 'date',
        'approved_at' => 'datetime',
    ];

    public function sppg()
    {
        return $this->belongsTo(SPPG::class, 'sppg_id');
    }

    public function ingredient()
    {
        return $this->belongsTo(Ingredient::class, 'ingredient_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function transactions()
    {
        return $this->hasMany(StockTransaction::class, 'stock_item_id');
    }
}
