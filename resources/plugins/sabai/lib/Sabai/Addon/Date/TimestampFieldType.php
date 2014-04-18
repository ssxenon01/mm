<?php
class Sabai_Addon_Date_TimestampFieldType implements Sabai_Addon_Field_IType
{
    private $_addon, $_info;

    public function __construct(Sabai_Addon_Date $addon)
    {
        $this->_addon = $addon;
    }

    public function fieldTypeGetInfo($key = null)
    {
        if (!isset($this->_info)) {
            $this->_info = array(
                'label' => __('Date', 'sabai'),
                'default_widget' => 'date_datepicker',
                'default_settings' => array(
                    'date_range' => false,
                    'date_range_min' => null,
                    'date_range_max' => null,
                    'enable_time' => true,
                ),
            );
        }

        return isset($key) ? @$this->_info[$key] : $this->_info;
    }

    public function fieldTypeGetSettingsForm(array $settings, array $parents = array())
    {
        return array(
            '#element_validate' => array(array($this, 'validateSettings')),
            'enable_time' => array(
                '#type' => 'checkbox',
                '#title' => __('Enable time (hour and minute)', 'sabai'),
                '#description' => __('Check this option to allow the user to enter the time (hour and minute) as well as the date.', 'sabai'),
                '#default_value' => !empty($settings['enable_time']),
            ),
            /*'require_time' => array(
                '#type' => 'checkbox',
                '#title' => __('Required', 'sabai'),
                '#default_value' => !empty($settings['require_time']),
                '#states' => array(
                    'visible' => array(
                        sprintf('input[name="%s[enable_time][]"]', $this->_addon->getApplication()->Form_FieldName($parents)) => array(
                            'type' => 'checked',
                            'value' => true,
                        ),
                    ),
                ),
            ),*/
            'date_range' => array(
                '#type' => 'checkbox',
                '#title' => __('Restrict dates', 'sabai'),
                '#description' => __('Check this option to set the range of allowed dates for this field.', 'sabai'),
                '#default_value' => !empty($settings['date_range']),
            ),
            'date_range_min' => array(
                '#type' => 'date_datepicker',
                '#field_prefix' => __('Minimum date:', 'sabai'),
                '#default_value' => $settings['date_range_min'],
                '#states' => array(
                    'visible' => array(
                        sprintf('input[name="%s[date_range][]"]', $this->_addon->getApplication()->Form_FieldName($parents)) => array(
                            'type' => 'checked',
                            'value' => true,
                        ),
                    ),
                ),
            ),
            'date_range_max' => array(
                '#type' => 'date_datepicker',
                '#field_prefix' => __('Maximum date:', 'sabai'),
                '#default_value' => $settings['date_range_max'],
                '#states' => array(
                    'visible' => array(
                        sprintf('input[name="%s[date_range][]"]', $this->_addon->getApplication()->Form_FieldName($parents)) => array(
                            'type' => 'checked',
                            'value' => true,
                        ),
                    ),
                ),
            ),
        );
    }

    public function fieldTypeGetSchema(array $settings)
    {
        return array(
            'columns' => array(
                'value' => array(
                    'type' => Sabai_Addon_Field::COLUMN_TYPE_INTEGER,
                    'notnull' => true,
                    'unsigned' => false,
                    'length' => 20,
                    'default' => 0,
                ),
            ),
            'indexes' => array(
                'value' => array(
                    'fields' => array('value' => array('sorting' => 'ascending')),
                ),
            ),
        );
    }
    
    public function fieldTypeOnSave(Sabai_Addon_Field_IField $field, array $values)
    {
        $ret = array();
        foreach ($values as $weight => $value) {
            if (!is_numeric($value)
                && (!$value = strtotime($value))
            ) {
                continue;
            } else {
                $value = intval($value);
            }
            $ret[]['value'] = $value;
        }

        return $ret;
    }

    public function fieldTypeOnLoad(Sabai_Addon_Field_IField $field, array &$values)
    {
        foreach ($values as $key => $value) {
            $values[$key] = $value['value'];
        }
    }
    
    public function fieldTypeIsModified($field, $valueToSave, $currentLoadedValue)
    {   
        $new = array();
        foreach ($valueToSave as $value) {
            $new[] = $value['value'];
        }
        return $currentLoadedValue !== $new;
    }

    public function validateSettings($form, &$value, $element)
    {
        if (empty($value['date_range'])) return;
        
        if (!empty($value['date_range_min']) && !empty($value['date_range_max'])) {
            if ($value['date_range_min'] >= $value['date_range_max']) {
                $form->setError(__('The date must be later than the minimum date.', 'sabai'), $element['#name'] . '[date_range_max]');
            }
        }
    }
}