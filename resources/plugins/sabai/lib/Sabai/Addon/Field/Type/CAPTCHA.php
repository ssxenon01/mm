<?php
class Sabai_Addon_Field_Type_CAPTCHA extends Sabai_Addon_Field_Type_AbstractType
{
    protected function _fieldTypeGetInfo()
    {
        return array(
            'label' => __('CAPTCHA', 'sabai'),
            'cacheable' => false,
        );
    }

    public function fieldTypeGetSettingsForm(array $settings, array $parents = array())
    {
        return array();
    }

    public function fieldTypeGetSchema(array $settings)
    {
        return array();
    }
}