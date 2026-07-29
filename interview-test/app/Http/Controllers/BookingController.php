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
            // Admin/Manager sees ALL bookings, regular users see only their own
            if ($this->hasBookingPermission()) {
                $bookings = $this->getAllBookings();
            } else {
                $bookings = $this->getBookingsByUser(Auth::id());
            }
            
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
                    $statusText = $this->getStatusText($booking->status);
                    $statusClass = 'label-status label-' . $booking->status;
                    return '<span class="' . $statusClass . '">' . $statusText . '</span>';
                })
                ->addColumn('action', function($booking) {
                    $actions = '<div class="action-buttons">';
                    // ============================================
                    // VIEW BUTTON - For Everyone
                    // ============================================
                    $actions .= '<a href="#" class="action-btn btn-info view-booking" 
                                data-id="' . $booking->booking_id . '" 
                                title="View Details">
                                <span class="glyphicon glyphicon-eye-open"></span>
                            </a>';
                    
                    // ============================================
                    // FOR ADMIN/MANAGER - Status Dropdown
                    // ============================================
                    if ($this->hasBookingPermission()) {
                        $actions .= $this->getStatusDropdown($booking);
                    } 
                    // ============================================
                    // FOR REGULAR USERS - Edit/Cancel Buttons
                    // ============================================
                    else {
                        $canEdit = $this->canEditBooking($booking);
                        $canCancel = $this->canCancelBooking($booking);
                        
                        // Edit button
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
                        
                        // Cancel button
                        if ($canCancel) {
                            $actions .= '<button class="action-btn btn-cancel cancel-booking" 
                                        data-id="' . $booking->booking_id . '" 
                                        data-ref="' . $booking->booking_ref_no . '"
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
                    }
                    
                    $actions .= '</div>';
                    return $actions;
                })
                ->rawColumns(['status', 'action'])
                ->make(true);
        }

        return view('bookings.index');
    }

    /**
     * Generate status dropdown for admin/manager
     */
    private function getStatusDropdown($booking)
    {
        $currentStatus = $booking->status;
        
        // Define allowed transitions (current -> next)
        $allowedTransitions = [
            'pending' => ['confirmed', 'cancelled'],
            'confirmed' => ['active', 'cancelled'],
            'active' => ['returned'],
            'returned' => ['completed'],
            'completed' => [], // No further transitions
            'cancelled' => [] // No further transitions
        ];
        
        // Get allowed next statuses
        $allowedNext = $allowedTransitions[$currentStatus] ?? [];
        
        // If no allowed transitions, show the current status as disabled
        if (empty($allowedNext)) {
            return '<span class="label-status label-' . $currentStatus . '" style="font-size:11px; padding:4px 8px;">' 
                    . $this->getStatusText($currentStatus) . ' (Final)</span> ';
        }
        
        // Build the dropdown
        $html = '<div class="status-dropdown-wrapper" style="display:inline-block; position:relative;">';
        $html .= '<select class="form-control status-dropdown" data-booking-id="' . $booking->booking_id . '" 
                    style="height:32px; padding:2px 20px 2px 8px; font-size:12px; border-radius:4px; min-width:110px; appearance:auto; border:1px solid #ccc; background-color:#fff; cursor:pointer;">';
        
        // Always show current status as selected (disabled)
        $html .= '<option value="' . $currentStatus . '" selected disabled style="font-weight:bold; color:#555;">▼ ' 
                . $this->getStatusText($currentStatus) . '</option>';
        
        // Add allowed next statuses
        foreach ($allowedNext as $status) {
            $html .= '<option value="' . $status . '" style="color:' . $this->getStatusColor($status) . ';">→ ' 
                    . $this->getStatusText($status) . '</option>';
        }
        
        $html .= '</select>';
        $html .= '</div> ';
        
        return $html;
    }

    /**
     * Get color for status (for dropdown options)
     */
    private function getStatusColor($status)
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
     * Update booking status from dropdown (Admin/Manager only)
     */
    public function updateStatusFromDropdown(Request $request, $id)
    {
        try {
            $booking = $this->getBookingById($id);
            $newStatus = $request->status;
            
            // Check if this transition is allowed
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
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid status transition. You cannot change from ' . $booking->status . ' to ' . $newStatus . '.'
                ], 422);
            }
            
            // Update the status
            $booking->status = $newStatus;
            $booking->save();
            
            $statusMessages = [
                'confirmed' => 'Booking confirmed successfully!',
                'active' => 'Car issued successfully!',
                'returned' => 'Car returned successfully!',
                'completed' => 'Booking completed successfully!',
                'cancelled' => 'Booking cancelled successfully!',
            ];
            
            return response()->json([
                'success' => true,
                'message' => $statusMessages[$newStatus] ?? 'Status updated successfully!',
                'booking' => $booking
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show the form for creating a new booking
     */
    public function create()
    {
        $cars = Car::with('model.brand')->where('id', '>', 0)->get();
        return view('bookings.form', compact('cars'));
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
        return view('bookings.form', compact('booking', 'cars'));
    }

    /**
     * Update an existing booking
     */
    public function update(Request $request, $id)
    {
        try {
            $booking = $this->getBookingById($id);
            
            if (!$this->canEditBooking($booking)) {
                return response()->json([
                    'success' => false,
                    'message' => 'You cannot edit this booking. It may have already started or is not pending.'
                ], 403);
            }
            
            $validated = $this->validateBookingUpdate($request->all(), $id);
            $this->validateCarAvailabilityForUpdate($booking->booking_id, $validated['car_id'], $validated['rental_start_date'], $validated['rental_end_date']);
            
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
            
            if (!$this->canCancelBooking($booking)) {
                return response()->json([
                    'success' => false,
                    'message' => 'You cannot cancel this booking. It may have already started or is not pending.'
                ], 403);
            }
            
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
     * Update booking status (Admin/Manager only) - Legacy method
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