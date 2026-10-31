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
        $latFrom = deg2rad($this->current_latitude);
        $lonFrom = deg2rad($this->current_longitude);
        $latTo = deg2rad($latitude);
        $lonTo = deg2rad($longitude);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
            cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));

        return round($angle * $earthRadius, 2);
    }
}

