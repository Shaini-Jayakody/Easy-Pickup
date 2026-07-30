<?php

namespace App\Traits;

trait BookingStatusTrait
{
    /**
     * Check if booking is pending
     */
    public function isPending()
    {
        return strtolower((string) $this->status) === 'pending';
    }

    /**
     * Check if booking is confirmed
     */
    public function isConfirmed()
    {
        return strtolower((string) $this->status) === 'confirmed';
    }

    /**
     * Check if booking is active
     */
    public function isActive()
    {
        return strtolower((string) $this->status) === 'active';
    }

    /**
     * Check if booking is returned
     */
    public function isReturned()
    {
        return strtolower((string) $this->status) === 'returned';
    }

    /**
     * Check if booking is completed
     */
    public function isCompleted()
    {
        return strtolower((string) $this->status) === 'completed';
    }

    /**
     * Check if booking is cancelled
     */
    public function isCancelled()
    {
        return strtolower((string) $this->status) === 'cancelled';
    }

    /**
     * Check if booking can be cancelled
     */
    public function canBeCancelled()
    {
        return in_array(strtolower((string) $this->status), ['pending', 'confirmed']);
    }

    /**
     * Get status badge class
     */
    public function getStatusBadgeClass()
    {
        return match($this->status) {
            'pending' => 'label label-warning',
            'confirmed' => 'label label-info',
            'active' => 'label label-success',
            'returned' => 'label label-primary',
            'completed' => 'label label-success',
            'cancelled' => 'label label-danger',
            default => 'label label-default'
        };
    }

    /**
     * Get status text
     */
    public function getStatusText()
    {
        return match($this->status) {
            'pending' => 'Pending',
            'confirmed' => 'Confirmed',
            'active' => 'Active',
            'returned' => 'Returned',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
            default => ucfirst($this->status)
        };
    }
}