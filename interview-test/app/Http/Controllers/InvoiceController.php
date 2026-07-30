<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\CarDetail\Car;
use App\Traits\InvoiceTrait;
use App\Models\Invoice;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Yajra\DataTables\Facades\DataTables;

class InvoiceController extends Controller
{
    use InvoiceTrait;

    /**
     * Display a listing of invoices
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            // Get invoices based on user role
            if ($this->hasInvoicePermission()) {
                // Admin/Manager sees ALL invoices
                $invoices = Invoice::with(['booking', 'user', 'car'])
                    ->orderBy('created_at', 'desc');
                
                // Apply admin filters
                $invoices = $this->applyInvoiceFilters($invoices, $request);
            } else {
                // Regular users see only their own invoices
                $invoices = Invoice::with(['booking', 'user', 'car'])
                    ->where('user_id', Auth::id())
                    ->orderBy('created_at', 'desc');
                
                // Apply basic filters for users
                $invoices = $this->applyUserInvoiceFilters($invoices, $request);
            }
            
            return DataTables::of($invoices)
                ->addIndexColumn()
                ->addColumn('customer_name', function($invoice) {
                    return $invoice->user->name ?? 'N/A';
                })
                ->addColumn('customer_nic', function($invoice) {
                    return $invoice->user->id_num ?? 'N/A';
                })
                ->addColumn('car_info', function($invoice) {
                    return $invoice->car->name . ' (' . $invoice->car->number_plate . ')';
                })
                ->addColumn('booking_ref', function($invoice) {
                    return $invoice->booking->booking_ref_no ?? 'N/A';
                })
                ->addColumn('total_cost', function($invoice) {
                    return 'Rs. ' . number_format($invoice->total_cost, 2);
                })
                ->addColumn('status', function($invoice) {
                    $badgeClass = $invoice->getStatusBadgeClass();
                    $statusText = ucfirst($invoice->payment_status);
                    return '<span class="label ' . $badgeClass . '">' . $statusText . '</span>';
                })
                ->addColumn('payment_method', function($invoice) {
                    return ucfirst($invoice->payment_method ?? 'N/A');
                })
                ->addColumn('action', function($invoice) {
                    $actions = '<div class="action-buttons">';
                    
                    // View button
                    $actions .= '<a href="#" class="action-btn btn-info view-invoice" data-id="' . $invoice->invoice_id . '" title="View Invoice">
                                <span class="glyphicon glyphicon-eye-open"></span>
                            </a> ';
                    
                    // Print button
                    $actions .= '<a href="#" class="action-btn btn-success print-invoice" data-id="' . $invoice->invoice_id . '" title="Print Invoice">
                                <span class="glyphicon glyphicon-print"></span>
                            </a> ';
                    
                    // Admin/Manager: Status change dropdown
                    if ($this->hasInvoicePermission()) {
                        $actions .= $this->getInvoiceStatusDropdown($invoice);
                    }
                    
                    $actions .= '</div>';
                    return $actions;
                })
                ->rawColumns(['status', 'action'])
                ->make(true);
        }

        // Get cars for filter dropdown
        $cars = Car::with('model.brand')->where('id', '>', 0)->get();
        
        return view('invoices.index', compact('cars'));
    }

    /**
     * Check if user has permission to manage invoices
     */
    public function hasInvoicePermission(): bool
    {
        if (!Auth::check()) {
            return false;
        }
        return in_array(Auth::user()->role, ['admin', 'manager']);
    }

    /**
     * Apply admin filters to invoice query
     */
    private function applyInvoiceFilters($query, $request)
    {
        // Filter by NIC
        if ($request->filled('nic')) {
            $query->whereHas('user', function($q) use ($request) {
                $q->where('id_num', 'LIKE', '%' . $request->nic . '%');
            });
        }

        // Filter by Customer Name
        if ($request->filled('customer_name')) {
            $query->whereHas('user', function($q) use ($request) {
                $q->where('name', 'LIKE', '%' . $request->customer_name . '%');
            });
        }

        // Filter by Car
        if ($request->filled('car_id')) {
            $query->where('car_id', $request->car_id);
        }

        // Filter by Status
        if ($request->filled('status')) {
            $query->where('payment_status', $request->status);
        }

        // Filter by Payment Method
        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        // Filter by Date Range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        return $query;
    }

    /**
     * Apply user filters to invoice query
     */
    private function applyUserInvoiceFilters($query, $request)
    {
        // Filter by Status
        if ($request->filled('status')) {
            $query->where('payment_status', $request->status);
        }

        // Filter by Payment Method
        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        // Filter by Date Range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        return $query;
    }

    /**
     * Generate invoice status dropdown for admin/manager
     */
    private function getInvoiceStatusDropdown($invoice)
    {
        $currentStatus = $invoice->payment_status;
        
        $statusColors = [
            'pending' => '#f59e0b',
            'paid' => '#10b981',
            'failed' => '#ef4444'
        ];
        
        $statusLabels = [
            'pending' => 'Pending',
            'paid' => 'Paid',
            'failed' => 'Failed'
        ];
        
        $allowedTransitions = [
            'pending' => ['paid', 'failed'],
            'paid' => [],
            'failed' => ['pending']
        ];
        
        $allowedNext = $allowedTransitions[$currentStatus] ?? [];
        $currentColor = $statusColors[$currentStatus] ?? '#6c757d';
        
        if (empty($allowedNext)) {
            return '<span class="label-status label-' . $currentStatus . '" style="font-size:11px; padding:4px 8px;">' 
                    . $statusLabels[$currentStatus] . ' (Final)</span> ';
        }
        
        // Build dropdown
        $html = '<div class="invoice-status-dropdown-wrapper" style="display:inline-block; position:relative;">';
        $html .= '<select class="form-control invoice-status-dropdown" data-invoice-id="' . $invoice->invoice_id . '" 
                    style="height:32px; padding:2px 20px 2px 8px; font-size:12px; border-radius:4px; min-width:100px; appearance:auto; border:1px solid #ccc; background-color:#fff; cursor:pointer;">';
        
        $html .= '<option value="' . $currentStatus . '" selected disabled style="font-weight:bold; color:#555;">▼ ' 
                . $statusLabels[$currentStatus] . '</option>';
        
        foreach ($allowedNext as $status) {
            $color = $statusColors[$status] ?? '#6c757d';
            $html .= '<option value="' . $status . '" style="color:' . $color . ';">→ ' 
                    . $statusLabels[$status] . '</option>';
        }
        
        $html .= '</select>';
        $html .= '</div> ';
        
        return $html;
    }

