<?php
class Sabai_Addon_Field_Widget_Textfield extends Sabai_Addon_Field_Widget_AbstractWidget
{
    protected function _fieldWidgetGetInfo()
    {
        return array(
            'label' => __('Text input field', 'sabai'),
            'field_types' => array('string', 'integer', 'number'),
            'default_settings' => array(
                'size' => null,
                'field_prefix' => null,
                'field_suffix' => null,
            ),
            'repeatable' => true,
        );
    }

    public function fieldWidgetGetSettingsForm($fieldType, array $fieldSettings, array $settings, array $parents = array())
    {
        $form = array(
            'size' => array(
                '#type' => 'select',
                '#title' => __('Field size', 'sabai'),
                '#options' => array(
                    'small' => __('Small', 'sabai'),
                    'medium' => __('Medium', 'sabai'),
                    'large' => __('Large', 'sabai'),
                ),
                '#default_value' => $settings['size'],
            ),
        );
        if ($fieldType === 'string') {
            $form += array(
                'field_prefix' => array(
                    '#type' => 'textfield',
                    '#title' => __('Field prefix', 'sabai'),
                    '#description' => __('Example: $, #, -', 'sabai'),
                    '#size' => 20,
                    '#default_value' => $settings['field_prefix'],
                    '#no_trim' => true,
                ),
                'field_suffix' => array(
                    '#type' => 'textfield',
                    '#title' => __('Field suffix', 'sabai'),
                    '#description' => __('Example: km, %, g', 'sabai'),
                    '#size' => 20,
                    '#default_value' => $settings['field_suffix'],
                    '#no_trim' => true,
                ),
            );
        }
        
        return $form;
    }

    public function fieldWidgetGetForm(Sabai_Addon_Field_IField $field, array $settings, $value = null, array $parents = array())
    {
        $sizes = array('small' => 20, 'medium' => 50, 'large' => null);
        $form = array(
            '#type' => 'textfield',
            '#size' => isset($settings['size']) && isset($sizes[$settings['size']]) ? $sizes[$settings['size']] : null,
            '#default_value' => isset($value) ? $value : null,
        );
        $field_settings = $field->getFieldSettings();
        switch ($field->getFieldType()) {
            case 'string':
                $form['#min_length'] = isset($field_settings['min_length']) ? $field_settings['min_length'] : null;
                $form['#max_length'] = isset($field_settings['max_length']) ? $field_settings['max_length'] : null;
                $form['#char_validation'] = isset($field_settings['char_validation']) ? $field_settings['char_validation'] : 'none';
                $form['#regex'] = isset($field_settings['regex']) ? $field_settings['regex'] : null;
                $form['#field_prefix'] = isset($settings['field_prefix']) && strlen($settings['field_prefix']) ? $settings['field_prefix'] : null;
                $form['#field_suffix'] = isset($settings['field_suffix']) && strlen($settings['field_suffix']) ? $settings['field_suffix'] : null;
                break;
            case 'integer':
            case 'number':
                $form['#field_prefix'] = isset($field_settings['prefix']) && strlen($field_settings['prefix']) ? $field_settings['prefix'] : null;
                $form['#field_suffix'] = isset($field_settings['suffix']) && strlen($field_settings['suffix']) ? $field_settings['suffix'] : null;
                $form['#min_value'] = isset($field_settings['min']) && strlen($field_settings['min']) ? $field_settings['min'] : null;
                $form['#max_value'] = isset($field_settings['max']) && strlen($field_settings['max']) ? $field_settings['max'] : null;
                if ($field->getFieldType() === 'integer') {
                    $form['#integer'] = true;
                    $form['#min_value'] = isset($field_settings['min']) ? intval($field_settings['min']) : null;
                    $form['#max_value'] = isset($field_settings['max']) ? intval($field_settings['max']) : null;
                } else {
                    $form['#numeric'] = true;
                    $form['#min_value'] = isset($field_settings['min']) && is_numeric($field_settings['min']) ? $field_settings['min'] : null;
                    $form['#max_value'] = isset($field_settings['max']) && is_numeric($field_settings['max']) ? $field_settings['max'] : null;
                }
                if (!isset($form['#size'])) {
                    $form['#size'] = 20;
                }
        }

        return $form;
    }
    
    public function fieldWidgetGetPreview(Sabai_Addon_Field_IField $field, array $settings)
    {
        $field_settings = $field->getFieldSettings();
        switch ($field->getFieldType()) {
            case 'string':
                $prefix = isset($settings['field_prefix']) && strlen($settings['field_prefix']) ? sprintf('<span class="sabai-form-field-prefix">%s</span>', $settings['field_prefix']) : '';
                $suffix = isset($settings['field_suffix']) && strlen($settings['field_suffix']) ? sprintf('<span class="sabai-form-field-suffix">%s</span>', $settings['field_suffix']) : '';
                break;
            case 'number':
			case 'integer':
                $prefix = isset($field_settings['prefix']) && strlen($field_settings['prefix']) ? sprintf('<span class="sabai-form-field-prefix">%s</span>', $field_settings['prefix']) : '';
                $suffix = isset($field_settings['suffix']) && strlen($field_settings['suffix']) ? sprintf('<span class="sabai-form-field-suffix">%s</span>', $field_settings['suffix']) : '';
            default:
                $prefix = $suffix = '';
		}

        $sizes = array('small' => 20, 'medium' => 50, 'large' => null);
        $size = isset($settings['size']) && isset($sizes[$settings['size']]) ? sprintf(' size="%d"', $sizes[$settings['size']]) : '';
        if ($size === '') {
            if ($prefix || $suffix) {
                $size = ' style="width:80%;"';
            } else {
                $size = ' style="width:100%;"';
            }
        }
        $default_value = $field->getFieldDefaultValue();
        return sprintf('%s<input type="text" value="%s" disabled="disabled"%s />%s', $prefix, isset($default_value) ? Sabai::h($default_value[0]) : '', $size, $suffix);
    }

    public function fieldWidgetGetEditDefaultValueForm($fieldType, array $fieldSettings, array $settings, array $parents = array())
    {
        $sizes = array('small' => 20, 'medium' => 50, 'large' => null);
        return array(
            '#type' => 'textfield',
            '#size' => isset($settings['size']) && isset($sizes[$settings['size']]) ? $sizes[$settings['size']] : null,
        );
    }
}