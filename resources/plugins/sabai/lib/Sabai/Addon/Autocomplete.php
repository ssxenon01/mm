<?php
class Sabai_Addon_Autocomplete extends Sabai_Addon
    implements Sabai_Addon_Form_IFields
{
    const VERSION = '1.2.18', PACKAGE = 'sabai';
            
    public function isUninstallable($currentVersion)
    {
        return false;
    }

    /* Start implementation of Sabai_Addon_Form_IFields */

    public function formGetFieldTypes()
    {
        return array('autocomplete_default', 'autocomplete_user');
    }

    public function formGetField($type)
    {
        switch ($type) {
            case 'autocomplete_user':
                return new Sabai_Addon_Autocomplete_UserFormField($this, $type);
            default:
                return new Sabai_Addon_Autocomplete_FormField($this, $type);
        }
    }

    /* End implementation of Sabai_Addon_Form_IFields */
}