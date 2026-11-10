<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeliveryPartner extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'is_online' => 'boolean',
        'is_verified' => 'boolean',
        'current_latitude' => 'float',
        'current_longitude' => 'float',
        'last_active_at' => 'datetime',
        'rating' => 'float',
        'total_deliveries' => 'integer',
        'base_delivery_fee' => 'float',
        'per_km_rate' => 'float',
        'max_delivery_radius_km' => 'float',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function assignments()
    {
        return $this->hasMany(DeliveryAssignment::class);
    }

    public function activeAssignments()
    {
        return $this->hasMany(DeliveryAssignment::class)->whereIn('status', ['assigned', 'accepted', 'picked_up']);
    }

    /**
     * Compute approximate distance in KM from a target coordinate (Haversine formula)
     */
    public function distanceTo($latitude, $longitude): float
    {
        if (!$this->current_latitude || !$this->current_longitude || !$latitude || !$longitude) {
            return 1.2; // default fallback 1.2 km in town
        }

        $earthRadius = 6371; // km
        $latFrom = deg2rad((float) $this->current_latitude);
        $lonFrom = deg2rad((float) $this->current_longitude);
        $latTo = deg2rad((float) $latitude);
        $lonTo = deg2rad((float) $longitude);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $haversine = pow(sin($latDelta / 2), 2) +
            cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2);
        $angle = 2 * asin(min(1.0, sqrt(max(0.0, $haversine))));

        return round($angle * $earthRadius, 2);
    }

    /**
     * Compute dynamic real-time delivery fee based on distance (Blinkit style)
     */
    public function calculateDeliveryFee(float $distanceKm): float
    {
        $base = (float) ($this->base_delivery_fee ?? 30.00);
        $perKm = (float) ($this->per_km_rate ?? 15.00);
        
        // Base fee covers first 2.0 km. Beyond that, per_km_rate is added.
        $extraKm = max(0.0, $distanceKm - 2.0);
        $totalFee = $base + ($extraKm * $perKm);

        return round(max($base, $totalFee), 2);
    }
}

