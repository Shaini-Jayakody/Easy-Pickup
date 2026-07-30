<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\CarDetail\Car;  // Make sure this path is correct

class Invoice extends Model
{
    use HasFactory;

    protected $table = 'tbl_invoices';
    protected $primaryKey = 'invoice_id';

    protected $fillable = [
        'invoice_ref_no',
        'booking_id',
        'user_id',
        'car_id',
        'returned_date',
        'actual_hours',
        'extra_cost',
        'discount_amount',
        'discount_percentage',
        'fine_amount',
        'fine_reason',
        'total_cost',
        'payment_status',
        'payment_method',
        'paid_at',
        'notes'
    ];

    protected $casts = [
        'returned_date' => 'datetime',
        'paid_at' => 'datetime',
        'actual_hours' => 'decimal:2',
        'extra_cost' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'discount_percentage' => 'decimal:2',
        'fine_amount' => 'decimal:2',
        'total_cost' => 'decimal:2'
    ];

    // ===== Relationships =====
    public function booking()
    {
        return $this->belongsTo(Booking::class, 'booking_id', 'booking_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function car()
    {
        return $this->belongsTo(Car::class, 'car_id', 'id');
    }

    // ===== Helper Methods =====
    public function isPaid()
    {
        return $this->payment_status === 'paid';
    }

    public function isPending()
    {
        return $this->payment_status === 'pending';
    }

    public function getStatusBadgeClass()
    {
        return match($this->payment_status ?? 'pending') {
            'paid' => 'label-success',
            'pending' => 'label-warning',
            'failed' => 'label-danger',
            default => 'label-default'
        };
    }

    public function getTotalCostFormatted()
    {
        return 'Rs. ' . number_format($this->total_cost ?? 0, 2);
    }

    public static function generateReference()
    {
        do {
            $refNo = 'INV' . date('Ymd') . rand(1000, 9999);
        } while (self::where('invoice_ref_no', $refNo)->exists());
        
        return $refNo;
    }

    public static function calculateDiscount($userId)
    {
        $completedBookings = Booking::where('user_id', $userId)
            ->where('status', 'completed')
            ->count();

        if ($completedBookings >= 5) {
            return ['percentage' => 3, 'label' => '3% (Loyal Customer - 5+ bookings)'];
        }

        return ['percentage' => 0, 'label' => 'No discount available'];
    }
}