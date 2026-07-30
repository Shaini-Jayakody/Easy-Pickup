<?php

namespace App\Traits;

use App\Models\CarDetail\CarBrand;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Validator;

trait CarBrandTrait
{
    /**
     * Get all brands with model count
     */
    public function getAllBrands()
    {
        return CarBrand::withCount('models')->orderBy('name')->get();
    }

    /**
     * Get a single brand by ID
     */
    public function getBrandById($id)
    {
        return CarBrand::withCount('models')->findOrFail($id);
    }

    /**
     * Get brands for dropdown
     */
    public function getBrandsForDropdown()
    {
        return CarBrand::orderBy('name')->get();
    }

    /**
     * Validate brand data
     */
    public function validateBrandData(array $data, $excludeId = null)
    {
        $rules = [
            'name' => 'required|string|max:255|unique:tbl_car_brands,name'
        ];

        if ($excludeId) {
            $rules['name'] = 'required|string|max:255|unique:tbl_car_brands,name,' . $excludeId;
        }

        $messages = [
            'name.required' => 'Brand name is required.',
            'name.max' => 'Brand name cannot exceed 255 characters.',
            'name.unique' => 'This brand already exists.',
        ];

        $validator = Validator::make($data, $rules, $messages);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $validator->validated();
    }

    /**
     * Create a new brand
     */
    public function createBrand(array $data)
    {
        $brand = CarBrand::create($data);

        return [
            'success' => true,
            'message' => 'Brand created successfully!',
            'brand' => $brand
        ];
    }

    /**
     * Update an existing brand
     */
    public function updateBrand($id, array $data)
    {
        $brand = $this->getBrandById($id);
        $brand->update($data);

        return [
            'success' => true,
            'message' => 'Brand updated successfully!',
            'brand' => $brand
        ];
    }

    /**
     * Delete a brand
     */
    public function deleteBrand($id)
    {
        $brand = $this->getBrandById($id);
        
        // Check if brand has models
        if ($brand->models()->count() > 0) {
            throw new \Exception('Cannot delete brand because it has models associated.');
        }

        $brand->delete();

        return [
            'success' => true,
            'message' => 'Brand deleted successfully!'
        ];
    }

    /**
     * Check if brand has models
     */
    public function brandHasModels($id)
    {
        $brand = $this->getBrandById($id);
        return $brand->models()->count() > 0;
    }

    /**
     * Format brand for response
     */
    public function formatBrandForResponse($brand)
    {
        return [
            'id' => $brand->id,
            'name' => $brand->name,
            'models_count' => $brand->models_count ?? $brand->models()->count(),
        ];
    }

    /**
     * Get validation rules for frontend
     */
    public function getBrandValidationRules()
    {
        return [
            'name' => [
                'required' => true,
                'max' => 255,
                'messages' => [
                    'required' => 'Brand name is required.',
                    'max' => 'Brand name cannot exceed 255 characters.',
                    'unique' => 'This brand already exists.'
                ]
            ]
        ];
    }
}