<?php
class Sabai_Addon_PayPal_Controller_Ipn extends Sabai_Controller
{    
    protected function _doExecute(Sabai_Context $context)
    {   
        // reading posted data from directly from $_POST causes serialization 
        // issues with array data in POST
        // reading raw POST data from input stream instead.
        $ipn = array();
        parse_str(file_get_contents('php://input'), $ipn);
        $request = array('cmd' => '_notify-validate') + $ipn;
  
        // Post IPN data back to paypal to validate
        $curl_options = array (
            CURLOPT_URL => !empty($_REQUEST['sandbox']) ? 'https://www.sandbox.paypal.com/cgi-bin/webscr' : 'https://www.paypal.com/cgi-bin/webscr',
            //CURLOPT_VERBOSE => 1,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_CAINFO => $this->getAddonPath('PayPal') . '/cacert.pem',
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_POST => 1,
            CURLOPT_POSTFIELDS => http_build_query($request),
            CURLOPT_FORBID_REUSE => 1,
            CURLOPT_HTTPHEADER => array('Connection: Close'),
        );
        $ch = curl_init();
        curl_setopt_array($ch, $curl_options);
        $response = curl_exec($ch);

        //Checking for cURL errors
        if (curl_errno($ch)) {            
            curl_close($ch);
            $this->LogInfo(curl_error($ch));
            return;
        }  
        curl_close($ch);

        // Check response
        if (strcmp($response, 'VERIFIED') !== 0) {
            // Error
            $this->LogError('Could not verify IPN: ' . $response . ' ' . http_build_query($request));
            return;
        }
        
        $this->doEvent('PayPalIpnReceived', array($ipn));
    }
}