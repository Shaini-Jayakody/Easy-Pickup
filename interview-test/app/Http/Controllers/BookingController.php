<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\CarDetail\Car;
use App\Traits\BookingTrait;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Yajra\DataTables\Facades\DataTables;

class BookingController extends Controller
{
    use BookingTrait;

    /**
     * Display a listing of bookings
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            // Get only the logged-in user's bookings
            $bookings = $this->getBookingsByUser(Auth::id());
            
            return DataTables::of($bookings)
                ->addIndexColumn()
                ->addColumn('user_name', function($booking) {
                    return $booking->user->name ?? 'N/A';
                })
                ->addColumn('user_nic', function($booking) {
                    return $booking->user->id_num ?? 'N/A';
                })
                ->addColumn('car_name', function($booking) {
                    return $booking->car->name ?? 'N/A';
                })
                ->addColumn('car_plate', function($booking) {
                    return $booking->car->number_plate ?? 'N/A';
                })
                ->addColumn('rental_start_date', function($booking) {
                    return $booking->rental_start_date;
                })
                ->addColumn('rental_end_date', function($booking) {
                    return $booking->rental_end_date;
                })
                ->addColumn('duration', function($booking) {
                    return $booking->getDurationInHours();
                })
                ->addColumn('status', function($booking) {
                    // FIXED: Use custom CSS classes
                    $statusText = $this->getStatusText($booking->status);
                    $statusClass = 'label-status label-' . $booking->status;
                    return '<span class="' . $statusClass . '">' . $statusText . '</span>';
                })
                ->addColumn('action', function($booking) {
                    $actions = '<div class="action-buttons">';
                    
                    // Check if user can edit
                    $canEdit = $this->canEditBooking($booking);
                    $canCancel = $this->canCancelBooking($booking);
                    
                    // Edit button - SQUARE
                    if ($canEdit) {
                        $actions .= '<a href="' . route('bookings.edit', $booking->booking_id) . '" 
                                    class="action-btn btn-edit" 
                                    title="Edit Booking">
                                    <span class="glyphicon glyphicon-edit"></span>
                                </a> ';
                    } else {
                        $editReason = 'Cannot edit';
                        if (!$booking->isPending()) {
                            $editReason = 'Only pending bookings can be edited';
                        } elseif (Carbon::parse($booking->rental_start_date)->lessThanOrEqualTo(Carbon::now())) {
                            $editReason = 'Booking has already started';
                        }
                        $actions .= '<button class="action-btn btn-edit" disabled title="' . $editReason . '">
                                    <span class="glyphicon glyphicon-edit"></span>
                                </button> ';
                    }
                    
                    // Cancel button - SQUARE
                    if ($canCancel) {
                        $actions .= '<button class="action-btn btn-cancel cancel-booking" 
                                    data-id="' . $booking->booking_id . '" 
                                    data-ref="' . $booking->booking_ref_no . '"
                                    data-can-cancel="true"
                                    title="Cancel Booking">
                                    <span class="glyphicon glyphicon-remove"></span>
                                </button> ';
                    } else {
                        $cancelReason = 'Cannot cancel';
                        if (!$booking->isPending()) {
                            $cancelReason = 'Only pending bookings can be cancelled';
                        } elseif (Carbon::parse($booking->rental_start_date)->lessThanOrEqualTo(Carbon::now())) {
                            $cancelReason = 'Booking has already started';
                        }
                        $actions .= '<button class="action-btn btn-cancel" disabled title="' . $cancelReason . '">
                                    <span class="glyphicon glyphicon-remove"></span>
                                </button> ';
                    }
                    
                    // Status management for admin/manager - SQUARE
                    if ($this->hasBookingPermission()) {
                        $allowedActions = $this->getAllowedActions($booking->status);
                        
                        foreach ($allowedActions as $action) {
                            $btnClass = $this->getAllowedTransitions()[$action]['btn'];
                            $label = $this->getAllowedTransitions()[$action]['label'];
                            $icon = match($action) {
                                'confirm' => 'glyphicon-check',
                                'issue' => 'glyphicon-road',
                                'return' => 'glyphicon-home',
                                'complete' => 'glyphicon-ok',
                                'cancel' => 'glyphicon-remove',
                                default => 'glyphicon-cog'
                            };
                            
                            $colorClass = match($btnClass) {
                                'success' => 'btn-success',
                                'primary' => 'btn-primary',
                                'warning' => 'btn-warning',
                                'danger' => 'btn-danger',
                                default => 'btn-default'
                            };
                            
                            $actions .= '<button class="action-btn ' . $colorClass . ' ' . $action . '-booking" 
                                        data-id="' . $booking->booking_id . '" 
                                        title="' . $label . '">
                                        <span class="glyphicon ' . $icon . '"></span>
                                    </button> ';
                        }
                    }
                    
                    // View button - SQUARE
                    $actions .= '<a href="#" class="action-btn btn-info view-booking" data-id="' . $booking->booking_id . '" title="View Details">
                                    <span class="glyphicon glyphicon-eye-open"></span>
                                </a>';
                    
                    $actions .= '</div>';
                    return $actions;
                })
                ->rawColumns(['status', 'action'])
                ->make(true);
        }

        return view('bookings.index');
    }

 /**
 * Show the form for creating a new booking
 */
public function create()
{
    $cars = Car::with('model.brand')->where('id', '>', 0)->get();
    return view('bookings.form', compact('cars')); // Uses the SAME form
}

/**
 * Show the form for editing a booking
 */
public function edit($id)
{
    $booking = $this->getBookingById($id);
    
    if (!$this->canEditBooking($booking)) {
        return redirect()->route('bookings.index')->with('error', 'You cannot edit this booking.');
    }
    
    $cars = Car::with('model.brand')->where('id', '>', 0)->get();
    return view('bookings.form', compact('booking', 'cars')); // Uses the SAME form!
}