    /**
     * Update invoice status (Admin/Manager only)
     */
    public function updateStatus(Request $request, $id)
    {
        try {
            if (!$this->hasInvoicePermission()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to update invoice status.'
                ], 403);
            }

            $invoice = Invoice::findOrFail($id);
            $newStatus = $request->status;
            
            $allowedTransitions = [
                'pending' => ['paid', 'failed'],
                'paid' => [],
                'failed' => ['pending']
            ];
            
            $allowedNext = $allowedTransitions[$invoice->payment_status] ?? [];
            
            if (!in_array($newStatus, $allowedNext)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid status transition. Cannot change from ' . $invoice->payment_status . ' to ' . $newStatus . '.'
                ], 422);
            }
            
            // Update status
            $invoice->payment_status = $newStatus;
            
            // If status is paid, set paid_at timestamp
            if ($newStatus === 'paid') {
                $invoice->paid_at = now();
            }
            
            $invoice->save();
            
            return response()->json([
                'success' => true,
                'message' => 'Invoice status updated successfully!',
                'invoice' => $invoice
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating invoice status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show the form for creating a new invoice (Admin/Manager only)
     */
public function create(Request $request)
    {
        // Get completed bookings without invoices, with valid relationships
        $bookings = Booking::where('status', 'completed')
            ->whereHas('car')      // Ensure booking has a car
            ->whereHas('user')     // Ensure booking has a user
            ->whereDoesntHave('invoice')
            ->with(['user', 'car'])
            ->orderBy('created_at', 'desc')
            ->get();

        // If booking_id is passed in the URL, pre-select it
        $selectedBookingId = $request->query('booking_id');

        return view('invoices.form', compact('bookings', 'selectedBookingId'));
    }
    /**
     * Get booking details for invoice calculation (AJAX)
     */
public function getBookingDetails(Request $request)
{
    // Restrict to admin/manager only
    if (!$this->hasInvoicePermission()) {
        return response()->json([
            'success' => false,
            'message' => 'You do not have permission to access this resource.'
        ], 403);
    }

    try {
        $bookingId = $request->booking_id;
        $booking = Booking::with(['user', 'car'])->findOrFail($bookingId);
        
        $startDate = Carbon::parse($booking->rental_start_date);
        $endDate = Carbon::parse($booking->rental_end_date);
        $expectedHours = $startDate->diffInHours($endDate);
        
        $discount = Invoice::calculateDiscount($booking->user_id);
        
        return response()->json([
            'success' => true,
            'booking' => [
                'id' => $booking->booking_id,
                'ref_no' => $booking->booking_ref_no,
                'user_name' => $booking->user->name,
                'user_nic' => $booking->user->id_num,
                'user_email' => $booking->user->email,
                'car_name' => $booking->car->name,
                'car_ref_no' => $booking->car->ref_no,
                'car_number_plate' => $booking->car->number_plate,
                'price_per_hour' => $booking->car->rent_price_per_hour,
                'rental_start_date' => $booking->rental_start_date,
                'rental_end_date' => $booking->rental_end_date,
                'expected_hours' => $expectedHours,
                'discount_percentage' => $discount['percentage'],
                'discount_label' => $discount['label'],
            ]
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => $e->getMessage()
        ], 500);
    }
}

    /**
     * Preview invoice calculation (AJAX)
     */
    public function previewInvoice(Request $request)
    {
        // Restrict to admin/manager only
        if (!$this->hasInvoicePermission()) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to access this resource.'
            ], 403);
        }

        try {
            $bookingId = $request->booking_id;
            $returnedDate = $request->returned_date;
            
            $details = $this->calculateInvoiceDetails($bookingId, $returnedDate);
            
            return response()->json([
                'success' => true,
                'details' => $details
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created invoice (Admin/Manager only)
     */
    public function store(Request $request)
    {
        // Restrict to admin/manager only
        if (!$this->hasInvoicePermission()) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to create invoices.'
            ], 403);
        }

        try {
            $validated = $this->validateInvoiceData($request->all());
            $result = $this->createInvoice($validated);
            return response()->json($result);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->validator->errors()->all()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating invoice: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display invoice details
     */
    public function show($id)
    {
        $invoice = Invoice::with(['booking', 'user', 'car'])->findOrFail($id);
        
        // Check if user has access to this invoice
        if (!$this->hasInvoicePermission() && $invoice->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have access to this invoice.'
            ], 403);
        }
        
        return response()->json([
            'invoice' => $this->formatInvoiceForResponse($invoice)
        ]);
    }

    /**
     * Print invoice
     */
    public function print($id)
    {
        $invoice = Invoice::with(['booking', 'user', 'car'])->findOrFail($id);
        
        // Check if user has access to this invoice
        if (!$this->hasInvoicePermission() && $invoice->user_id !== Auth::id()) {
            abort(403, 'You do not have access to this invoice.');
        }
        
        return view('invoices.print', compact('invoice'));
    }
}