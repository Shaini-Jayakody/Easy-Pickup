<?php

namespace App\Traits;

use App\Models\Booking;
use App\Models\CarDetail\Car;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

trait BookingTrait
{
    /**
     * Get all bookings with relationships
     */
    public function getAllBookings()
    {
        return Booking::with(['user', 'car'])->orderBy('created_at', 'desc');
    }

    /**
     * Get bookings by status
     */
    public function getBookingsByStatus($status)
    {
        return Booking::with(['user', 'car'])->where('status', $status)->orderBy('created_at', 'desc');
    }

    /**
     * Get bookings by user
     */
    public function getBookingsByUser($userId)
    {
        return Booking::with(['car'])->where('user_id', $userId)->orderBy('created_at', 'desc');
    }

    /**
     * Get bookings by car
     */
    public function getBookingsByCar($carId)
    {
        return Booking::with(['user'])->where('car_id', $carId)->orderBy('created_at', 'desc');
    }

    /**
     * Get a single booking by ID
     */
    public function getBookingById($id)
    {
        return Booking::with(['user', 'car'])->findOrFail($id);
    }

    /**
     * Get booking by reference number
     */
    public function getBookingByRef(string $refNo): ?Booking
    {
        return Booking::where('booking_ref_no', $refNo)->first();
    }

    /**
     * Create a new booking
     */
    public function createBooking(array $data): array
    {
        // Check if car is available
        $this->validateCarAvailability($data['car_id'], $data['rental_start_date'], $data['rental_end_date']);

        $booking = Booking::create([
            'booking_ref_no' => $this->generateBookingReference(),
            'user_id' => Auth::id(),
            'car_id' => $data['car_id'],
            'rental_start_date' => $data['rental_start_date'],
            'rental_end_date' => $data['rental_end_date'],
            'status' => 'pending',
            'notes' => $data['notes'] ?? null,
        ]);

        return [
            'success' => true,
            'message' => "Booking created successfully! Reference: {$booking->booking_ref_no}",
            'booking' => $booking
        ];
    }

    /**
     * Update booking status
     */
    public function updateBookingStatus($id, string $action): array
    {
        $booking = $this->getBookingById($id);
        
        // Define allowed transitions
        $allowedTransitions = [
            'confirm' => ['pending' => 'confirmed'],
            'issue' => ['confirmed' => 'active'],
            'return' => ['active' => 'returned'],
            'complete' => ['returned' => 'completed'],
            'cancel' => ['pending' => 'cancelled', 'confirmed' => 'cancelled'],
        ];

        if (!isset($allowedTransitions[$action])) {
            throw new \Exception('Invalid action.');
        }

        $transition = $allowedTransitions[$action];
        if (!isset($transition[$booking->status])) {
            throw new \Exception('Cannot perform this action on current status.');
        }

        $newStatus = $transition[$booking->status];
        $booking->status = $newStatus;
        $booking->save();

        $statusMessages = [
            'confirmed' => 'Booking confirmed successfully!',
            'active' => 'Car issued successfully!',
            'returned' => 'Car returned successfully!',
            'completed' => 'Booking completed successfully!',
            'cancelled' => 'Booking cancelled successfully!',
        ];

        return [
            'success' => true,
            'message' => $statusMessages[$newStatus] ?? 'Status updated successfully!',
            'booking' => $booking
        ];
    }

    /**
     * Check if car is available for the given dates
     */
    public function validateCarAvailability($carId, $startDate, $endDate): void
    {
        $exists = Booking::where('car_id', $carId)
            ->whereIn('status', ['confirmed', 'active'])
            ->where(function($query) use ($startDate, $endDate) {
                $query->whereBetween('rental_start_date', [$startDate, $endDate])
                      ->orWhereBetween('rental_end_date', [$startDate, $endDate]);
            })
            ->exists();

        if ($exists) {
            throw new \Exception('This car is not available for the selected dates.');
        }
    }

    /**
     * Check if car is available (for AJAX)
     */
    public function isCarAvailable($carId, $startDate, $endDate): bool
    {
        $exists = Booking::where('car_id', $carId)
            ->whereIn('status', ['confirmed', 'active'])
            ->where(function($query) use ($startDate, $endDate) {
                $query->whereBetween('rental_start_date', [$startDate, $endDate])
                      ->orWhereBetween('rental_end_date', [$startDate, $endDate]);
            })
            ->exists();

        return !$exists;
    }

    /**
     * Get booking statistics
     */
    public function getBookingStats(): array
    {
        return [
            'total' => Booking::count(),
            'pending' => Booking::where('status', 'pending')->count(),
            'confirmed' => Booking::where('status', 'confirmed')->count(),
            'active' => Booking::where('status', 'active')->count(),
            'returned' => Booking::where('status', 'returned')->count(),
            'completed' => Booking::where('status', 'completed')->count(),
            'cancelled' => Booking::where('status', 'cancelled')->count(),
        ];
    }