    /**
     * Update an existing booking
     */
    public function update(Request $request, $id)
    {
        try {
            $booking = $this->getBookingById($id);
            
            // Check if user can edit
            if (!$this->canEditBooking($booking)) {
                return response()->json([
                    'success' => false,
                    'message' => 'You cannot edit this booking. It may have already started or is not pending.'
                ], 403);
            }
            
            // Validate with update-specific rules
            $validated = $this->validateBookingUpdate($request->all(), $id);
            
            // Check availability (excluding current booking)
            $this->validateCarAvailabilityForUpdate($booking->booking_id, $validated['car_id'], $validated['rental_start_date'], $validated['rental_end_date']);
            
            // Update the booking
            $booking->update([
                'car_id' => $validated['car_id'],
                'rental_start_date' => $validated['rental_start_date'],
                'rental_end_date' => $validated['rental_end_date'],
                'notes' => $validated['notes'] ?? null,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Booking updated successfully!',
                'booking' => $booking
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->validator->errors()->all()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating booking: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cancel a booking
     */
public function cancel(Request $request, $id)
{
    try {
        $booking = $this->getBookingById($id);
        
        // Check if user can cancel (uses the trait method)
        if (!$this->canCancelBooking($booking)) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot cancel this booking. It may have already started or is not pending.'
            ], 403);
        }
        
        // Cancel the booking
        $result = $this->cancelBooking($id);
        
        return response()->json($result);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Error cancelling booking: ' . $e->getMessage()
        ], 500);
    }
}

    /**
     * Store a newly created booking
     */
    public function store(Request $request)
    {
        try {
            $validated = $this->validateBookingData($request->all());
            $result = $this->createBooking($validated);
            return response()->json($result);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->validator->errors()->all()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating booking: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update booking status (Admin/Manager only)
     */
    public function updateStatus(Request $request, $id)
    {
        try {
            $result = $this->updateBookingStatus($id, $request->action);
            return response()->json($result);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get booking details for view
     */
    public function show($id)
    {
        $booking = $this->getBookingById($id);
        return response()->json([
            'booking' => $this->formatBookingForResponse($booking)
        ]);
    }

    /**
     * Check car availability (AJAX)
     */
public function checkAvailability(Request $request)
{
    try {
        $carId = $request->car_id;
        $startDate = $request->start_date;
        $endDate = $request->end_date;
        $bookingId = $request->booking_id;
        
        if (!$carId || !$startDate || !$endDate) {
            return response()->json([
                'available' => false,
                'message' => 'Missing required parameters'
            ], 400);
        }
        
        $available = $this->isCarAvailable($carId, $startDate, $endDate, $bookingId);
        
        return response()->json([
            'available' => $available,
            'message' => $available
                ? 'Car is available for the selected dates.'
                : 'This car is not available for the selected dates because it overlaps an existing booking.'
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'available' => false,
            'message' => $e->getMessage()
        ], 500);
    }
}
  /**
 * Get car bookings for calendar (AJAX)
 * This returns ALL bookings for a car, regardless of user
 */
public function getCarBookings(Request $request)
{
    $carId = $request->car_id;
    $bookingId = $request->booking_id;
    
    if (!$carId) {
        return response()->json(['bookings' => []]);
    }
    
    $query = Booking::where('car_id', $carId)
        ->whereNotIn('status', ['cancelled']);
    
    // Exclude current booking when editing (so user can see their own booking as available)
    if ($bookingId) {
        $query->where('booking_id', '!=', $bookingId);
    }
    
    $bookings = $query->select('booking_id', 'rental_start_date', 'rental_end_date', 'status')
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
    
    return response()->json([
        'bookings' => $bookings
    ]);
}
    /**
     * Get booking statistics (for dashboard)
     */
    public function getStats()
    {
        return response()->json($this->getBookingStats());
    }

    /**
     * Get user's booking history
     */
    public function getUserBookings($userId = null)
    {
        $userId = $userId ?? Auth::id();
        $bookings = $this->getBookingsByUser($userId);
        
        if (request()->ajax()) {
            return DataTables::of($bookings)
                ->addIndexColumn()
                ->addColumn('car_name', function($booking) {
                    return $booking->car->name ?? 'N/A';
                })
                ->addColumn('car_plate', function($booking) {
                    return $booking->car->number_plate ?? 'N/A';
                })
                ->addColumn('status', function($booking) {
                    $statusText = $this->getStatusText($booking->status);
                    $statusClass = 'label-status label-' . $booking->status;
                    return '<span class="' . $statusClass . '">' . $statusText . '</span>';
})
                ->addColumn('duration', function($booking) {
                    return $booking->getDurationInHours() . ' hrs';
                })
                ->rawColumns(['status'])
                ->make(true);
        }

        return view('bookings.user-history', compact('bookings'));
    }
}