<?php

namespace App\Helpers;

use App\Traits\UserValidationTrait;

class ValidationHelper
{
    use UserValidationTrait;

    /**
     * Get frontend validation rules
     */
    public static function getRules()
    {
        $helper = new self();
        return $helper->getFrontendValidationRules();
    }
}