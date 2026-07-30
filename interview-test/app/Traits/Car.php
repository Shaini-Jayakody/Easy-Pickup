<?php

namespace App\Traits;

use App\Models\CarDetail\Car as CarModel;
use App\Models\CarDetail\CarBrand as Brand;
use App\Models\CarDetail\CarModel as Model;
use App\Models\Booking;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

trait Car
{
   
    // RETRIEVAL METHODS
    /**
     * Get all cars with relationships
     */
    public function getAllCars()
    {
        return CarModel::with(['model', 'model.brand'])->select('tbl_cars.*');
    }

    /**
     * Get cars filtered by brand
     */
    public function getCarsByBrand($brandId)
    {
        return $this->getAllCars()->whereHas('model', function($query) use ($brandId) {
            $query->where('brand_id', $brandId);
        });
    }

    /**
     * Get a single car by ID with relationships
     */
    public function getCarById($id)
    {
        return CarModel::with(['model', 'model.brand'])->findOrFail($id);
    }

    /**
     * Get car by reference number
     */
    public function getCarByRef(string $refNo): ?CarModel
    {
        return CarModel::where('ref_no', $refNo)->first();
    }

    /**
     * Get all brands
     */
    public function getAllBrands()
    {
        return Brand::all();
    }

    /**
     * Get all models
     */
    public function getAllModels()
    {
        return Model::all();
    }

    /**
     * Get brands for select dropdown
     */
    public function getBrandsForSelect()
    {
        return Brand::get();
    }

    /**
     * Get models for select dropdown
     */
    public function getModelsForSelect()
    {
        return Model::get();
    }

  /**
 * Check if car has any bookings
 */
public function carHasBookings($carId): bool
{
    return Booking::where('car_id', $carId)->exists();
}

/**
 * Get active bookings count for a car
 */
public function getActiveBookingsCount($carId): int
{
    return Booking::where('car_id', $carId)
        ->whereIn('status', ['pending', 'confirmed', 'active'])
        ->count();
}

    /**
     * Get all bookings for a car
     */
    public function getCarBookings($carId)
    {
        return Booking::where('car_id', $carId)
            ->with(['user'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    // CRUD OPERATIONS

    /**
     * Save a new car
     */
    public function saveCar(array $carArray): array
    {
        // Generate unique reference number
        $carArray['ref_no'] = $this->generateUniqueReference();
        
        // Create new car
        $car = CarModel::create($carArray);
        
        return [
            'success' => true,
            'message' => "Successfully created {$car->name} (Ref: {$car->ref_no})",
            'car' => $car
        ];
    }

    /**
     * Update an existing car
     */
    public function updateCar($id, array $carArray): array
    {
        $car = CarModel::findOrFail($id);
        
        // ✅ Check if car has active bookings before updating
        if ($this->carHasBookings($id)) {
            $bookingCount = $this->getActiveBookingsCount($id);
            throw new \Exception("Cannot update car because it has {$bookingCount} active booking(s). Please complete or cancel the bookings first.");
        }
        
        $car->update($carArray);
        
        return [
            'success' => true,
            'message' => "Successfully updated {$car->name} (Ref: {$car->ref_no})",
            'car' => $car
        ];
    }

    /**
     * Delete a car
     */
    public function deleteCar($id): array
    {
        $car = CarModel::findOrFail($id);
        $carName = $car->name;
        
        // ✅ Check if car has active bookings before deleting
        if ($this->carHasBookings($id)) {
            $bookingCount = $this->getActiveBookingsCount($id);
            throw new \Exception("Cannot delete car because it has {$bookingCount} active booking(s). Please complete or cancel the bookings first.");
        }
        
        $car->delete();
        
        return [
            'success' => true,
            'message' => "Successfully deleted {$carName}"
        ];
    }


    // UNIQUENESS CHECKS

    /**
     * Check if engine number exists (with optional exclude)
     */
    public function engineNumberExists(string $engineNumber, $excludeId = null): bool
    {
        $query = CarModel::where('engine_number', $engineNumber);
        
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }
        
        return $query->exists();
    }

    /**
     * Check if chassis number exists (with optional exclude)
     */
    public function chassisExists(string $chassisNumber, $excludeId = null): bool
    {
        $query = CarModel::where('chassis_number', $chassisNumber);
        
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }
        
        return $query->exists();
    }

    /**
     * Check if number plate exists (with optional exclude)
     */
    public function numberPlateExists(string $numberPlate, $excludeId = null): bool
    {
        $query = CarModel::where('number_plate', $numberPlate);
        
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }
        
        return $query->exists();
    }


    // HELPER METHODS

    /**
     * Generate a unique reference number (no duplicates)
     */
    public function generateUniqueReference(): string
    {
        do {
            // Format: CAR + YYMMDD + 4 digit random
            $refNo = 'CAR' . date('ymd') . rand(1000, 9999);
        } while ($this->getCarByRef($refNo));
        
        return $refNo;
    }

