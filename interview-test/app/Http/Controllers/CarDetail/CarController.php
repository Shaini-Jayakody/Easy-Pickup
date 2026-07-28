<?php

namespace App\Http\Controllers\CarDetail;

use App\Http\Controllers\Controller;
use App\Traits\Car;
use Illuminate\Http\Request;
use App\Models\CarDetail\CarBrand as Brand;
use App\Models\CarDetail\Car as CarDetail;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class CarController extends Controller
{
    use Car;

    public function index(Request $request)
    {
        if ($request->ajax()) {
            // Use the trait method to get cars
            $cars = $this->getAllCars();

            // Brand filter
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
                ->addColumn('number_plate', function($car) {
                    return $car->number_plate ?? 'N/A';
                })
                ->addColumn('rent_price', function($car) {
                    return $car->rent_price_per_hour ? '$' . number_format($car->rent_price_per_hour, 2) : 'N/A';
                })
                ->make(true);
        }


        // Use trait method to get brands
        $brands = $this->getAllBrands();

        return view('car-details.cars.index', compact('brands'));
    }

    public function form()
    {
        // Check permission using trait method
        if (!$this->hasCarPermission()) {
            return redirect()->route('home')->with('error', 'You do not have permission to add cars.');
        }

        // Use trait methods to get data
        $brands = $this->getBrandsForSelect();
        $models = $this->getModelsForSelect();
        
        return view('car-details.cars.components.form', compact('brands', 'models'));
    }

    public function save(Request $request)
    {
        // Check permission using trait method
       if (!$this->hasCarPermission()) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to add cars.'
            ], 403);
        }

        try {
            // Validate using trait method
            $validated = $this->validateCarData($request->all());
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->validator->errors()->all()
            ], 422);
        }


        // Prepare car data
        $carArray = [
            'model_id' => $validated['car_model'],
            'name' => $validated['car_name'],
            'color' => $validated['car_color'],
            'engine_number' => $validated['eng_number'],
            'chassis_number' => $validated['chas_number'],
            'number_plate' => $validated['number_plate'],
            'rent_price_per_hour' => $validated['rent_price_per_hour'],
            'transmition' => $validated['car_trans']
        ];

        // Save using trait method
        $result = $this->saveCar($carArray);
        
       return response()->json([
            'success' => true,
            'message' => $result['message'],
            'car' => $result['car']
        ]);
    }

    // AJAX endpoint to check engine number
public function checkEngineNumber(Request $request)
{
    $engineNumber = $request->get('value');
    $exists = $this->engineNumberExists($engineNumber);
    
    return response()->json(['exists' => $exists]);
}

// AJAX endpoint to check chassis number
public function checkChassis(Request $request)
{
    $chassisNumber = $request->get('value');
    $exists = $this->chassisExists($chassisNumber);
    
    return response()->json(['exists' => $exists]);
}

// AJAX endpoint to check number plate
public function checkNumberPlate(Request $request)
{
    $numberPlate = $request->get('value');
    $exists = $this->numberPlateExists($numberPlate);
    
    return response()->json(['exists' => $exists]);
}
}