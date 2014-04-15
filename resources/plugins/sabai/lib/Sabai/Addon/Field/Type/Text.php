<?php
class Sabai_Addon_Field_Type_Text extends Sabai_Addon_Field_Type_String
{
    protected function _fieldTypeGetInfo()
    {
        return array(
            'label' => __('Paragraph Text', 'sabai'),
            'default_widget' => 'textarea',
            'default_settings' => array(
                'no_trim' => false,
                'char_validation' => 'none',
            ),
        );
    }

    public function fieldTypeGetSettingsForm(array $settings, array $parents = array())
    {
        $form = parent::fieldTypeGetSettingsForm($settings, $parents);
        $form += array(
            'no_trim' => array(
                '#type' => 'checkbox',
                '#title' => __('Do not remove leading and trailing white spaces', 'sabai'),
                '#description' => __('Leading and trailing white spaces are removed from the value the user entered. Check this option if you do not want this to happen automatically.', 'sabai'),
                '#default_value' => !empty($settings['no_trim']),
            ),
        );
        return $form;
    }

    public function fieldTypeGetSchema(array $settings)
    {
        return array(
            'columns' => array(
                'value' => array(
                    'type' => Sabai_Addon_Field::COLUMN_TYPE_TEXT,
                    'notnull' => true,
                    'was' => 'value',
                ),
            ),
        );
    }
}