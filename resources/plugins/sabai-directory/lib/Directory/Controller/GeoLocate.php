<?php
require_once dirname(__FILE__) . '/AllListings.php';
class Sabai_Addon_Directory_Controller_GeoLocate extends Sabai_Addon_Directory_Controller_AllListings
{ 
    protected function _doExecute(Sabai_Context $context)
    {
        $context->template = 'directory_listings_geolocate';
        $context->country = $this->getAddon()->getConfig('search', 'country');
        parent::_doExecute($context);
    }
    
    protected function _getUrlParams(Sabai_Context $context, Sabai_Addon_Entity_Model_Bundle $bundle = null)
    {
        $params = parent::_getUrlParams($context, $bundle) + array(
            'sort' => $this->_settings['sort'],
            'view' => $this->_settings['view'],
            'category' => isset($context->category) ? $context->category : '',
        );
        // We do not want perform geolocation search again, so do not include the template parameter 
        unset($params['template']);
        return $params;
    }
}
