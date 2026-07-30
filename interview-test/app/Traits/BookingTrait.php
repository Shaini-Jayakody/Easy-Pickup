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
use Illuminate\Http\Request;

trait BookingTrait
{
   
    // QUERY METHODS

    /**
     * Get all bookings with relationships
     */
    public function getAllBookings()
    {
        return Booking::with(['user', 'car'])->orderBy('created_at', 'desc');
    }

    /**
     * Apply filters to bookings
     */
    public function applyFilters($query, $request)
    {
        // Filter by Car - ALL USERS
        if ($request->filled('car_id')) {
            $query->where('car_id', $request->car_id);
        }

        // Filter by Date Range (Start Date) - ALL USERS
        if ($request->filled('date_from')) {
            $query->whereDate('rental_start_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('rental_start_date', '<=', $request->date_to);
        }

        // Filter by NIC - ADMIN/MANAGER ONLY
        if ($request->filled('nic') && $this->hasBookingPermission()) {
            $query->whereHas('user', function($q) use ($request) {
                $q->where('id_num', 'LIKE', '%' . $request->nic . '%');
            });
        }

        // Status is handled by DataTables via 'status' parameter
        return $query;
    }

    /**
     * Get filtered bookings based on user role
     */
    public function getFilteredBookings(Request $request)
    {
        if ($this->hasBookingPermission()) {
            $bookings = $this->getAllBookings();
            return $this->applyFilters($bookings, $request);
        }
        
        $bookings = $this->getBookingsByUser(Auth::id());
        return $this->applyFilters($bookings, $request);
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
     * Get car bookings for calendar
     */
    public function getCarBookingsForCalendar($carId, $bookingId = null)
    {
        $query = Booking::where('car_id', $carId)
            ->whereNotIn('status', ['cancelled']);
        
        if ($bookingId) {
            $query->where('booking_id', '!=', $bookingId);
        }
        
        return $query->select('booking_id', 'rental_start_date', 'rental_end_date', 'status')
            ->get()
            ->map(function($booking) {
                return [
                    'id' => $booking->booking_id,
                    'rental_start_date' => $booking->rental_start_date,
                    'rental_end_date' => $booking->rental_end_date,
                    'status' => $booking->status,
                    'status_label' => $this->getStatusText($booking->status)
                ];
            });
    }


    // CRUD OPERATIONS

    /**
     * Create a new booking with estimated cost
     */
public function createBooking(array $data): array
{
    $this->validateRentalDuration($data['rental_start_date'], $data['rental_end_date']);
    $this->validateCarAvailability($data['car_id'], $data['rental_start_date'], $data['rental_end_date']);

    // Get car and calculate estimated cost
    $car = Car::find($data['car_id']);
    $pricePerHour = $car->rent_price_per_hour ?? 0;
    $start = Carbon::parse($data['rental_start_date']);
    $end = Carbon::parse($data['rental_end_date']);
    $duration = ceil($start->diffInHours($end));
    $estimatedCost = $duration * $pricePerHour;

    $booking = Booking::create([
        'booking_ref_no' => $this->generateBookingReference(),
        'user_id' => Auth::id(),
        'car_id' => $data['car_id'],
        'rental_start_date' => $data['rental_start_date'],
        'rental_end_date' => $data['rental_end_date'],
        'estimated_cost' => $estimatedCost,
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
 * Update booking with estimated cost recalculation
 */
public function updateBooking($id, array $data): array
{
    $booking = $this->getBookingById($id);
    
    if (!$booking->isPending()) {
        throw new \Exception('Only pending bookings can be updated.');
    }

    // Calculate new estimated cost
    $car = Car::find($data['car_id']);
    $pricePerHour = $car->rent_price_per_hour ?? 0;
    $start = Carbon::parse($data['rental_start_date']);
    $end = Carbon::parse($data['rental_end_date']);
    $duration = ceil($start->diffInHours($end));
    $estimatedCost = $duration * $pricePerHour;

    $booking->update([
        'car_id' => $data['car_id'],
        'rental_start_date' => $data['rental_start_date'],
        'rental_end_date' => $data['rental_end_date'],
        'estimated_cost' => $estimatedCost,
        'notes' => $data['notes'] ?? null,
    ]);

    return [
        'success' => true,
        'message' => 'Booking updated successfully!',
        'booking' => $booking
    ];
}

    /**
     * Update booking status (legacy button method)
     */
    public function updateBookingStatus($id, string $action): array
    {
        $booking = $this->getBookingById($id);
        
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
     * Update booking status with validation
     */
    public function updateBookingStatusWithValidation($id, string $newStatus): array
    {
        $booking = $this->getBookingById($id);
        
        $allowedTransitions = [
            'pending' => ['confirmed', 'cancelled'],
            'confirmed' => ['active', 'cancelled'],
            'active' => ['returned'],
            'returned' => ['completed'],
            'completed' => [],
            'cancelled' => []
        ];
        
        $allowedNext = $allowedTransitions[$booking->status] ?? [];
        
        if (!in_array($newStatus, $allowedNext)) {
            throw new \Exception('Invalid status transition. You cannot change from ' . $booking->status . ' to ' . $newStatus . '.');
        }
        
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
        
        if (!$booking->isPending()) {
            throw new \Exception('Only pending bookings can be cancelled.');
        }

        $now = Carbon::now();
        $startDate = Carbon::parse($booking->rental_start_date);
        
        if ($startDate->lessThanOrEqualTo($now)) {
            throw new \Exception('Cannot cancel a booking that has already started.');
        }

        $booking->status = 'cancelled';
        $booking->save();

        return [
            'success' => true,
            'message' => 'Booking cancelled successfully!',
            'booking' => $booking
        ];
    }


    // VALIDATION METHODS

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
     * Validate booking update 
     */
    public function validateBookingUpdate(array $data, $bookingId)
    {
        $booking = $this->getBookingById($bookingId);
        
        if (!$booking->isPending()) {
            throw new \Exception('Only pending bookings can be updated.');
        }

        $currentStartDate = Carbon::parse($booking->rental_start_date);
        $now = Carbon::now();
        
        if ($currentStartDate->lessThanOrEqualTo($now)) {
            throw new \Exception('Cannot update a booking that has already started or is in the past.');
        }

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

        $this->validateRentalDuration($data['rental_start_date'], $data['rental_end_date']);

        return $validator->validated();
    }

    /**
     * Validate the requested rental window
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

        if ($excludeBookingId) {
            $query->where('booking_id', '!=', $excludeBookingId);
        }

        return !$query->exists();
    }

    
    // UI HELPER METHODS

    /**
     * Generate status badge HTML
     */
    public function getStatusBadgeHtml(string $status): string
    {
        $statusText = $this->getStatusText($status);
        $statusClass = 'label-status label-' . $status;
        return '<span class="' . $statusClass . '">' . $statusText . '</span>';
    }

    /**
     * Get action buttons for booking (called by DataTables)
     */
    public function getActionButtons($booking): string
    {
        $isAdmin = $this->hasBookingPermission();
        
        $leftActions = '';
        
        // View button - everyone
        $leftActions .= '<button type="button" class="action-btn edit-btn view-booking" 
                            data-id="' . $booking->booking_id . '" 
                            title="View Details">
                            <img src="' . asset('images/eye-open.svg') . '" alt="View" width="14" height="14" style="filter: brightness(0) invert(1);">
                        </button>';
        
        // Invoice button 
        if ($isAdmin && $booking->status === 'completed') {
            // Check if invoice already exists
            $hasInvoice = $booking->invoice()->exists();
            
            if (!$hasInvoice) {
                $leftActions .= '<a href="' . route('invoices.create') . '?booking_id=' . $booking->booking_id . '" 
                                    class="action-btn btn-success invoice-booking" 
                                    data-id="' . $booking->booking_id . '" 
                                    data-ref="' . $booking->booking_ref_no . '"
                                    title="Generate Invoice">
                                    <img src="' . asset('images/invoice.svg') . '" alt="Invoice" width="14" height="14" style="filter: brightness(0) invert(1);">
                                </a>';
            } else {
                $leftActions .= '<span class="action-btn" 
                                    title="Invoice already generated"
                                    style="background-color:#6c757d; color:white; opacity:0.6; cursor:default;">
                                    <img src="' . asset('images/invoice-check.svg') . '" alt="Invoice Generated" width="14" height="14" style="filter: brightness(0) invert(1);">
                                </span>';
            }
        }
        
        //Status Dropdown (Admin/Manager Only)
        $rightActions = '';
        
        if ($isAdmin) {
            $statusOptions = $this->getStatusOptionsForDropdown($booking->status);
            $rightActions = '<select class="form-control input-sm status-dropdown" 
                                    data-booking-id="' . $booking->booking_id . '" 
                                    style="width: auto; display: inline-block; padding: 2px 20px 2px 8px; height: 28px; font-size: 11px; border-radius: 4px; min-width: 80px; border: 1px solid #ccc; background-color: #fff; cursor: pointer;">
                                ' . $statusOptions . '
                            </select>';
        } else {
            $rightActions = '';
        }
        

        return '<div class="action-column-wrapper" style="display: flex; align-items: center; justify-content: space-between; gap: 8px; min-width: 140px;">
                    <div class="action-left" style="display: flex; gap: 4px; align-items: center;">
                        ' . $leftActions . '
                    </div>
                    <div class="action-right" style="display: flex; gap: 4px; align-items: center;">
                        ' . $rightActions . '
                    </div>
                </div>';
    }

    /**
     * Get status options for dropdown
     */
    public function getStatusOptionsForDropdown($currentStatus)
    {
        // Define allowed status transitions
        $allowedTransitions = [
            'pending' => ['pending', 'confirmed', 'cancelled'],
            'confirmed' => ['confirmed', 'active', 'cancelled'],
            'active' => ['active', 'returned'],
            'returned' => ['returned', 'completed'],
            'completed' => ['completed'],
            'cancelled' => ['cancelled']
        ];
        
        $allowedStatuses = $allowedTransitions[$currentStatus] ?? [$currentStatus];
        
        $statusLabels = [
            'pending' => 'Pending',
            'confirmed' => 'Confirmed',
            'active' => 'Active',
            'returned' => 'Returned',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled'
        ];
        
        $options = '';
        foreach ($allowedStatuses as $status) {
            $selected = $status === $currentStatus ? 'selected' : '';
            $options .= '<option value="' . $status . '" ' . $selected . '>' . $statusLabels[$status] . '</option>';
        }
        
        return $options;
    }

    /**
     * Get status dropdown (legacy - kept for compatibility)
     */
    public function getStatusDropdown($booking): string
    {
        $currentStatus = $booking->status;
        
        $allowedTransitions = [
            'pending' => ['confirmed', 'cancelled'],
            'confirmed' => ['active', 'cancelled'],
            'active' => ['returned'],
            'returned' => ['completed'],
            'completed' => [],
            'cancelled' => []
        ];
        
        $allowedNext = $allowedTransitions[$currentStatus] ?? [];
        
        if (empty($allowedNext)) {
            return '<span class="label-status label-' . $currentStatus . '" style="font-size:11px; padding:4px 8px;">' 
                    . $this->getStatusText($currentStatus) . ' (Final)</span> ';
        }
        
        $html = '<div class="status-dropdown-wrapper" style="display:inline-block; position:relative;">';
        $html .= '<select class="form-control status-dropdown" data-booking-id="' . $booking->booking_id . '" 
                    style="height:32px; padding:2px 20px 2px 8px; font-size:12px; border-radius:4px; min-width:110px; appearance:auto; border:1px solid #ccc; background-color:#fff; cursor:pointer;">';
        
        $html .= '<option value="' . $currentStatus . '" selected disabled style="font-weight:bold; color:#555;">▼ ' 
                . $this->getStatusText($currentStatus) . '</option>';
        
        foreach ($allowedNext as $status) {
            $html .= '<option value="' . $status . '" style="color:' . $this->getStatusColor($status) . ';">→ ' 
                    . $this->getStatusText($status) . '</option>';
        }
        
        $html .= '</select>';
        $html .= '</div> ';
        
        return $html;
    }

    /**
     * Get color for status
     */
    public function getStatusColor($status): string
    {
        return match($status) {
            'pending' => '#f0ad4e',
            'confirmed' => '#5bc0de',
            'active' => '#5cb85c',
            'returned' => '#337ab7',
            'completed' => '#2e6da4',
            'cancelled' => '#d9534f',
            default => '#666'
        };
    }

    /**
     * Get reason why edit is disabled
     */
    public function getEditDisabledReason($booking): string
    {
        if (!$booking->isPending()) {
            return 'Only pending bookings can be edited';
        }
        if (Carbon::parse($booking->rental_start_date)->lessThanOrEqualTo(Carbon::now())) {
            return 'Booking has already started';
        }
        return 'Cannot edit';
    }

    /**
     * Get reason why cancel is disabled
     */
    public function getCancelDisabledReason($booking): string
    {
        if (!$booking->isPending()) {
            return 'Only pending bookings can be cancelled';
        }
        if (Carbon::parse($booking->rental_start_date)->lessThanOrEqualTo(Carbon::now())) {
            return 'Booking has already started';
        }
        return 'Cannot cancel';
    }

    
    // PERMISSION METHODS

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
     * Check if user can edit the booking
     */
    public function canEditBooking($booking): bool
    {
        if (!$booking) return false;
        if (!$booking->isPending()) return false;
        if (!Auth::check()) return false;
        if ((int) Auth::id() !== (int) $booking->user_id) return false;
        
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
        if (!$booking->isPending()) return false;
        if (!Auth::check()) return false;
        if ((int) Auth::id() !== (int) $booking->user_id) return false;
        
        $now = Carbon::now();
        $startDate = Carbon::parse($booking->rental_start_date);
        if ($startDate->lessThanOrEqualTo($now)) {
            return false;
        }
        
        return true;
    }

  
    // HELPER METHODS

    /**
     * Calculate the rental duration in hours.
     */
    public function getRentalDurationInHours($startDate, $endDate): int
    {
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);
        return (int) ceil($start->diffInHours($end));
    }

    /**
     * Calculate estimated cost for booking
     */
    public function calculateEstimatedCost($carId, $startDate, $endDate): float
    {
        $car = Car::find($carId);
        $pricePerHour = $car->rent_price_per_hour ?? 0;
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);
        $duration = ceil($start->diffInHours($end));
        return $duration * $pricePerHour;
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
     * Get booking status badge class (legacy - kept for compatibility)
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
     * Get allowed status transitions (legacy - kept for compatibility)
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
     * Get allowed actions for a booking status (legacy - kept for compatibility)
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
            'estimated_cost' => $booking->estimated_cost ?? 0,
            'status' => $booking->status,
            'status_text' => $this->getStatusText($booking->status),
            'status_badge' => $this->getStatusBadge($booking->status),
            'notes' => $booking->notes,
            'created_at' => $booking->created_at,
        ];
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

   
    // EXCEPTION HELPERS

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
}