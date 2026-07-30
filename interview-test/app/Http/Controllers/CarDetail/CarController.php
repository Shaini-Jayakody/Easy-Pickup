<?php

namespace App\Http\Controllers\CarDetail;

use App\Http\Controllers\Controller;
use App\Traits\Car;
use Illuminate\Http\Request;
use App\Models\CarDetail\CarBrand as Brand;
use App\Models\CarDetail\Car as CarDetail;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Auth;
use App\Helpers\IconHelper;
use Illuminate\Validation\ValidationException;

class CarController extends Controller
{
    use Car;

    /**
     * Display a listing of cars with DataTables
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $cars = $this->getAllCars();

            if ($request->filled('brand_id')) {
                $cars = $this->getCarsByBrand($request->brand_id);
            }
            
            $isAdminOrManager = Auth::check() && in_array(Auth::user()->role, ['admin', 'manager']);
            $isAuthenticated = Auth::check();
            
            $dataTable = DataTables::of($cars)
                ->addIndexColumn()
                ->addColumn('brand', function($car) {
                    return $car->model->brand->name ?? 'N/A';
                })
                ->addColumn('model_name', function($car) {
                    return $car->model->name ?? 'N/A';
                })
                ->addColumn('ref_no', function($car) {
                    return $car->ref_no ?? 'N/A';
                })
                ->addColumn('name', function($car) {
                    return $car->name ?? 'N/A';
                })
                ->addColumn('color', function($car) {
                    $color = $car->color ?? 'N/A';
                    if ($color !== 'N/A') {
                        return '<span class="label" style="background-color: ' . strtolower($color) . '; color: #fff; padding: 4px 10px; border-radius: 4px;">' . $color . '</span>';
                    }
                    return $color;
                })
                ->addColumn('number_plate', function($car) {
                    return $car->number_plate ?? 'N/A';
                })
                ->addColumn('rent_price_per_hour', function($car) {
                    return $car->rent_price_per_hour ? 'Rs. ' . number_format($car->rent_price_per_hour, 2) : 'N/A';
                })
                ->addColumn('transmition', function($car) {
                    return $car->transmition ?? 'N/A';
                })
                ->addColumn('engine_number', function($car) {
                    return $car->engine_number ?? 'N/A';
                })
                ->addColumn('chassis_number', function($car) {
                    return $car->chassis_number ?? 'N/A';
                });
            
            if ($isAuthenticated) {
                $dataTable->addColumn('action', function($car) use ($isAdminOrManager) {
                    if ($isAdminOrManager) {
                        // Check if car has bookings
                        $hasBookings = $this->carHasBookings($car->id);
                        $bookingCount = $this->getActiveBookingsCount($car->id);
                        
                        // EDIT BUTTON - Disabled if has bookings
                        if ($hasBookings) {
                            $editBtn = '<button class="action-btn edit-btn disabled" 
                                        disabled 
                                        title="Cannot edit - Car has ' . $bookingCount . ' active booking(s)">
                                        ' . IconHelper::edit(14) . '
                                    </button>';
                        } else {
                            $editBtn = '<a href="' . route('car.edit', $car->id) . '" 
                                        class="action-btn edit-btn" 
                                        title="Edit Car">
                                        ' . IconHelper::edit(14) . '
                                    </a>';
                        }
                        
                        // DELETE BUTTON - Disabled if has bookings
                        if ($hasBookings) {
                            $deleteBtn = '<button class="action-btn delete-btn disabled" 
                                            disabled 
                                            title="Cannot delete - Car has ' . $bookingCount . ' active booking(s)">
                                            ' . IconHelper::delete(14) . '
                                        </button>';
                        } else {
                            $deleteBtn = '<button class="action-btn delete-car" 
                                            data-id="' . $car->id . '" 
                                            data-name="' . $car->name . '" 
                                            title="Delete Car">
                                            ' . IconHelper::delete(14) . '
                                        </button>';
                        }
                        
                        return '<div class="action-buttons">' . $editBtn . ' ' . $deleteBtn . '</div>';
                    }

                    $rentUrl = route('bookings.create', ['car_id' => $car->id, 'user_id' => Auth::id()]);
                    return '<a href="' . $rentUrl . '" 
                                class="btn btn-rent btn-xs rent-booking" 
                                data-id="' . $car->id . '" 
                                data-name="' . $car->name . '"
                                title="Rent this car">
                                ' . IconHelper::rent(14) . ' Rent
                            </a>';
                });
                $dataTable->rawColumns(['color', 'action']);
            } else {
                $dataTable->rawColumns(['color']);
            }
            
            return $dataTable->make(true);
        }

        $brands = $this->getAllBrands();
        return view('car-details.cars.index', compact('brands'));
    }

    /**
     * Show the form for creating a new car
     */
    public function form()
    {
        if (!$this->hasCarPermission()) {
            return redirect()->route('home')->with('error', 'You do not have permission to add cars.');
        }

        $brands = $this->getBrandsForSelect();
        $models = $this->getModelsForSelect();
        $car = null;
        
        return view('car-details.cars.components.form', compact('brands', 'models', 'car'));
    }

