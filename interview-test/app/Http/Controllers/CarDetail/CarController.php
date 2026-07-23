<?php

namespace App\Http\Controllers\CarDetail;

use App\Http\Controllers\Controller;
use App\Models\CarDetail\CarModel;
use App\Traits\Car;
use Illuminate\Http\Request;
use App\Models\CarDetail\CarModel as Model;
use App\Models\CarDetail\CarBrand as Brand;
use App\Models\CarDetail\Car as CarDetail;
use Yajra\DataTables\Facades\DataTables;


class CarController extends Controller
{
    use Car;

    public function index(Request $request)
    {
        if ($request->ajax()) {
            $cars = CarDetail::with(['model', 'model.brand'])->select('tbl_cars.*');


           //brand filter 
            if ($request->filled('brand_id')) {
                $cars->whereHas('model', function($query) use ($request) {
                    $query->where('brand_id', $request->brand_id);
                });
            }
            
            return DataTables::of($cars)
                ->addIndexColumn()
                ->addColumn('brand', function($car) {
                    return $car->model->brand->name ?? 'N/A';
                })
                ->addColumn('model_name', function($car) {
                    return $car->model->name ?? 'N/A';
                })
                ->make(true);
        }

        $brands = Brand::all();//pass brand

        return view('car-details.cars.index');
    }

    public function form()
    {
        $brands = Brand::get();
        $models = Model::get();
        return view('car-details.cars.components.form',compact(['brands','models']));
    }

    public function save(Request $request)
    {
        $carArray = [
            'model_id' => $request->car_model,
            'name' => $request->car_name,
            'color' => $request->car_color,
            'engine_number' => $request->eng_number,
            'chassis_number' => $request->chas_number,
            'transmition' => $request->car_trans
        ];

        $this->saveCar($carArray);
         return redirect()->route('car')->with('success', 'Car added successfully!');
    }
}