    /**
     * Get user's rental history count
     */
    public function getUserRentalCount($userId): int
    {
        return Booking::where('user_id', $userId)
            ->whereIn('status', ['completed', 'returned'])
            ->count();
    }

    /**
     * Get active bookings count by car
     */
    public function getActiveBookingsByCar($carId): int
    {
        return Booking::where('car_id', $carId)
            ->whereIn('status', ['confirmed', 'active'])
            ->count();
    }

    /**
     * Generate unique booking reference
     */
    public function generateBookingReference(): string
    {
        do {
            $refNo = 'BK' . date('ymd') . rand(1000, 9999);
        } while ($this->getBookingByRef($refNo));
        
        return $refNo;
    }

    /**
     * Validate booking data
     */
    public function validateBookingData(array $data, $excludeId = null)
    {
        $rules = [
            'car_id' => ['required', 'exists:tbl_cars,id'],
            'rental_start_date' => ['required', 'date', 'after:now'],
            'rental_end_date' => ['required', 'date', 'after:rental_start_date'],
            'notes' => ['nullable', 'string', 'max:500'],
        ];

        $messages = [
            'car_id.required' => 'Please select a car.',
            'car_id.exists' => 'Selected car does not exist.',
            'rental_start_date.required' => 'Please select rental start date.',
            'rental_start_date.date' => 'Please enter a valid date.',
            'rental_start_date.after' => 'Rental must start in the future.',
            'rental_end_date.required' => 'Please select rental end date.',
            'rental_end_date.date' => 'Please enter a valid date.',
            'rental_end_date.after' => 'Rental end must be after start date.',
            'notes.max' => 'Notes cannot exceed 500 characters.',
        ];

        $validator = \Illuminate\Support\Facades\Validator::make($data, $rules, $messages);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $validator->validated();
    }

    /**
     * Check if user has permission to manage bookings
     */
    public function hasBookingPermission(): bool
    {
        if (!Auth::check()) {
            return false;
        }
        return in_array(Auth::user()->role, ['admin', 'manager']);
    }

    /**
     * Get booking status badge class
     */
    public function getStatusBadge(string $status): string
    {
        return match($status) {
            'pending' => 'label label-warning',
            'confirmed' => 'label label-info',
            'active' => 'label label-success',
            'returned' => 'label label-primary',
            'completed' => 'label label-success',
            'cancelled' => 'label label-danger',
            default => 'label label-default'
        };
    }

    /**
     * Get booking status text
     */
    public function getStatusText(string $status): string
    {
        return match($status) {
            'pending' => 'Pending',
            'confirmed' => 'Confirmed',
            'active' => 'Active',
            'returned' => 'Returned',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
            default => ucfirst($status)
        };
    }

    /**
     * Get allowed status transitions
     */
    public function getAllowedTransitions(): array
    {
        return [
            'confirm' => ['pending' => 'confirmed', 'label' => 'Confirm', 'btn' => 'success'],
            'issue' => ['confirmed' => 'active', 'label' => 'Issue Car', 'btn' => 'primary'],
            'return' => ['active' => 'returned', 'label' => 'Return Car', 'btn' => 'warning'],
            'complete' => ['returned' => 'completed', 'label' => 'Complete', 'btn' => 'success'],
            'cancel' => ['pending' => 'cancelled', 'confirmed' => 'cancelled', 'label' => 'Cancel', 'btn' => 'danger'],
        ];
    }

    /**
     * Get allowed actions for a booking status
     */
    public function getAllowedActions(string $status): array
    {
        $actions = [];
        $transitions = $this->getAllowedTransitions();

        foreach ($transitions as $action => $transition) {
            if (isset($transition[$status])) {
                $actions[] = $action;
            }
        }

        return $actions;
    }

    /**
     * Format booking for API response
     */
    public function formatBookingForResponse($booking): array
    {
        return [
            'id' => $booking->booking_id,
            'ref_no' => $booking->booking_ref_no,
            'user' => [
                'id' => $booking->user_id,
                'name' => $booking->user->name ?? null,
                'nic' => $booking->user->id_num ?? null,
                'email' => $booking->user->email ?? null,
            ],
            'car' => [
                'id' => $booking->car->id ?? null,
                'name' => $booking->car->name ?? null,
                'plate_no' => $booking->car->number_plate ?? null,
                'ref_no' => $booking->car->ref_no ?? null,
                'price_per_hour' => $booking->car->rent_price_per_hour ?? null,
            ],
            'rental_start' => $booking->rental_start_date,
            'rental_end' => $booking->rental_end_date,
            'duration' => $booking->getDurationInHours(),
            'status' => $booking->status,
            'status_text' => $this->getStatusText($booking->status),
            'status_badge' => $this->getStatusBadge($booking->status),
            'notes' => $booking->notes,
            'created_at' => $booking->created_at,
        ];
    }
}