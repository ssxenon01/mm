<?php
class Sabai_Addon_PaidListings_Helper_MoneyFormat extends Sabai_Helper
{    
    /**
     * @param Sabai $application
     * @param mixed $value
     * @param string $currency
     */
    public function help(Sabai $application, $value, $currency)
    {
        $symbol = $application->PaidListings_Currencies($currency, false);
        return $currency === 'JPY' ? $symbol . number_format($value) : $symbol . number_format($value, 2);
    }
}