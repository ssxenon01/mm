<?php
class Sabai_Addon_Field_Type_String extends Sabai_Addon_Field_Type_AbstractType
{
    protected function _fieldTypeGetInfo()
    {
        return array(
            'label' => __('Single Line Text', 'sabai'),
            'default_widget' => 'textfield',
            'default_settings' => array(
                'min_length' => null,
                'max_length' => null,
                'char_validation' => 'none',
            ),
        );
    }

    public function fieldTypeGetSettingsForm(array $settings, array $parents = array())
    {
        return array(
            'min_length' => array(
                '#type' => 'textfield',
                '#title' => __('Minimum length', 'sabai'),
                '#description' => __('The minimum length of value in characters.', 'sabai'),
                '#size' => 5,
                '#numeric' => true,
                '#default_value' => $settings['min_length'],
            ),
            'max_length' => array(
                '#type' => 'textfield',
                '#title' => __('Maximum length', 'sabai'),
                '#description' => __('The maximum length of value in characters.', 'sabai'),
                '#size' => 5,
                '#numeric' => true,
                '#default_value' => $settings['max_length'],
            ),
            'char_validation' => array(
                '#type' => 'select',
                '#title' => __('Character validation', 'sabai'),
                '#options' => array(
                    'integer' => __('Allow only integer numbers', 'sabai'),
                    'alpha' => __('Allow only alphabetic characters', 'sabai'),
                    'alnum' => __('Allow only alphanumeric characters', 'sabai'),
                    'lower' => __('Allow only lowercase characters', 'sabai'),
                    'upper' => __('Allow only uppercase characters', 'sabai'),
                    'url' => __('Must be a valid URL', 'sabai'),
                    'email' => __('Must be a valid e-mail address', 'sabai'),
                    'regex' => __('Must match a regular expression', 'sabai'),
                    'none' => __('No validation', 'sabai'),
                ),
                '#default_value' => $settings['char_validation'],
            ),
            'regex' => array(
                '#type' => 'textfield',
                '#title' => __('Regular Expression', 'sabai'),
                '#description' => __('Example: /^[0-9a-z]+$/i', 'sabai'),
                '#default_value' => $settings['regex'],
                '#states' => array(
                    'visible' => array(
                        sprintf('select[name="%s[char_validation]"]', $this->_addon->getApplication()->Form_FieldName($parents)) => array(
                            'type' => 'value',
                            'value' => 'regex',
                        ),
                    ),
                ),
                '#required' => array(array($this, 'isRegexRequired'), array($parents)),
                '#size' => 20,
            ),
        );
    }
    
    public function isRegexRequired($form, $parents)
    {
        $values = $form->getValue($parents);
        return $values['char_validation'] === 'regex';
    }

    public function fieldTypeGetSchema(array $settings)
    {
        return array(
            'columns' => array(
                'value' => array(
                    'type' => Sabai_Addon_Field::COLUMN_TYPE_VARCHAR,
                    'length' => 255,
                    'notnull' => true,
                    'was' => 'value',
                    'default' => '',
                ),
            ),
            'indexes' => array(
                'value' => array(
                    'fields' => array('value' => array('sorting' => 'ascending')),
                    'was' => 'value',
                ),
            ),
        );
    }
}