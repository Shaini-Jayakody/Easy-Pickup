<?php

namespace App\Traits;

use App\Models\CarDetail\CarModel;
use App\Models\CarDetail\CarBrand;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Validator;

trait CarModelTrait
{
    /**
     * Get all models with relationships
     */
    public function getAllModels()
    {
        return CarModel::with('brand')->orderBy('name');
    }

    /**
     * Get models by brand
     */
    public function getModelsByBrand($brandId)
    {
        return CarModel::where('brand_id', $brandId)->orderBy('name')->get();
    }

    /**
     * Get a single model by ID
     */
    public function getModelById($id)
    {
        return CarModel::with('brand')->findOrFail($id);
    }

    /**
     * Get all brands for dropdown
     */
    public function getBrandsForDropdown()
    {
        return CarBrand::orderBy('name')->get();
    }

    /**
     * Validate model data
     */
    public function validateModelData(array $data, $excludeId = null)
    {
        $rules = [
            'name' => 'required|string|max:255',
            'brand_id' => 'required|exists:tbl_car_brands,id',
            'year' => 'nullable|integer|min:1900|max:' . date('Y')
        ];

        $messages = [
            'name.required' => 'Model name is required.',
            'name.max' => 'Model name cannot exceed 255 characters.',
            'brand_id.required' => 'Please select a brand.',
            'brand_id.exists' => 'Selected brand does not exist.',
            'year.integer' => 'Year must be a valid number.',
            'year.min' => 'Year must be at least 1900.',
            'year.max' => 'Year cannot be in the future.',
        ];

        // Add uniqueness check for model name within the same brand
        if ($excludeId) {
            $rules['name'] = 'required|string|max:255|unique:tbl_car_models,name,' . $excludeId . ',id,brand_id,' . ($data['brand_id'] ?? '');
        } else {
            $rules['name'] = 'required|string|max:255|unique:tbl_car_models,name,NULL,id,brand_id,' . ($data['brand_id'] ?? '');
        }

        $validator = Validator::make($data, $rules, $messages);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $validator->validated();
    }

    /**
     * Create a new model
     */
    public function createModel(array $data)
    {
        $model = CarModel::create($data);

        return [
            'success' => true,
            'message' => 'Model created successfully!',
            'model' => $model
        ];
    }

    /**
     * Update an existing model
     */
    public function updateModel($id, array $data)
    {
        $model = $this->getModelById($id);
        $model->update($data);

        return [
            'success' => true,
            'message' => 'Model updated successfully!',
            'model' => $model
        ];
    }

    /**
     * Delete a model
     */
    public function deleteModel($id)
    {
        $model = $this->getModelById($id);
        
        // Check if model has cars
        if ($model->cars()->count() > 0) {
            throw new \Exception('Cannot delete model because it has cars associated.');
        }

        $model->delete();

        return [
            'success' => true,
            'message' => 'Model deleted successfully!'
        ];
    }

    /**
     * Check if model has cars
     */
    public function modelHasCars($id)
    {
        $model = $this->getModelById($id);
        return $model->cars()->count() > 0;
    }

    /**
     * Format model for response
     */
    public function formatModelForResponse($model)
    {
        return [
            'id' => $model->id,
            'name' => $model->name,
            'year' => $model->year,
            'brand' => [
                'id' => $model->brand->id ?? null,
                'name' => $model->brand->name ?? null,
            ],
            'cars_count' => $model->cars()->count(),
        ];
    }

    /**
     * Get validation rules for frontend
     */
    public function getModelValidationRules()
    {
        return [
            'name' => [
                'required' => true,
                'max' => 255,
                'messages' => [
                    'required' => 'Model name is required.',
                    'max' => 'Model name cannot exceed 255 characters.'
                ]
            ],
            'brand_id' => [
                'required' => true,
                'messages' => [
                    'required' => 'Please select a brand.'
                ]
            ],
            'year' => [
                'min' => 1900,
                'max' => date('Y'),
                'messages' => [
                    'min' => 'Year must be at least 1900.',
                    'max' => 'Year cannot be in the future.'
                ]
            ]
        ];
    }
}