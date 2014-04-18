<?php
class Sabai_Addon_HTML extends Sabai_Addon
    implements Sabai_Addon_Form_IFields,
               Sabai_Addon_Field_ITypes,
               Sabai_Addon_Field_IWidgets
{
    const VERSION = '1.2.29', PACKAGE = 'sabai';
                
    public function isUninstallable($currentVersion)
    {
        return false;
    }

    /* Start implementation of Sabai_Addon_Field_ITypes */

    public function fieldGetTypeNames()
    {
        return array();
    }

    public function fieldGetType($typeName)
    {

    }

    /* End implementation of Sabai_Addon_Field_ITypes */

    /* Start implementation of Sabai_Addon_Field_IWidgets */

    public function fieldGetWidgetNames()
    {
        return array();
    }

    public function fieldGetWidget($widgetName)
    {

    }

    /* End implementation of Sabai_Addon_Field_IWidgets */

    /* Start implementation of Sabai_Addon_Form_IFields */

    public function formGetFieldTypes()
    {
        return array();
    }

    public function formGetField($type)
    {
        
    }

    /* End implementation of Sabai_Addon_Form_IFields */

    public function hasVarDir()
    {
        return true;
    }
}
