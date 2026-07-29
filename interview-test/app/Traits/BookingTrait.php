<?php

namespace App\Traits;

use App\Models\Booking;
use App\Models\CarDetail\Car;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Translation\Translator;
use Illuminate\Validation\Factory as ValidationFactory;
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
        $this->validateRentalDuration($data['rental_start_date'], $data['rental_end_date']);
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
     * Cancel a booking (only if pending and before start date)
     */
    public function cancelBooking($id): array
    {
        $booking = $this->getBookingById($id);
        
        // Check if booking can be cancelled
        if (!$booking->isPending()) {
            throw new \Exception('Only pending bookings can be cancelled.');
        }

        // Check if start date is in the future
        $now = Carbon::now();
        $startDate = Carbon::parse($booking->rental_start_date);
        
        if ($startDate->lessThanOrEqualTo($now)) {
            throw new \Exception('Cannot cancel a booking that has already started.');
        }

        // Update status to cancelled
        $booking->status = 'cancelled';
        $booking->save();

        return [
            'success' => true,
            'message' => 'Booking cancelled successfully!',
            'booking' => $booking
        ];
    }

    /**
     * Check if car is available for the given dates
     */
    public function validateCarAvailability($carId, $startDate, $endDate): void
    {
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);

        $exists = Booking::where('car_id', $carId)
            ->whereIn('status', ['pending', 'confirmed', 'active'])
            ->where(function($query) use ($start, $end) {
                $query->where('rental_start_date', '<', $end)
                      ->where('rental_end_date', '>', $start);
            })
            ->exists();

        if ($exists) {
            throw new \Exception('This car is not available for the selected dates.');
        }
    }

    /**
     * Check if car is available for update (excluding current booking)
     */
    public function validateCarAvailabilityForUpdate($bookingId, $carId, $startDate, $endDate): void
    {
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);

        $exists = Booking::where('car_id', $carId)
            ->where('booking_id', '!=', $bookingId)
            ->whereIn('status', ['pending', 'confirmed', 'active'])
            ->where(function($query) use ($start, $end) {
                $query->where('rental_start_date', '<', $end)
                      ->where('rental_end_date', '>', $start);
            })
            ->exists();

        if ($exists) {
            throw new \Exception('This car is not available for the selected dates.');
        }
    }

    /**
     * Check if car is available (for AJAX)
     */
