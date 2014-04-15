<?php
class Sabai_Addon_Date extends Sabai_Addon
    implements Sabai_Addon_Form_IFields,
               Sabai_Addon_Field_ITypes,
               Sabai_Addon_Field_IWidgets
{
    const VERSION = '1.2.18', PACKAGE = 'sabai';
                
    public function isUninstallable($currentVersion)
    {
        return false;
    }

    public function formGetFieldTypes()
    {
        return array('date_datepicker');
    }

    public function formGetField($type)
    {
        return new Sabai_Addon_Date_DatePickerFormField($this);
    }

    public function fieldGetTypeNames()
    {
        return array('date_timestamp');
    }

    public function fieldGetType($name)
    {
        return new Sabai_Addon_Date_TimestampFieldType($this);
    }

    public function fieldGetWidgetNames()
    {
        return array('date_datepicker');
    }

    public function fieldGetWidget($name)
    {
        return new Sabai_Addon_Date_DatePickerFieldWidget($this);
    }
}