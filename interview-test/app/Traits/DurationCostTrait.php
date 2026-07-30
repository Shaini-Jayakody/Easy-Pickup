<?php

namespace App\Traits;

use Carbon\Carbon;

trait DurationCostTrait
{
    /**
     * Get duration in hours (rounded up)
     */
    public function getDurationInHours(): int
    {
        if (!$this->rental_start_date || !$this->rental_end_date) {
            return 0;
        }
        $start = Carbon::parse($this->rental_start_date);
        $end = Carbon::parse($this->rental_end_date);
        return (int) ceil($start->diffInHours($end));
    }

    /**
     * Calculate estimated cost
     * Formula: Duration (hours) × Price Per Hour
     */
    public function calculateEstimatedCost(): float
    {
        $duration = $this->getDurationInHours();
        $pricePerHour = $this->car->rent_price_per_hour ?? 0;
        return $duration * $pricePerHour;
    }

    /**
     * Update estimated cost and save
     */
    public function updateEstimatedCost(): void
    {
        $this->estimated_cost = $this->calculateEstimatedCost();
        $this->saveQuietly();
    }

    /**
     * Get formatted estimated cost
     */
    public function getEstimatedCostFormatted(): string
    {
        return 'Rs. ' . number_format($this->estimated_cost ?? 0, 2);
    }

    /**
     * Get duration in hours with formatted string
     */
    public function getDurationFormatted(): string
    {
        return $this->getDurationInHours() . ' hrs';
    }

    /**
     * Boot method for auto-calculation
     */
    protected static function bootDurationCostTrait()
    {
        static::creating(function ($model) {
            if ($model->car_id && $model->rental_start_date && $model->rental_end_date) {
                $duration = $model->getDurationInHours();
                $pricePerHour = $model->car->rent_price_per_hour ?? 0;
                $model->estimated_cost = $duration * $pricePerHour;
            }
        });

        static::updating(function ($model) {
            if ($model->isDirty(['car_id', 'rental_start_date', 'rental_end_date'])) {
                if ($model->car_id && $model->rental_start_date && $model->rental_end_date) {
                    $duration = $model->getDurationInHours();
                    $pricePerHour = $model->car->rent_price_per_hour ?? 0;
                    $model->estimated_cost = $duration * $pricePerHour;
                }
            }
        });
    }
}