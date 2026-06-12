<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;

class StockShortageException extends Exception
{
    protected array $shortages;

    public function __construct(array $shortages)
    {
        parent::__construct('Stok tidak mencukupi untuk publish menu.');
        $this->shortages = $shortages;
    }

    public function render(): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => 'Stok tidak mencukupi untuk publish menu.',
            'shortages' => $this->shortages,
        ], 422);
    }
}
