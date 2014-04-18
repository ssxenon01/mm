<?php
class Sabai_Addon_PayPal_Helper_ExpressCheckoutUrl extends Sabai_Helper
{    
    /**
     * @param Sabai $application
     * @param stirng|array $token
     */
    public function help(Sabai $application, $token, $sandbox = false)
    {
        if (is_array($token)) {
            $token = $token['TOKEN'];
        }
        return !$sandbox
            ? 'https://www.paypal.com/webscr&cmd=_express-checkout&token=' . $token
            : 'https://www.sandbox.paypal.com/webscr?cmd=_express-checkout&token=' . $token;
    }
}