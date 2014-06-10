<?php
class Sabai_Addon_PayPal extends Sabai_Addon
    implements Sabai_Addon_System_IMainRouter
{
    const VERSION = '1.2.31', PACKAGE = 'sabai-directory';
    
    /* Start implementation of Sabai_Addon_System_IMainRouter */
    
    public function systemGetMainRoutes()
    {
        return array(
            '/sabai/paypal/ipn' => array(
                'controller' => 'Ipn',
                'type' => Sabai::ROUTE_CALLBACK,
                //'method' => 'post',
            ),
        );
    }

    public function systemOnAccessMainRoute(Sabai_Context $context, $path, $accessType, array &$route)
    {

    }

    public function systemGetMainRouteTitle(Sabai_Context $context, $path, $title, $titleType, array $route)
    {
        
    }

    /* End implementation of Sabai_Addon_System_IMainRouter */
    
    public function getPayPalSettingsForm(array $settings = array(), array $parents = array())
    {
        $settings += array(
            'version' => '63.0',
            'user' => '',
            'pwd' => '',
            'sig' => '',
            'sb' => true,
            'sb_user' => '',
            'sb_pwd' => '',
            'sb_sig' => '',
            'sb_processing' => false,
            'currency' => 'USD',
        );
        return array(
            '#tree' => true,
            '#title' => __('PayPal Settings', 'sabai-directory'),
            '#collapsed' => true,
            'currency' => array(
                '#type' => 'select',
                '#title' => __('Currency', 'sabai-directory'),
                '#options' => $this->_application->PaidListings_Currencies(null, true),
                '#default_value' => $settings['currency'],
            ),
            'user' => array(
                '#type' => 'textfield',
                '#title' => __('API Username', 'sabai-directory'),
                '#default_value' => $settings['user'],
                '#size' => 40,
            ),
            'pwd' => array(
                '#type' => 'textfield',
                '#title' => __('API Password', 'sabai-directory'),
                '#default_value' => $settings['pwd'],
                '#size' => 40,
            ),
            'sig' => array(
                '#type' => 'textfield',
                '#title' => __('API Signature', 'sabai-directory'),
                '#default_value' => $settings['sig'],
                '#size' => 70,
            ),
            'version' => array(
                '#type' => 'textfield',
                '#title' => __('API Version', 'sabai-directory'),
                '#default_value' => $settings['version'],
                '#size' => 10,
            ),
            'ipn' => array(
                '#type' => 'item',
                '#title' => __('IPN endpoint URL', 'sabai-directory'),
                '#value' => (string)$this->_application->PayPal_IpnUrl(),
            ),
            'sb' => array(
                '#type' => 'checkbox',
                '#title' => __('Sandbox Mode', 'sabai-directory'),
                '#default_value' => $settings['sb'],
                '#description' => __('Use PayPal in Sandbox mode for testing.', 'sabai-directory'),
            ),
            'sb_user' => array(
                '#type' => 'textfield',
                '#title' => __('Sandbox API Username', 'sabai-directory'),
                '#default_value' => $settings['sb_user'],
                '#size' => 40,
                '#states' => array(
                    'visible' => array(
                        sprintf('input[name="%s[sb][]"]', $this->_application->Form_FieldName($parents)) => array('type' => 'checked', 'value' => true),
                    ),
                ),
            ),
            'sb_pwd' => array(
                '#type' => 'textfield',
                '#title' => __('Sandbox API Password', 'sabai-directory'),
                '#default_value' => $settings['sb_pwd'],
                '#size' => 40,
                '#states' => array(
                    'visible' => array(
                        sprintf('input[name="%s[sb][]"]', $this->_application->Form_FieldName($parents)) => array('type' => 'checked', 'value' => true),
                    ),
                ),
            ),
            'sb_sig' => array(
                '#type' => 'textfield',
                '#title' => __('Sandbox API Signature', 'sabai-directory'),
                '#default_value' => $settings['sb_sig'],
                '#size' => 70,
                '#states' => array(
                    'visible' => array(
                        sprintf('input[name="%s[sb][]"]', $this->_application->Form_FieldName($parents)) => array('type' => 'checked', 'value' => true),
                    ),
                ),
            ),
            'sb_processing' => array(
                '#type' => 'checkbox',
                '#title' => __('Set payment status as "Processing"', 'sabai-directory'),
                '#description' => __('Check this option to set the payment status of orders to "Processing" even when it is complete, which is useful for testing IPN.', 'sabai-directory'),
                '#default_value' => $settings['sb_processing'],
                '#states' => array(
                    'visible' => array(
                        sprintf('input[name="%s[sb][]"]', $this->_application->Form_FieldName($parents)) => array('type' => 'checked', 'value' => true),
                    ),
                ),
            ),
            'sb_ipn' => array(
                '#type' => 'item',
                '#title' => __('Sandbox IPN endpoint URL', 'sabai-directory'),
                '#value' => (string)$this->_application->PayPal_IpnUrl(true),
                '#states' => array(
                    'visible' => array(
                        sprintf('input[name="%s[sb][]"]', $this->_application->Form_FieldName($parents)) => array('type' => 'checked', 'value' => true),
                    ),
                ),
            ),
        );
    }
}