    /**
     * Show the form for editing an existing car
     */
    public function edit($id)
    {
        if (!$this->hasCarPermission()) {
            return redirect()->route('home')->with('error', 'You do not have permission to edit cars.');
        }

        $car = $this->getCarById($id);
        
        // Check if car has bookings before editing
        if ($this->carHasBookings($id)) {
            $bookingCount = $this->getActiveBookingsCount($id);
            return redirect()->route('car')->with('error', 'Cannot edit car because it has ' . $bookingCount . ' active booking(s). Please complete or cancel the bookings first.');
        }

        $brands = $this->getBrandsForSelect();
        $models = $this->getModelsForSelect();
        
        return view('car-details.cars.components.form', compact('car', 'brands', 'models'));
    }

    /**
     * Store a newly created car
     */
    public function save(Request $request)
    {
        if (!$this->hasCarPermission()) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to add cars.'
            ], 403);
        }

        try {
            $validated = $this->validateCarData($request->all());
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->validator->errors()->all()
            ], 422);
        }

        $carArray = $this->prepareCarData($validated);
        $result = $this->saveCar($carArray);
        
        return response()->json([
            'success' => true,
            'message' => $result['message'],
            'car' => $result['car']
        ]);
    }

    /**
     * Update an existing car
     */
    public function update(Request $request, $id)
    {
        if (!$this->hasCarPermission()) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to update cars.'
            ], 403);
        }

        try {
            // Check if car has bookings before updating
            if ($this->carHasBookings($id)) {
                $bookingCount = $this->getActiveBookingsCount($id);
                return response()->json([
                    'success' => false,
                    'message' => "Cannot update car because it has {$bookingCount} active booking(s). Please complete or cancel the bookings first."
                ], 422);
            }

            $validated = $this->validateCarData($request->all(), $id);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->validator->errors()->all()
            ], 422);
        }

        $carArray = $this->prepareCarData($validated);
        $result = $this->updateCar($id, $carArray);
        
        return response()->json([
            'success' => true,
            'message' => $result['message'],
            'car' => $result['car']
        ]);
    }

    

    /**
     * Delete a car
     */
    public function delete($id)
    {
        try {
            \Log::info('Delete car attempt', ['id' => $id, 'user' => Auth::id()]);
            
            if (!$this->hasCarPermission()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to delete cars.'
                ], 403);
            }

            // Check if car has bookings before deleting
            if ($this->carHasBookings($id)) {
                $bookingCount = $this->getActiveBookingsCount($id);
                return response()->json([
                    'success' => false,
                    'message' => "Cannot delete car because it has {$bookingCount} active booking(s). Please complete or cancel the bookings first."
                ], 422);
            }

            $result = $this->deleteCar($id);
            
            return response()->json([
                'success' => true,
                'message' => $result['message']
            ]);
        } catch (\Exception $e) {
            \Log::error('Delete car error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error deleting car: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Prepare car data array from validated data
     */
    private function prepareCarData(array $validated): array
    {
        return [
            'model_id' => $validated['car_model'],
            'name' => $validated['car_name'],
            'color' => $validated['car_color'],
            'engine_number' => $validated['eng_number'],
            'chassis_number' => $validated['chas_number'],
            'number_plate' => $validated['number_plate'],
            'rent_price_per_hour' => $validated['rent_price_per_hour'],
            'transmition' => $validated['car_trans']
        ];
    }


    // AJAX ENDPOINTS FOR UNIQUENESS CHECKS

    /**
     * Check if engine number exists
     */
    public function checkEngineNumber(Request $request)
    {
        $engineNumber = $request->get('value');
        $carId = $request->get('car_id');
        $exists = $this->engineNumberExists($engineNumber, $carId);
        
        return response()->json(['exists' => $exists]);
    }

    /**
     * Check if chassis number exists
     */
    public function checkChassis(Request $request)
    {
        $chassisNumber = $request->get('value');
        $carId = $request->get('car_id');
        $exists = $this->chassisExists($chassisNumber, $carId);
        
        return response()->json(['exists' => $exists]);
    }

    /**
     * Check if number plate exists
     */
    public function checkNumberPlate(Request $request)
    {
        $numberPlate = $request->get('value');
        $carId = $request->get('car_id');
        $exists = $this->numberPlateExists($numberPlate, $carId);
        
        return response()->json(['exists' => $exists]);
    }
}