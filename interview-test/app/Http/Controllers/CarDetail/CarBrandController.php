<?php

namespace App\Http\Controllers\CarDetail;

use App\Http\Controllers\Controller;
use App\Traits\CarBrandTrait;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class CarBrandController extends Controller
{
    use CarBrandTrait;

    /**
     * Display a listing of brands
     */
    public function index()
    {
        $brands = $this->getAllBrands();
        return view('car-details.brands.index', compact('brands'));
    }

    /**
     * Show the form for creating a new brand
     */
    public function create()
    {
        return view('car-details.brands.form');
    }

    /**
     * Store a newly created brand
     */
    public function store(Request $request)
    {
        try {
            $validated = $this->validateBrandData($request->all());
            $result = $this->createBrand($validated);
            return response()->json($result);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->validator->errors()->all()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating brand: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show the form for editing a brand
     */
    public function edit($id)
    {
        $brand = $this->getBrandById($id);
        return view('car-details.brands.form', compact('brand'));
    }

    /**
     * Update the specified brand
     */
    public function update(Request $request, $id)
    {
        try {
            $validated = $this->validateBrandData($request->all(), $id);
            $result = $this->updateBrand($id, $validated);
            return response()->json($result);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->validator->errors()->all()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating brand: ' . $e->getMessage()
            ], 500);
        }
    }

   /**
 * Delete the specified brand
 */
public function destroy($id)
{
    try {
        \Log::info('======= DELETE BRAND REQUEST =======');
        \Log::info('Brand ID: ' . $id);
        \Log::info('User ID: ' . auth()->id());
        \Log::info('Request method: ' . request()->method());
        
        $brand = $this->getBrandById($id);
        
        // Check if brand has models
        $modelCount = $brand->models()->count();
        \Log::info('Model count for brand: ' . $modelCount);
        
        if ($modelCount > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete brand because it has ' . $modelCount . ' models associated. Please delete the models first.'
            ], 422);
        }

        $brand->delete();
        \Log::info('Brand deleted successfully');

        return response()->json([
            'success' => true,
            'message' => 'Brand deleted successfully!'
        ]);

    } catch (\Exception $e) {
        \Log::error('Delete brand error: ' . $e->getMessage());
        \Log::error('Stack trace: ' . $e->getTraceAsString());
        return response()->json([
            'success' => false,
            'message' => 'Error deleting brand: ' . $e->getMessage()
        ], 500);
    }
}
}