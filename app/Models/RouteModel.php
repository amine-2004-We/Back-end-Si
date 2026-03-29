<?php

namespace App\Models;

use App\Enums\TransportMode;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RouteModel extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * @var string
     */
    protected $table = 'routes';

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'route_code',
        'departure_location_name',
        'departure_latitude',
        'departure_longitude',
        'arrival_location_name',
        'arrival_latitude',
        'arrival_longitude',
        'transport_mode',
        'rate',
        'scale_price',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'transport_mode' => TransportMode::class,
        'rate' => 'decimal:2',
        'scale_price' => 'decimal:2',
    ];
}
