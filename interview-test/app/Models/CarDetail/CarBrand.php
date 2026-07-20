<?php

namespace App\Models\CarDetail;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\CarDetail\CarModel;

class CarBrand extends Model
{
    use HasFactory;

    protected $table = 'tbl_car_brands';

    public function models()
    {
        return $this->hasMany(CarModel::class,'brand_id');
    }

}
