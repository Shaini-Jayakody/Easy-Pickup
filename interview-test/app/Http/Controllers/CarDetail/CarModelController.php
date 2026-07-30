<?php

namespace App\Http\Controllers\CarDetail;

use App\Http\Controllers\Controller;
use App\Traits\CarModelTrait;
use App\Models\CarDetail\CarBrand;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class CarModelController extends Controller
{
    use CarModelTrait;

    /**
     * Get brands for dropdown
     */
    private function getBrandsForDropdown()
    {
        return CarBrand::orderBy('name')->get();
    }

    /**
     * Display a listing of models
     */
    public function index(Request $request)
    {
        $query = $this->getAllModels();
        
        if ($request->filled('brand_id')) {
            $query->where('brand_id', $request->brand_id);
        }
        
        $models = $query->get();
        $brands = $this->getBrandsForDropdown();
        
        return view('car-details.car-models.index', compact('models', 'brands'));
    }

    /**
     * Show the form for creating a new model
     */
    public function create()
    {
        $brands = $this->getBrandsForDropdown();
        return view('car-details.car-models.form', compact('brands'));
    }

    /**
     * Store a newly created model
     */
    public function store(Request $request)
    {
        try {
            $validated = $this->validateModelData($request->all());
            $result = $this->createModel($validated);
            return response()->json($result);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->validator->errors()->all()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating model: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show the form for editing a model
     */
    public function edit($id)
    {
        $model = $this->getModelById($id);
        $brands = $this->getBrandsForDropdown();
        return view('car-details.car-models.form', compact('model', 'brands'));
    }

    /**
     * Update the specified model
     */
    public function update(Request $request, $id)
    {
        try {
            $validated = $this->validateModelData($request->all(), $id);
            $result = $this->updateModel($id, $validated);
            return response()->json($result);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->validator->errors()->all()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating model: ' . $e->getMessage()
            ], 500);
        }
    }

   /**
 * Delete the specified model
 */
public function destroy($id)
{
    try {
        \Log::info('======= DELETE MODEL REQUEST =======');
        \Log::info('Model ID: ' . $id);
        \Log::info('User ID: ' . auth()->id());
        \Log::info('Request method: ' . request()->method());
        
        $model = $this->getModelById($id);
        
        // Check if model has cars
        $carCount = $model->cars()->count();
        \Log::info('Car count for model: ' . $carCount);
        
        if ($carCount > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete model because it has ' . $carCount . ' cars associated. Please delete the cars first.'
            ], 422);
        }

        $model->delete();
        \Log::info('Model deleted successfully');

        return response()->json([
            'success' => true,
            'message' => 'Model deleted successfully!'
        ]);

    } catch (\Exception $e) {
        \Log::error('Delete model error: ' . $e->getMessage());
        \Log::error('Stack trace: ' . $e->getTraceAsString());
        return response()->json([
            'success' => false,
            'message' => 'Error deleting model: ' . $e->getMessage()
        ], 500);
    }
}
    /**
     * Get models by brand (AJAX)
     */
    public function getByBrand(Request $request)
    {
        try {
            $brandId = $request->brand_id;
            $models = $this->getModelsByBrand($brandId);
            
            return response()->json([
                'success' => true,
                'models' => $models
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching models: ' . $e->getMessage()
            ], 500);
        }
    }
}