<?php

namespace App\Traits;

use App\Models\Invoice;
use App\Models\Booking;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Validator;

trait InvoiceTrait
{
    /**
     * Calculate invoice details based on booking and returned date
     */
    public function calculateInvoiceDetails($bookingId, $returnedDate)
    {
        $booking = Booking::with(['user', 'car'])->findOrFail($bookingId);
        
        $startDate = Carbon::parse($booking->rental_start_date);
        $endDate = Carbon::parse($booking->rental_end_date);
        $returned = Carbon::parse($returnedDate);
        
        // Validate returned date is after start date
        if ($returned->lessThan($startDate)) {
            throw new \Exception('Returned date cannot be before rental start date.');
        }
        
        // Calculate hours (round up to nearest hour)
        $expectedHours = ceil($startDate->diffInHours($endDate));
        $actualHours = ceil($startDate->diffInHours($returned));
        
        // Extra hours = Actual - Expected (only if positive)
        $extraHours = max(0, $actualHours - $expectedHours);
        
        // Pricing - Get from car
        $pricePerHour = (float) ($booking->car->rent_price_per_hour ?? 0);
        $extraHourRate = $pricePerHour * 2; // Double for extra hours
        
        // Costs
        $baseCost = $expectedHours * $pricePerHour;
        $extraCost = $extraHours * $extraHourRate;
        
        // Discount based on user history (3% for 5+ completed bookings)
        $discount = $this->calculateDiscount($booking->user_id);
        $discountPercentage = $discount['percentage'];
        $discountAmount = ($baseCost + $extraCost) * ($discountPercentage / 100);
        
        // Fine is manually entered, default to 0 (for damages or additional charges)
        $fineAmount = 0;
        $fineReason = null;
        
        // Total cost (without fine - fine will be added separately)
        $totalCost = ($baseCost + $extraCost) - $discountAmount;
        
        return [
            'booking' => $booking,
            'expected_hours' => $expectedHours,
            'actual_hours' => $actualHours,
            'extra_hours' => $extraHours,
            'price_per_hour' => $pricePerHour,
            'extra_hour_rate' => $extraHourRate,
            'base_cost' => $baseCost,
            'extra_cost' => $extraCost,
            'has_extra_hours' => $extraHours > 0,
            'discount_percentage' => $discountPercentage,
            'discount_amount' => $discountAmount,
            'discount_label' => $discount['label'],
            'has_discount' => $discountPercentage > 0,
            'fine_amount' => 0,
            'fine_reason' => null,
            'total_cost' => $totalCost,
        ];
    }

    /**
     * Calculate discount based on user's completed bookings
     * - 5+ completed bookings: 3% discount
     */
    public function calculateDiscount($userId)
    {
        // Count completed bookings for the user
        $completedBookings = Booking::where('user_id', $userId)
            ->where('status', 'completed')
            ->count();

        // Log for debugging
        \Log::info('Discount Check:', [
            'user_id' => $userId,
            'completed_bookings' => $completedBookings,
            'discount_applied' => $completedBookings >= 5 ? 'Yes (3%)' : 'No'
        ]);

        // Apply discount based on number of completed bookings
        if ($completedBookings >= 5) {
            return [
                'percentage' => 3,
                'label' => '3% (Loyal Customer - 5+ completed bookings)'
            ];
        }

        return [
            'percentage' => 0,
            'label' => 'No discount available'
        ];
    }

    /**
     * Validate invoice data
     */
    public function validateInvoiceData(array $data)
    {
        $rules = [
            'booking_id' => ['required', 'exists:tbl_bookings,booking_id'],
            'returned_date' => ['required', 'date'],
            'payment_method' => ['required', 'in:cash,card,bank_transfer'],
            'notes' => ['nullable', 'string', 'max:500'],
            'fine_amount' => ['nullable', 'numeric', 'min:0'],
            'fine_reason' => ['nullable', 'string', 'max:255'],
        ];

        $messages = [
            'booking_id.required' => 'Please select a booking.',
            'booking_id.exists' => 'Selected booking does not exist.',
            'returned_date.required' => 'Please enter the return date.',
            'returned_date.date' => 'Please enter a valid date.',
            'payment_method.required' => 'Please select a payment method.',
            'payment_method.in' => 'Invalid payment method selected.',
            'fine_amount.numeric' => 'Fine amount must be a valid number.',
            'fine_amount.min' => 'Fine amount cannot be negative.',
        ];

        $validator = Validator::make($data, $rules, $messages);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        // Additional validation: returned date must be after rental start
        $booking = Booking::find($data['booking_id']);
        if ($booking) {
            $startDate = Carbon::parse($booking->rental_start_date);
            $returnedDate = Carbon::parse($data['returned_date']);
            
            if ($returnedDate->lessThan($startDate)) {
                throw new \Exception('Returned date cannot be before rental start date.');
            }
        }

        return $validator->validated();
    }

