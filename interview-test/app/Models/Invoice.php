<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
        return $this->belongsTo(CarDetail\Car::class, 'car_id', 'id');
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
        return match($this->payment_status) {
            'paid' => 'label-success',
            'pending' => 'label-warning',
            'failed' => 'label-danger',
            default => 'label-default'
        };
    }

    public function getTotalCostFormatted()
    {
        return 'Rs. ' . number_format($this->total_cost, 2);
    }

    // ===== Generate Invoice Reference =====
    public static function generateReference()
    {
        do {
            $refNo = 'INV' . date('Ymd') . rand(1000, 9999);
        } while (self::where('invoice_ref_no', $refNo)->exists());
        
        return $refNo;
    }

    // ===== Calculate Discount Based on User History =====
    public static function calculateDiscount($userId)
    {
        $bookingCount = Booking::where('user_id', $userId)
            ->whereIn('status', ['completed', 'returned'])
            ->count();

        if ($bookingCount >= 10) {
            return ['percentage' => 20, 'label' => '20% (Loyal Customer - 10+ bookings)'];
        } elseif ($bookingCount >= 5) {
            return ['percentage' => 10, 'label' => '10% (Frequent Renter - 5+ bookings)'];
        } elseif ($bookingCount >= 3) {
            return ['percentage' => 5, 'label' => '5% (Regular Customer - 3+ bookings)'];
        }

        return ['percentage' => 0, 'label' => 'No discount available'];
    }
}