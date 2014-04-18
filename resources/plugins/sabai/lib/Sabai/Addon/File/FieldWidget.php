<?php
class Sabai_Addon_File_FieldWidget implements Sabai_Addon_Field_IWidget
{
    private $_addon, $_name;

    public function __construct(Sabai_Addon_File $addon, $name)
    {
        $this->_addon = $addon;
        $this->_name = $name;
    }

    public function fieldWidgetGetInfo($key = null)
    {
        $info = array(
            'label' => __('File upload field', 'sabai'),
            'field_types' => array('file_file', 'file_image'),
            'accept_multiple' => true,
            'default_settings' => array(
                'max_file_size' => 1024,
                'allowed_extensions' => array('txt', 'pdf', 'zip'),
                'thumbnail' => true,
                'large_image' => true,
                'medium_image' => true,
            ),
            'is_fieldset' => true,
        );

        return isset($key) ? @$info[$key] : $info;
    }

    public function fieldWidgetGetSettingsForm($fieldType, array $fieldSettings, array $settings, array $parents = array())
    {
        $form = array(
            'max_file_size' => array(
                '#type' => 'textfield',
                '#title' => __('Maximum upload size', 'sabai'),
                '#description' => __('The maximum file size of uploaded files in kilobytes. Leave this field blank for no limit.', 'sabai'),
                '#size' => 7,
                '#integer' => true,
                '#field_suffix' => 'KB',
                '#default_value' => $settings['max_file_size'],
                '#weight' => 2,
            ),
        );

        if ($fieldType === 'file_file') {
            $form['allowed_extensions'] = array(
                '#type' => 'textfield',
                '#separator' => ',',
                '#title' => __('Allowed file extensions', 'sabai'),
                '#description' => __('Enter a list of allowed file extensions separated by commas (i.e. jpg, gif, png, pdf).', 'sabai'),
                '#default_value' => $settings['allowed_extensions'],
                '#regex' => '/^[a-z0-9\.]+$/',
                '#required' => true,
                '#weight' => 1,
            );
        }

        return $form;
    }

    public function fieldWidgetGetForm(Sabai_Addon_Field_IField $field, array $settings, $value = null, array $parents = array())
    {
        $default_value = array();
        if (isset($value)) {
            foreach ($value as $_value) {
                $default_value[] = $_value['id'];
            }
        }
        return array(
            '#type' => 'file_upload',
            '#title' => $field->getFieldTitle(),
            '#allowed_extensions' => $settings['allowed_extensions'],
            '#max_file_size' => $settings['max_file_size'],
            '#multiple' => $field->getFieldMaxNumItems() !== 1,
            '#allow_only_images' => $field->getFieldType() === 'file_image',
            '#default_value' => !empty($default_value) ? $default_value : null,
            '#max_num_files' => $field->getFieldMaxNumItems(),
            '#thumbnail' => !isset($settings['thumbnail']) || !empty($settings['thumbnail']),
            '#thumbnail_width' => empty($settings['thumbnail_width']) ? null : $settings['thumbnail_width'],
            '#medium_image' => !isset($settings['medium_image']) || !empty($settings['medium_image']),
            '#medium_image_width' => empty($settings['medium_image_width']) ? null : $settings['medium_image_width'],
            '#large_image' => !isset($settings['large_image']) || !empty($settings['large_image']),
            '#large_image_width' => empty($settings['large_image_width']) ? null : $settings['large_image_width'],
        );
    }
    
    public function fieldWidgetGetPreview(Sabai_Addon_Field_IField $field, array $settings)
    {
        return '<input type="file" disabled="disabled" />';
    }

    public function fieldWidgetGetEditDefaultValueForm($fieldType, array $fieldSettings, array $settings, array $parents = array())
    {

    }
}
