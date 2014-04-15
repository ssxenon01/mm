<?php
class Sabai_Addon_Field_Widget_Textarea extends Sabai_Addon_Field_Widget_AbstractWidget
{
    protected function _fieldWidgetGetInfo()
    {
        return array(
            'label' => __('Textarea field', 'sabai'),
            'field_types' => array('text'),
            'default_settings' => array(
                'rows' => 5,
            ),
            'repeatable' => true,
        );
    }

    public function fieldWidgetGetSettingsForm($fieldType, array $fieldSettings, array $settings, array $parents = array())
    {
        return array(
            'rows' => array(
                '#type' => 'textfield',
                '#title' => __('Rows', 'sabai'),
                '#size' => 5,
                '#integer' => true,
                '#default_value' => $settings['rows'],
            ),
        );
    }

    public function fieldWidgetGetForm(Sabai_Addon_Field_IField $field, array $settings, $value = null, array $parents = array())
    {
        return array(
            '#type' => 'textarea',
            '#rows' => $settings['rows'],
            '#default_value' => isset($value) ? $value : null,
        );
    }
    
    public function fieldWidgetGetPreview(Sabai_Addon_Field_IField $field, array $settings)
    {
        $default_value = $field->getFieldDefaultValue();
        return sprintf('<textarea rows="%d" style="width:100%%;" disabled="disabled">%s</textarea>', $settings['rows'], isset($default_value) ? Sabai::h($default_value[0]) : '');
    }

    public function fieldWidgetGetEditDefaultValueForm($fieldType, array $fieldSettings, array $settings, array $parents = array())
    {
        return array(
            '#type' => 'textarea',
            '#rows' => $settings['rows'] > 5 ? 5 : $settings['rows'],
        );
    }
}