public function isCarAvailable($carId, $startDate, $endDate, $excludeBookingId = null): bool
{
    if (!$carId || !$startDate || !$endDate) {
        return false;
    }

    $start = Carbon::parse(trim($startDate));
    $end = Carbon::parse(trim($endDate));

    if ($end->lessThanOrEqualTo($start)) {
        return false;
    }

    $query = Booking::where('car_id', $carId)
        ->whereIn('status', ['pending', 'confirmed', 'active'])
        ->where(function($q) use ($start, $end) {
            $q->where('rental_start_date', '<', $end)
              ->where('rental_end_date', '>', $start);
        });

    // Exclude current booking when editing
    if ($excludeBookingId) {
        $query->where('booking_id', '!=', $excludeBookingId);
    }

    return !$query->exists();
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
            'user_id' => ['nullable', 'exists:users,user_id'],
            'rental_start_date' => ['required', 'date', 'after:now'],
            'rental_end_date' => ['required', 'date', 'after:rental_start_date'],
            'notes' => ['nullable', 'string', 'max:500'],
        ];

        $messages = [
            'car_id.required' => 'Please select a car.',
            'car_id.exists' => 'Selected car does not exist.',
            'user_id.exists' => 'Selected user does not exist.',
            'rental_start_date.required' => 'Please select rental start date.',
            'rental_start_date.date' => 'Please enter a valid date.',
            'rental_start_date.after' => 'Rental must start in the future.',
            'rental_end_date.required' => 'Please select rental end date.',
            'rental_end_date.date' => 'Please enter a valid date.',
            'rental_end_date.after' => 'Rental end must be after start date.',
            'notes.max' => 'Notes cannot exceed 500 characters.',
        ];

        // For updates, we need different validation rules
        if ($excludeId) {
            $rules['rental_start_date'] = ['required', 'date'];
            $rules['rental_end_date'] = ['required', 'date', 'after:rental_start_date'];
        }

        $validator = Validator::make($data, $rules, $messages);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $this->validateRentalDuration($data['rental_start_date'], $data['rental_end_date']);

        return $validator->validated();
    }

    /**
     * Validate booking update (with additional restrictions)
     */
    public function validateBookingUpdate(array $data, $bookingId)
    {
        $booking = $this->getBookingById($bookingId);
        
        // Only pending bookings can be updated
        if (!$booking->isPending()) {
            throw new \Exception('Only pending bookings can be updated.');
        }

        // Check if start date is already passed
        $currentStartDate = Carbon::parse($booking->rental_start_date);
        $now = Carbon::now();
        
        if ($currentStartDate->lessThanOrEqualTo($now)) {
            throw new \Exception('Cannot update a booking that has already started or is in the past.');
        }

        // Validate the data
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

        $validator = Validator::make($data, $rules, $messages);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        // Check duration
        $this->validateRentalDuration($data['rental_start_date'], $data['rental_end_date']);

        return $validator->validated();
    }

    /**
     * Calculate the rental duration in hours.
     */
    public function getRentalDurationInHours($startDate, $endDate): int
    {
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);

        return (int) $start->diffInHours($end);
    }

    /**
     * Validate the requested rental window.
     */
    public function validateRentalDuration($startDate, $endDate): void
    {
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);

        if ($end->lessThanOrEqualTo($start)) {
            $this->throwValidationException([
                'rental_end_date' => ['Rental end date must be after rental start date.'],
            ]);
        }

        $durationInMinutes = $start->diffInMinutes($end, false);
        if ($durationInMinutes < 120) {
            $this->throwValidationException([
                'rental_end_date' => ['Rental duration must be at least 2 hours.'],
            ]);
        }

        $maxAllowedEnd = $start->copy()->addDays(30);
        if ($end->greaterThan($maxAllowedEnd)) {
            $this->throwValidationException([
                'rental_end_date' => ['Rental duration cannot exceed 1 month.'],
            ]);
        }
    }

    protected function throwValidationException(array $messages): void
    {
        $validator = $this->getValidationFactory()->make([], []);

        foreach ($messages as $key => $value) {
            foreach (Arr::wrap($value) as $message) {
                $validator->errors()->add($key, $message);
            }
        }

        throw new ValidationException($validator);
    }

    protected function getValidationFactory(): ValidationFactory
    {
        if (function_exists('app') && app()->bound('validator')) {
            return app('validator');
        }

        return new ValidationFactory(new Translator(new \Illuminate\Translation\ArrayLoader, 'en'));
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
            'booking_ref_no' => $booking->booking_ref_no,
            'ref_no' => $booking->booking_ref_no,
            'user' => [
                'id' => $booking->user_id,
                'name' => $booking->user->name ?? null,
                'nic' => $booking->user->id_num ?? null,
                'id_num' => $booking->user->id_num ?? null,
                'email' => $booking->user->email ?? null,
            ],
            'car' => [
                'id' => $booking->car->id ?? null,
                'name' => $booking->car->name ?? null,
                'plate_no' => $booking->car->number_plate ?? null,
                'number_plate' => $booking->car->number_plate ?? null,
                'ref_no' => $booking->car->ref_no ?? null,
                'price_per_hour' => $booking->car->rent_price_per_hour ?? null,
            ],
            'rental_start' => $booking->rental_start_date,
            'rental_end' => $booking->rental_end_date,
            'rental_start_date' => $booking->rental_start_date,
            'rental_end_date' => $booking->rental_end_date,
            'duration' => $booking->getDurationInHours(),
            'duration_in_hours' => $booking->getDurationInHours(),
            'status' => $booking->status,
            'status_text' => $this->getStatusText($booking->status),
            'status_badge' => $this->getStatusBadge($booking->status),
            'notes' => $booking->notes,
            'created_at' => $booking->created_at,
        ];
    }

    /**
     * Check if user can edit the booking
     */
    public function canEditBooking($booking): bool
    {
        if (!$booking) return false;
        
        // Only pending bookings can be edited
        if (!$booking->isPending()) return false;
        
        // User must own the booking
        if (!Auth::check()) return false;

        $authenticatedUserId = (int) Auth::id();
        $bookingOwnerId = (int) $booking->user_id;
        if ($authenticatedUserId !== $bookingOwnerId) return false;

        // Check if start date is in the future
        $now = Carbon::now();
        $startDate = Carbon::parse($booking->rental_start_date);
        if ($startDate->lessThanOrEqualTo($now)) {
            return false;
        }
        
        return true;
    }

   /**
 * Check if user can cancel the booking
 */
public function canCancelBooking($booking): bool
{
    if (!$booking) return false;
    
    // Only pending bookings can be cancelled
    if (!$booking->isPending()) return false;
    
    // User must be logged in
    if (!Auth::check()) return false;

    // Check if start date is in the future
    $now = Carbon::now();
    $startDate = Carbon::parse($booking->rental_start_date);
    if ($startDate->lessThanOrEqualTo($now)) {
        return false;
    }
    
    return true;
}
      

    /**
     * Get the edit route for a booking
     */
    public function getEditRoute($booking): string
    {
        return route('bookings.edit', $booking->booking_id);
    }

    /**
     * Get the cancel route for a booking
     */
    public function getCancelRoute($booking): string
    {
        return route('bookings.cancel', $booking->booking_id);
    }
}