<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    use HasFactory;

    protected $fillable = [
        'property_type',
        'property_name',
        'property_category',
        'room_number',
        'name',
        'type_label',
        'description',
        'image',
        'price_weekly',
        'price_monthly',
        'price_daily',
        'allowed_rental_types',
        'features',
        'max_guests',
        'size_sqm',
        'floor',
        'status',
        'notes',
    ];

    protected $casts = [
        'features' => 'array',
        'price_weekly' => 'float',
        'price_monthly' => 'float',
        'price_daily' => 'float',
        'max_guests' => 'integer',
        'size_sqm' => 'integer',
    ];

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function activeBooking()
    {
        return $this->hasOne(Booking::class)->whereIn('status', ['confirmed', 'active'])->latestOfMany();
    }
}
