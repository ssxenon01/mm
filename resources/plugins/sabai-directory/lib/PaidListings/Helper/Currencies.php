<?php
class Sabai_Addon_PaidListings_Helper_Currencies extends Sabai_Helper
{
    protected $_labels;
    static protected $_currencies = array(
        'USD' => '&#36;',
        'AUD' => '&#36;',
        'CAD' => '&#36;',
        'CZK' => '&#75;&#269;',
        'DKK' => '&#107;&#114;',
        'EUR' => '&#8364;',
        'HKD' => '&#36;',
        'HUF' => '&#70;&#116;',
        'ILS' => '&#8362;',
        'JPY' => '&#165;',
        'MXN' => '&#8369;',
        'NOK' => '&#107;&#114;',
        'NZD' => '&#36;',
        'PLN' => '&#122;&#322;',
        'GBP' => '&#163;',
        'SGD' => '&#36;',
        'SEK' => '&#107;&#114;',
        'CHF' => '&#67;&#72;&#70;',
        'THB' => '&#3647;', 
    );
    
    /**
     * @param Sabai $application
     */
    public function help(Sabai $application, $currency, $labels = false)
    {
        if (!$labels) {
            return isset($currency) ? self::$_currencies[$currency] : self::$_currencies; 
        }
        if (!isset($this->_labels)) {
            $this->_labels = array(
                'USD' => __('U.S. Dollar', 'sabai-directory'),
                'AUD' => __('Australian Dollar', 'sabai-directory'),
                'CAD' => __('Canadian Dollar', 'sabai-directory'),
                'CZK' => __('Czech Koruna', 'sabai-directory'),
                'DKK' => __('Danish Krone', 'sabai-directory'),
                'EUR' => __('Euro', 'sabai-directory'),
                'HKD' => __('Hong Kong Dollar', 'sabai-directory'),
                'HUF' => __('Hungarian Forint', 'sabai-directory'),
                'ILS' => __('Israeli New Sheqel', 'sabai-directory'),
                'JPY' => __('Japanese Yen', 'sabai-directory'),
                'MXN' => __('Mexican Peso', 'sabai-directory'),
                'NOK' => __('Norwegian Krone', 'sabai-directory'),
                'NZD' => __('New Zealand Dollar', 'sabai-directory'),
                'PLN' => __('Polish Zloty', 'sabai-directory'),
                'GBP' => __('Pound Sterling', 'sabai-directory'),
                'SGD' => __('Singapore Dollar', 'sabai-directory'),
                'SEK' => __('Swedish Krona', 'sabai-directory'),
                'CHF' => __('Swiss Franc', 'sabai-directory'),
                'THB' => __('Thailand Baht', 'sabai-directory'),
            );
            $format = __('%s (%s)', 'sabai-directory');
            foreach ($this->_labels as $code => $label) {
                $this->_labels[$code] = sprintf($format, $label, self::$_currencies[$code]);
            }
        }
        return isset($currency) ? $this->_labels[$currency] : $this->_labels;
    }
}