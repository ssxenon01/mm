<?php
class Sabai_Addon_Field_Widget_HTML extends Sabai_Addon_Field_Widget_AbstractWidget
{
    protected function _fieldWidgetGetInfo()
    {
        return array(
            'label' => __('HTML fragment', 'sabai'),
            'field_types' => array('html'),
            'default_settings' => array(
                'html' => '',
            ),
            'disable_edit_title' => true,
            'disable_edit_description' => true,
            'disable_edit_required' => true,
        );
    }

    public function fieldWidgetGetSettingsForm($fieldType, array $fieldSettings, array $settings, array $parents = array())
    {
        return array(
            'html' => array(
                '#type' => 'textarea',
                '#title' => __('HTML', 'sabai'),
                '#rows' => 10,
                '#default_value' => $settings['html'],
            ),
        );
    }

    public function fieldWidgetGetForm(Sabai_Addon_Field_IField $field, array $settings, $value = null, array $parents = array())
    {
        return array(
            '#type' => 'markup',
            '#markup' => $settings['html'],
        );
    }
    
    public function fieldWidgetGetPreview(Sabai_Addon_Field_IField $field, array $settings)
    {
        return Sabai::h($settings['html']);
    }

    public function fieldWidgetGetEditDefaultValueForm($fieldType, array $fieldSettings, array $settings, array $parents = array())
    {

    }
}