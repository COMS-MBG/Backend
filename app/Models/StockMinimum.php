<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockMinimum extends Model
{
    use HasFactory;

    protected $table = 'stock_minimum';

    protected $fillable = [
        'sppg_id',
        'ingredient_id',
        'minimum_quantity',
        'unit',
    ];

    protected $casts = [
        'minimum_quantity' => 'float',
    ];

    public function sppg()
    {
        return $this->belongsTo(SPPG::class, 'sppg_id');
    }

    public function ingredient()
    {
        return $this->belongsTo(Ingredient::class, 'ingredient_id');
    }
}
