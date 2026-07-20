<?php

namespace App\Http\Controllers\CarDetail;

use App\Http\Controllers\Controller;
use App\Models\CarDetail\CarModel;
use App\Traits\Car;
use Illuminate\Http\Request;
use App\Models\CarDetail\CarModel as Model;
use App\Models\CarDetail\CarBrand as Brand;
use App\Models\CarDetail\Car as CarDetail;

class CarController extends Controller
{
    use Car;

    public function index()
    {
        $cars = CarDetail::with(['model','model.brand'])->paginate(10);
        return view('car-details.cars.index',compact('cars'));
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
    }
}
