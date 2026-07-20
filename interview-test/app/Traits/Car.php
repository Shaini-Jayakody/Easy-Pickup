<?php

namespace App\Traits;

use App\Models\CarDetail\Car as CarModel;

trait Car {

    public function saveCar(array $carArray): string
    {
        $carArray['ref_no'] = $this->generateReference();
        $car = CarModel::updateOrCreate(['id' => 0],$carArray);
        return "succes fully created $car->name (Ref:$car->ref_no)";
    }

    private function generateReference(): string
    {
        return 'CAR'.time();
    }
    
}