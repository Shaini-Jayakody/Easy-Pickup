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

            // Apply brand filter if provided
            if ($request->filled('brand_id')) {
                $cars = $this->getCarsByBrand($request->brand_id);
            }
            
            return DataTables::of($cars)
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
                })
               ->addColumn('action', function($car) {
 $editBtn = '<a href="' . route('car.edit', $car->id) . '" 
                class="btn btn-primary btn-xs action-btn edit-btn" 
                title="Edit Car">
                ' . IconHelper::edit() . '
            </a>';
    $deleteBtn = '<button class="btn btn-danger btn-xs action-btn delete-btn" 
                    data-id="' . $car->id . '" 
                    data-name="' . $car->name . '" 
                    title="Delete Car">
                    ' . IconHelper::delete() . '
                </button>';
    
    return '<div class="action-buttons">' . $editBtn . ' ' . $deleteBtn . '</div>';
})
                ->rawColumns(['color', 'action'])
                ->make(true);
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
            // Validate with exclude ID to ignore current record in uniqueness checks
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
        if (!$this->hasCarPermission()) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to delete cars.'
            ], 403);
        }

        $result = $this->deleteCar($id);
        
        return response()->json([
            'success' => true,
            'message' => $result['message']
        ]);
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
        $carId = $request->get('car_id'); // For edit mode, exclude current car
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