    /**
     * Validate car data (with optional exclude ID for updates)
     */
    public function validateCarData(array $data, $excludeId = null)
    {
        $rules = [
            'car_brand' => ['required', 'exists:tbl_car_brands,id'],
            'car_model' => ['required', 'exists:tbl_car_models,id'],
            'car_name' => ['required', 'string', 'max:255', 'min:2'],
            'car_color' => ['required', 'string', 'max:100', 'min:2'],
            'eng_number' => ['required', 'string', 'max:50', 'min:2'],
            'chas_number' => ['required', 'string', 'max:255', 'min:2'],
            'number_plate' => ['required', 'string', 'max:20', 'min:2'],
            'rent_price_per_hour' => ['required', 'numeric', 'min:0', 'max:999999.99'],
            'car_trans' => ['required', 'in:Auto,Manual,Tiptronic'],
        ];

        // Add uniqueness rules with exclude if provided
        if ($excludeId) {
            $rules['chas_number'][] = 'unique:tbl_cars,chassis_number,' . $excludeId;
            $rules['number_plate'][] = 'unique:tbl_cars,number_plate,' . $excludeId;
            $rules['eng_number'][] = 'unique:tbl_cars,engine_number,' . $excludeId;
        } else {
            $rules['chas_number'][] = 'unique:tbl_cars,chassis_number';
            $rules['number_plate'][] = 'unique:tbl_cars,number_plate';
            $rules['eng_number'][] = 'unique:tbl_cars,engine_number';
        }

        $messages = [
            // Brand & Model
            'car_brand.required' => 'Please select a brand.',
            'car_brand.exists' => 'Selected brand is invalid.',
            'car_model.required' => 'Please select a model.',
            'car_model.exists' => 'Selected model is invalid.',
            
            // Car Name
            'car_name.required' => 'Car name is required.',
            'car_name.min' => 'Car name must be at least 2 characters.',
            'car_name.max' => 'Car name cannot exceed 255 characters.',
            
            // Color
            'car_color.required' => 'Car color is required.',
            'car_color.min' => 'Color must be at least 2 characters.',
            'car_color.max' => 'Color cannot exceed 100 characters.',
            
            // Engine Number
            'eng_number.required' => 'Engine number is required.',
            'eng_number.min' => 'Engine number must be at least 2 characters.',
            'eng_number.max' => 'Engine number cannot exceed 50 characters.',
            'eng_number.unique' => 'This engine number already exists. Please enter a unique engine number.',
            
            // Chassis Number
            'chas_number.required' => 'Chassis number is required.',
            'chas_number.min' => 'Chassis number must be at least 2 characters.',
            'chas_number.max' => 'Chassis number cannot exceed 255 characters.',
            'chas_number.unique' => 'This chassis number already exists. Please enter a unique chassis number.',
            
            // Number Plate
            'number_plate.required' => 'Number plate is required.',
            'number_plate.min' => 'Number plate must be at least 2 characters.',
            'number_plate.max' => 'Number plate cannot exceed 20 characters.',
            'number_plate.unique' => 'This number plate already exists. Please enter a unique number plate.',
            
            // Rent Price
            'rent_price_per_hour.required' => 'Rent price per hour is required.',
            'rent_price_per_hour.numeric' => 'Rent price must be a valid number.',
            'rent_price_per_hour.min' => 'Rent price cannot be negative.',
            'rent_price_per_hour.max' => 'Rent price is too high.',
            
            // Transmission
            'car_trans.required' => 'Please select transmission type.',
            'car_trans.in' => 'Invalid transmission type selected.',
        ];

        $validator = Validator::make($data, $rules, $messages);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $validator->validated();
    }

    /**
     * Check if user has permission (admin or manager)
     */
    public function hasCarPermission(): bool
    {
        if (!Auth::check()) {
            return false;
        }
        return in_array(Auth::user()->role, ['admin', 'manager']);
    }

    // FRONTEND VALIDATION RULES (for JavaScript)

    /**
     * Get frontend validation rules for JavaScript
     */
    public function getCarFrontendValidationRules()
    {
        return [
            'car_brand' => [
                'required' => true,
                'messages' => [
                    'required' => 'Please select a brand.'
                ]
            ],
            'car_model' => [
                'required' => true,
                'messages' => [
                    'required' => 'Please select a model.'
                ]
            ],
            'car_name' => [
                'required' => true,
                'min' => 2,
                'max' => 255,
                'messages' => [
                    'required' => 'Car name is required.',
                    'min' => 'Car name must be at least 2 characters.',
                    'max' => 'Car name cannot exceed 255 characters.'
                ]
            ],
            'car_color' => [
                'required' => true,
                'min' => 2,
                'max' => 100,
                'messages' => [
                    'required' => 'Car color is required.',
                    'min' => 'Color must be at least 2 characters.',
                    'max' => 'Color cannot exceed 100 characters.'
                ]
            ],
            'eng_number' => [
                'required' => true,
                'min' => 2,
                'max' => 50,
                'messages' => [
                    'required' => 'Engine number is required.',
                    'min' => 'Engine number must be at least 2 characters.',
                    'max' => 'Engine number cannot exceed 50 characters.'
                ]
            ],
            'chas_number' => [
                'required' => true,
                'min' => 2,
                'max' => 255,
                'messages' => [
                    'required' => 'Chassis number is required.',
                    'min' => 'Chassis number must be at least 2 characters.',
                    'max' => 'Chassis number cannot exceed 255 characters.',
                    'unique' => 'This chassis number already exists. Please enter a unique chassis number.'
                ]
            ],
            'number_plate' => [
                'required' => true,
                'min' => 2,
                'max' => 20,
                'messages' => [
                    'required' => 'Number plate is required.',
                    'min' => 'Number plate must be at least 2 characters.',
                    'max' => 'Number plate cannot exceed 20 characters.',
                    'unique' => 'This number plate already exists. Please enter a unique number plate.'
                ]
            ],
            'rent_price_per_hour' => [
                'required' => true,
                'min' => 0,
                'max' => 999999.99,
                'messages' => [
                    'required' => 'Rent price per hour is required.',
                    'min' => 'Rent price cannot be negative.',
                    'max' => 'Rent price is too high.'
                ]
            ],
            'car_trans' => [
                'required' => true,
                'values' => ['Auto', 'Manual', 'Tiptronic'],
                'messages' => [
                    'required' => 'Please select transmission type.',
                    'values' => 'Invalid transmission type selected.'
                ]
            ]
        ];
    }
}