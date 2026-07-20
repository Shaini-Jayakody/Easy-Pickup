<?php

namespace App\Models\CarDetail;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\CarDetail\CarModel;

class Car extends Model
{
    use HasFactory;

    protected $table = 'tbl_cars';
    public $timestamps = false;

    protected $fillable = [
        'ref_no',
        'name',
        'engine_number',
        'chassis_number',
        'color',
        'transmition',
        'model_id'
    ];

    public function model()
    {
        return $this->belongsTo(CarModel::class,'model_id', 'id');
    }

}
