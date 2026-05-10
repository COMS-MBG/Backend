<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * RESOURCE untuk output Menu (Perencanaan Menu).
 */
class MenuResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'name'       => $this->name,
            'week_start' => $this->week_start?->format('Y-m-d'),
            'week_end'   => $this->week_end?->format('Y-m-d'),
            'notes'      => $this->notes,

            // Status (kode untuk FE)
            'status'       => $this->status,
            // Label status dalam Bahasa Indonesia
            'status_label' => $this->status_label,

            // Label rentang minggu yang friendly
            'week_range_label' => $this->week_range_label,

            // Items dikelompokkan per hari
            'items' => $this->whenLoaded('menuItems', function () {
                return $this->menuItems->groupBy('day_of_week')->map(function ($dayItems, $dayNumber) {
                    return [
                        'day_of_week' => $dayNumber,
                        'day_name'    => $dayItems->first()->day_name,
                        'date'        => $dayItems->first()->menu_date?->format('Y-m-d'),
                        'recipes'     => $dayItems->map(fn($item) => [
                            'menu_item_id'    => $item->id,
                            'meal_time'       => $item->meal_time,
                            'meal_time_label' => $item->meal_time_label,
                            'order'           => $item->order,
                            'recipe'          => $item->recipe ? [
                                'id'            => $item->recipe->id,
                                'name'          => $item->recipe->name,
                                'total_calorie' => $item->recipe->total_calorie,
                                'total_protein' => $item->recipe->total_protein,
                            ] : null,
                        ])->values(),
                    ];
                })->values();
            }),
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}