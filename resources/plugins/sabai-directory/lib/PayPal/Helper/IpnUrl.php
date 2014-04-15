<?php
class Sabai_Addon_PayPal_Helper_IpnUrl extends Sabai_Helper
{    
    /**
     * @param Sabai $application
     */
    public function help(Sabai $application, $sandbox = false)
    {
        return $application->MainUrl('/sabai/paypal/ipn', $sandbox ? array('sandbox' => 1) : array());
    }
}