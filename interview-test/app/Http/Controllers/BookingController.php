<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\CarDetail\Car;
use App\Traits\BookingTrait;
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
            $bookings = $this->getAllBookings();
            
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
                ->addColumn('duration', function($booking) {
                    return $booking->getDurationInHours() . ' hrs';
                })
                ->addColumn('status', function($booking) {
                    return '<span class="' . $this->getStatusBadge($booking->status) . '">' . $this->getStatusText($booking->status) . '</span>';
                })
                ->addColumn('action', function($booking) {
                    $actions = '';
                    
                    // Only show actions for admin/manager
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
                            
                            $actions .= '<button class="btn btn-' . $btnClass . ' btn-xs ' . $action . '-booking" 
                                        data-id="' . $booking->booking_id . '" 
                                        title="' . $label . '">
                                        <span class="glyphicon ' . $icon . '"></span>
                                    </button> ';
                        }
                    }
                    
                    // View button for everyone
                    $actions .= '<a href="#" class="btn btn-info btn-xs view-booking" data-id="' . $booking->booking_id . '" title="View Details">
                                    <span class="glyphicon glyphicon-eye-open"></span>
                                </a>';
                    
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
        return view('bookings.form', compact('cars'));
    }

    /**
     * Store a newly created booking
     */
    public function store(Request $request)
    {
        try {
            // Validate using trait
            $validated = $this->validateBookingData($request->all());
            
            // Create booking using trait
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
     * Update booking status
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
 * Check car availability
 */
public function checkAvailability(Request $request)
{
    try {
        $carId = $request->car_id;
        $startDate = $request->start_date;
        $endDate = $request->end_date;
        
        // Validate input
        if (!$carId || !$startDate || !$endDate) {
            return response()->json([
                'available' => false,
                'message' => 'Missing required parameters'
            ], 400);
        }
        
        $available = $this->isCarAvailable($carId, $startDate, $endDate);
        
        return response()->json(['available' => $available]);
        
    } catch (\Exception $e) {
        return response()->json([
            'available' => false,
            'message' => $e->getMessage()
        ], 500);
    }
}

    /**
     * Get car bookings for calendar
     */
    public function getCarBookings(Request $request)
    {
        $carId = $request->car_id;
        
        if (!$carId) {
            return response()->json(['bookings' => []]);
        }
        
        $bookings = Booking::where('car_id', $carId)
            ->whereIn('status', ['pending', 'confirmed', 'active'])
            ->select('booking_id', 'rental_start_date', 'rental_end_date', 'status')
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
                    return '<span class="' . $this->getStatusBadge($booking->status) . '">' . $this->getStatusText($booking->status) . '</span>';
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