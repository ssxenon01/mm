<?php
class Sabai_Addon_Date_DatePickerFieldWidget implements Sabai_Addon_Field_IWidget
{
    private $_addon, $_info;

    public function __construct(Sabai_Addon_Date $addon)
    {
        $this->_addon = $addon;
    }

    public function fieldWidgetGetInfo($key = null)
    {
        if (!isset($this->_info)) {
            $this->_info = array(
                'label' => __('Date/Time picker', 'sabai'),
                'field_types' => array('date_timestamp'),
                'default_settings' => array(
                    'current_date_selected' => true,
                ),
                'is_fieldset' => true,
            );
        }

        return isset($key) ? @$this->_info[$key] : $this->_info;
    }

    public function fieldWidgetGetSettingsForm($fieldType, array $fieldSettings, array $settings, array $parents = array())
    {
        return array(
            'current_date_selected' => array(
                '#type' => 'checkbox',
                '#title' => __('Set current date selected by default', 'sabai'),
                '#description' => __('Check this option to set the current date (and time if time is eabled) selected by default if the field has not been selected yet.', 'sabai'),
                '#default_value' => !empty($settings['current_date_selected']),
            ),
        );
    }

    public function fieldWidgetGetForm(Sabai_Addon_Field_IField $field, array $settings, $value = null, array $parents = array(), $admin = false)
    {
        $field_settings = $field->getFieldSettings();
        return array(
            '#type' => 'date_datepicker',
            '#current_date_selected' => !empty($settings['current_date_selected']),
            '#min_date' => !empty($field_settings['date_range']) ? $field_settings['date_range_min'] : null,
            '#max_date' => !empty($field_settings['date_range']) ? $field_settings['date_range_max'] : null,
            '#disable_time' => empty($field_settings['enable_time']),
            '#default_value' => $value,
        );
    }
    
    public function fieldWidgetGetPreview(Sabai_Addon_Field_IField $field, array $settings)
    {
        $date = $time = '';
        if ($settings['current_date_selected']) {
             $date = date('Y/m/d', time());
             $time = date('H:i', time());
        }
        $field_settings = $field->getFieldSettings();
        if (empty($field_settings['enable_time'])) {
            return sprintf('<input type="text" disabled="disabled" size="10" value="%s" />', $date);
        }
        return sprintf('<input type="text" disabled="disabled" size="10" value="%s" /><input type="text" disabled="disabled" size="5" placeholder="HH:MM" value="%s" />', $date, $time);
    }

    public function fieldWidgetGetEditDefaultValueForm($fieldType, array $fieldSettings, array $settings, array $parents = array())
    {

    }
}
