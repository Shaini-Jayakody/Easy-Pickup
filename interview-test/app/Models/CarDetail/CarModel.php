<?php

namespace App\Models\CarDetail;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\CarDetail\CarBrand;
use App\Models\CarDetail\Car;

class CarModel extends Model
{
    use HasFactory;

    protected $table = 'tbl_car_models';

    public function brand()
    {
        return $this->belongsTo(CarBrand::class,'brand_id','id');
    }

    public function cars()
    {
        return $this->hasMany(Car::class,'model_id');
    }

}
