<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\DeliverySchedule;
use App\Models\Employee;

class CourierLocation extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'delivery_schedule_id',
        'courier_id',
        'latitude',
        'longitude',
        'speed_kmh',
        'heading_degrees',
        'accuracy_meters',
        'recorded_at',
    ];

    protected $casts = [
        'latitude'        => 'float',
        'longitude'       => 'float',
        'speed_kmh'       => 'float',
        'heading_degrees' => 'float',
        'accuracy_meters' => 'float',
        'recorded_at'     => 'datetime',
    ];

    public function schedule()
    {
        return $this->belongsTo(DeliverySchedule::class, 'delivery_schedule_id');
    }

    public function courier()
    {
        return $this->belongsTo(Employee::class, 'courier_id');
    }
}