    /**
     * Create invoice
     */
    public function createInvoice(array $data)
    {
        $booking = Booking::with(['user', 'car'])->findOrFail($data['booking_id']);
        
        // Check if invoice already exists for this booking
        $existingInvoice = Invoice::where('booking_id', $data['booking_id'])->first();
        if ($existingInvoice) {
            throw new \Exception('Invoice already exists for this booking.');
        }

        // Check if booking is completed
        if ($booking->status !== 'completed') {
            throw new \Exception('Booking must be completed before generating invoice.');
        }

        // Calculate invoice details
        $details = $this->calculateInvoiceDetails($data['booking_id'], $data['returned_date']);
        
        // Use custom fine if provided (for damages or additional charges)
        $fineAmount = (float) ($data['fine_amount'] ?? 0);
        $fineReason = $data['fine_reason'] ?? null;
        
        // Recalculate total with fine
        $totalCost = ($details['base_cost'] + $details['extra_cost']) 
            - $details['discount_amount'] 
            + $fineAmount;

        // Log invoice creation
        \Log::info('Creating Invoice:', [
            'user_id' => $booking->user_id,
            'completed_bookings' => Booking::where('user_id', $booking->user_id)->where('status', 'completed')->count(),
            'discount_percentage' => $details['discount_percentage'],
            'discount_amount' => $details['discount_amount'],
            'base_cost' => $details['base_cost'],
            'extra_cost' => $details['extra_cost'],
            'total_cost' => $totalCost,
        ]);

        // Create invoice
        $invoice = Invoice::create([
            'invoice_ref_no' => Invoice::generateReference(),
            'booking_id' => $data['booking_id'],
            'user_id' => $booking->user_id,
            'car_id' => $booking->car_id,
            'returned_date' => $data['returned_date'],
            'actual_hours' => $details['actual_hours'],
            'extra_cost' => $details['extra_cost'],
            'discount_amount' => $details['discount_amount'],
            'discount_percentage' => $details['discount_percentage'],
            'fine_amount' => $fineAmount,
            'fine_reason' => $fineReason,
            'total_cost' => $totalCost,
            'payment_status' => 'paid',
            'payment_method' => $data['payment_method'],
            'paid_at' => now(),
            'notes' => $data['notes'] ?? null,
        ]);

        return [
            'success' => true,
            'message' => 'Invoice created successfully! Reference: ' . $invoice->invoice_ref_no,
            'invoice' => $invoice
        ];
    }

    /**
     * Format invoice for response (with related data)
     */
    public function formatInvoiceForResponse($invoice)
    {
        return [
            'id' => $invoice->invoice_id,
            'ref_no' => $invoice->invoice_ref_no,
            'booking_ref' => $invoice->booking->booking_ref_no ?? null,
            'customer' => [
                'name' => $invoice->user->name ?? null,
                'nic' => $invoice->user->id_num ?? null,
                'email' => $invoice->user->email ?? null,
            ],
            'car' => [
                'name' => $invoice->car->name ?? null,
                'ref_no' => $invoice->car->ref_no ?? null,
                'number_plate' => $invoice->car->number_plate ?? null,
                'price_per_hour' => $invoice->car->rent_price_per_hour ?? null,
            ],
            'rental' => [
                'start_date' => $invoice->booking->rental_start_date ?? null,
                'end_date' => $invoice->booking->rental_end_date ?? null,
                'returned_date' => $invoice->returned_date,
                'actual_hours' => $invoice->actual_hours,
                'expected_hours' => $invoice->booking ? $invoice->booking->getDurationInHours() : 0,
                'extra_hours' => max(0, $invoice->actual_hours - ($invoice->booking ? $invoice->booking->getDurationInHours() : 0)),
            ],
            'cost' => [
                'base_cost' => $invoice->booking ? ($invoice->booking->getDurationInHours() * ($invoice->car->rent_price_per_hour ?? 0)) : 0,
                'extra_cost' => $invoice->extra_cost,
                'discount_amount' => $invoice->discount_amount,
                'discount_percentage' => $invoice->discount_percentage,
                'fine_amount' => $invoice->fine_amount,
                'fine_reason' => $invoice->fine_reason,
                'total_cost' => $invoice->total_cost,
            ],
            'payment' => [
                'status' => $invoice->payment_status,
                'method' => $invoice->payment_method,
                'paid_at' => $invoice->paid_at,
            ],
            'notes' => $invoice->notes,
            'created_at' => $invoice->created_at,
        ];
    }
}