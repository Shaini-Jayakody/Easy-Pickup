<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\CarDetail\Car;

class Booking extends Model
{
    use HasFactory;

    protected $table = 'tbl_bookings';
    protected $primaryKey = 'booking_id';

    protected $fillable = [
        'booking_ref_no',
        'user_id',
        'car_id',
        'rental_start_date',
        'rental_end_date',
        'status',
        'notes'
    ];

    protected $casts = [
        'rental_start_date' => 'datetime',
        'rental_end_date' => 'datetime',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function car()
    {
        return $this->belongsTo(Car::class, 'car_id', 'id');
    }

    // Helper methods
    public function isPending()
    {
        return $this->status === 'pending';
    }

    public function isConfirmed()
    {
        return $this->status === 'confirmed';
    }

    public function isActive()
    {
        return $this->status === 'active';
    }

    public function isReturned()
    {
        return $this->status === 'returned';
    }

    public function isCompleted()
    {
        return $this->status === 'completed';
    }

    public function isCancelled()
    {
        return $this->status === 'cancelled';
    }

    public function canBeCancelled()
    {
        return in_array($this->status, ['pending', 'confirmed']);
    }

    public function getDurationInHours()
    {
        return $this->rental_start_date->diffInHours($this->rental_end_date);
    }

    public function getStatusBadgeClass()
    {
        return match($this->status) {
            'pending' => 'label label-warning',
            'confirmed' => 'label label-info',
            'active' => 'label label-success',
            'returned' => 'label label-primary',
            'completed' => 'label label-success',
            'cancelled' => 'label label-danger',
            default => 'label label-default'
        };
    }

    public function getStatusText()
    {
        return match($this->status) {
            'pending' => 'Pending',
            'confirmed' => 'Confirmed',
            'active' => 'Active',
            'returned' => 'Returned',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
            default => ucfirst($this->status)
        };
    }

    // Generate unique booking reference
    public static function generateReference()
    {
        do {
            $refNo = 'BK' . date('ymd') . rand(1000, 9999);
        } while (self::where('booking_ref_no', $refNo)->exists());
        
        return $refNo;
    }
}