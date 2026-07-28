<?php

namespace App\Traits;

use App\Models\CarDetail\Car as CarModel;
use App\Models\CarDetail\CarBrand as Brand;
use App\Models\CarDetail\CarModel as Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

trait Car
{
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
        $car->delete();
        
        return [
            'success' => true,
            'message' => "Successfully deleted {$carName}"
        ];
    }

    /**
     * Get a single car by ID
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
     * Check if engine number exists
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
     * Check if chassis number exists
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
     * Check if number plate exists
     */
    public function numberPlateExists(string $numberPlate, $excludeId = null): bool
    {
        $query = CarModel::where('number_plate', $numberPlate);
        
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }
        
        return $query->exists();
    }

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
     * Validate car data
     */
    public function validateCarData(array $data, $chassisId = null)
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

        // Add unique rules
        if ($chassisId) {
            $rules['chas_number'][] = 'unique:tbl_cars,chassis_number,' . $chassisId;
            $rules['number_plate'][] = 'unique:tbl_cars,number_plate,' . $chassisId;
        } else {
            $rules['chas_number'][] = 'unique:tbl_cars,chassis_number';
            $rules['number_plate'][] = 'unique:tbl_cars,number_plate';
        }

        $messages = [
            'car_brand.required' => 'Please select a brand.',
            'car_brand.exists' => 'Selected brand is invalid.',
            'car_model.required' => 'Please select a model.',
            'car_model.exists' => 'Selected model is invalid.',
            'car_name.required' => 'Car name is required.',
            'car_name.min' => 'Car name must be at least 2 characters.',
            'car_name.max' => 'Car name cannot exceed 255 characters.',
            'car_color.required' => 'Car color is required.',
            'car_color.min' => 'Color must be at least 2 characters.',
            'car_color.max' => 'Color cannot exceed 100 characters.',
            'eng_number.required' => 'Engine number is required.',
            'eng_number.min' => 'Engine number must be at least 2 characters.',
            'eng_number.max' => 'Engine number cannot exceed 50 characters.',
            'chas_number.required' => 'Chassis number is required.',
            'chas_number.min' => 'Chassis number must be at least 2 characters.',
            'chas_number.max' => 'Chassis number cannot exceed 255 characters.',
            'chas_number.unique' => 'This chassis number already exists. Please enter a unique chassis number.',
            'number_plate.required' => 'Number plate is required.',
            'number_plate.min' => 'Number plate must be at least 2 characters.',
            'number_plate.max' => 'Number plate cannot exceed 20 characters.',
            'number_plate.unique' => 'This number plate already exists. Please enter a unique number plate.',
            'rent_price_per_hour.required' => 'Rent price per hour is required.',
            'rent_price_per_hour.numeric' => 'Rent price must be a valid number.',
            'rent_price_per_hour.min' => 'Rent price cannot be negative.',
            'rent_price_per_hour.max' => 'Rent price is too high.',
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
                    'unique' => 'This chassis number already exists.'
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
                    'unique' => 'This number plate already exists.'
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