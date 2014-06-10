<?php
class Sabai_Addon_GoogleMaps extends Sabai_Addon
    implements Sabai_Addon_Form_IFields,
               Sabai_Addon_Field_ITypes,
               Sabai_Addon_Field_IWidgets
{
    const VERSION = '1.2.31', PACKAGE = 'sabai-directory';
    
    /* Start implementation of Sabai_Addon_Form_IFields */

    public function formGetFieldTypes()
    {
        return array('googlemaps_marker');
    }

    public function formGetField($name)
    {
        switch ($name) {
            case 'googlemaps_marker':
                require_once dirname(__FILE__) . '/GoogleMaps/MarkerFormField.php';
                return new Sabai_Addon_GoogleMaps_MarkerFormField($this);
        }
    }

    /* End implementation of Sabai_Addon_Form_IFields */

    /* Start implementation of Sabai_Addon_Field_ITypes */

    public function fieldGetTypeNames()
    {
        return array('googlemaps_marker');
    }

    public function fieldGetType($name)
    {
        switch ($name) {
            case 'googlemaps_marker':
                require_once dirname(__FILE__) . '/GoogleMaps/MarkerFieldType.php';
                return new Sabai_Addon_GoogleMaps_MarkerFieldType($this);
        }
    }

    /* End implementation of Sabai_Addon_Field_ITypes */

    /* Start implementation of Sabai_Addon_Field_IWidgets */

    public function fieldGetWidgetNames()
    {
        return array('googlemaps_marker');
    }

    public function fieldGetWidget($name)
    {
        switch ($name) {
            case 'googlemaps_marker':
                require_once dirname(__FILE__) . '/GoogleMaps/MarkerFieldWidget.php';
                return new Sabai_Addon_GoogleMaps_MarkerFieldWidget($this);
        }
    }

    /* End implementation of Sabai_Addon_Field_IWidgets */
    
    public function onSabaiWebResponseRenderHtmlLayout(Sabai_Context $context, Sabai_WebResponse $response, &$content)
    { 
        // Add Google loader
        // We do not use the helper here so as to ensure the google loader script is loaded in the header
        $response->addJsFile('https://www.google.com/jsapi', 'google-loader');
    }
    
    public function onGoogleMapsUpgradeSuccess(Sabai_Addon $addon, $log, $previousVersion)
    {
        if (version_compare($previousVersion, '1.2.7', '<')) {            
            $db = $this->_application->getDB();
            $db->begin();
            $sql = sprintf('DELETE FROM %sgooglemaps_geocode', $db->getResourcePrefix());
            $db->exec($sql);
            $db->commit();
        }
    }
}
