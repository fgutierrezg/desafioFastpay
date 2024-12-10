<?php

namespace App\Services;

class FeeCalculator
{
    public static function ratePaymentCalculation($amount, $feePercentage)
    {
        return $amount * ($feePercentage / 100);
    }
}
