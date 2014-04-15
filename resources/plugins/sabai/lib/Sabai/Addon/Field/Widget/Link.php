<?php
class Sabai_Addon_Field_Widget_Link extends Sabai_Addon_Field_Widget_AbstractWidget
{
    protected function _fieldWidgetGetInfo()
    {
        return array(
            'label' => __('Link field', 'sabai'),
            'field_types' => array('link'),
            'default_settings' => array(
                'size' => null,
            ),
            'repeatable' => array('group_fields' => false),
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
        
        return $form;
    }

    public function fieldWidgetGetForm(Sabai_Addon_Field_IField $field, array $settings, $value = null, array $parents = array())
    {
        $sizes = array('small' => 20, 'medium' => 50, 'large' => null);
        $form = array(
            '#type' => 'fieldset',
            '#class' => 'sabai-form-group',
            'url' => array(
                '#type' => 'textfield',
                '#size' => isset($settings['size']) && isset($sizes[$settings['size']]) ? $sizes[$settings['size']] : null,
                '#default_value' => isset($value['url']) ? $value['url'] : null,
                '#char_validation' => 'url',
                '#attributes' => array('placeholder' => 'http://'),
                '#weight' => 1,
            ),
            'title' => array(
                '#field_prefix' => __('Link Title:', 'sabai'),
                '#type' => 'textfield',
                '#size' => isset($settings['size']) && isset($sizes[$settings['size']]) ? $sizes[$settings['size']] : null,
                '#default_value' => isset($value['title']) ? $value['title'] : null,
                '#weight' => 3,
                '#required' => false,
            ),
        );

        return $form;
    }
    
    public function fieldWidgetGetPreview(Sabai_Addon_Field_IField $field, array $settings)
    {
        $sizes = array('small' => 20, 'medium' => 50, 'large' => null);
        $required_label = '<span class="sabai-fieldui-widget-required">*</span>';
        return sprintf(
            '<div>
    <div><input type="textfield" disabled="disabled" %2$s placeholder="http://" /></div>
</div>
<div>
    <div><span class="sabai-form-field-prefix">%1$s</span><input type="textfield" disabled="disabled" %2$s /></div>
</div>',
            __('Link Title:', 'sabai'),
            isset($settings['size']) && isset($sizes[$settings['size']]) ? sprintf('size="%d"', $sizes[$settings['size']]) : 'style="width:100%;"'
        );
    }

    public function fieldWidgetGetEditDefaultValueForm($fieldType, array $fieldSettings, array $settings, array $parents = array())
    {

    }
}