<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\CarDetail\Car;
use App\Traits\DurationCostTrait;
use App\Traits\BookingStatusTrait;

class Booking extends Model
{
    use HasFactory;
    use DurationCostTrait;
    use BookingStatusTrait;

    protected $table = 'tbl_bookings';
    protected $primaryKey = 'booking_id';

    protected $fillable = [
        'booking_ref_no',
        'user_id',
        'car_id',
        'rental_start_date',
        'rental_end_date',
        'estimated_cost',
        'status',
        'notes'
    ];

    protected $casts = [
        'rental_start_date' => 'datetime',
        'rental_end_date' => 'datetime',
        'estimated_cost' => 'decimal:2',
    ];

    
    // RELATIONSHIPS
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function car()
    {
        return $this->belongsTo(Car::class, 'car_id', 'id');
    }

    public function invoice()
    {
        return $this->hasOne(Invoice::class, 'booking_id', 'booking_id');
    }


    // REFERENCE GENERATION

    /**
     * Generate unique booking reference
     */
    public static function generateReference()
    {
        do {
            $refNo = 'BK' . date('ymd') . rand(1000, 9999);
        } while (self::where('booking_ref_no', $refNo)->exists());
        
        return $refNo;
    }

    // BOOT METHOD
    protected static function booted()
    {
        // Auto-calculate estimated cost on create
        static::creating(function ($booking) {
            if ($booking->car_id && $booking->rental_start_date && $booking->rental_end_date) {
                $duration = $booking->getDurationInHours();
                $pricePerHour = $booking->car->rent_price_per_hour ?? 0;
                $booking->estimated_cost = $duration * $pricePerHour;
            }
        });

        // Auto-calculate estimated cost on update
        static::updating(function ($booking) {
            if ($booking->isDirty(['car_id', 'rental_start_date', 'rental_end_date'])) {
                if ($booking->car_id && $booking->rental_start_date && $booking->rental_end_date) {
                    $duration = $booking->getDurationInHours();
                    $pricePerHour = $booking->car->rent_price_per_hour ?? 0;
                    $booking->estimated_cost = $duration * $pricePerHour;
                }
            }
        });
    }
}