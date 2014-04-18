<?php
class Sabai_Addon_Field_Type_Link extends Sabai_Addon_Field_Type_AbstractType
{
    protected function _fieldTypeGetInfo()
    {
        return array(
            'label' => __('Link', 'sabai'),
            'default_widget' => 'link',
            'default_settings' => array(
                'target' => '_blank',
                'nofollow' => true,
                'title' => array('default' => null, 'no_custom' => false),
            ),
        );
    }

    public function fieldTypeGetSettingsForm(array $settings, array $parents = array())
    {
        return array(
            'target' => array(
                '#type' => 'radios',
                '#options' => array(
                    '_self' => __('Open in the same window', 'sabai'),
                    '_blank' => __('Open in a new window', 'sabai'),
                ),
                '#default_value' => $settings['target'],
            ),
            'nofollow' => array(
                '#type' => 'checkbox',
                '#title' => __('Add rel="nofollow"', 'sabai'),
                '#default_value' => $settings['nofollow'],
            ),
            'title' => array(
                '#class' => 'sabai-form-group',
                '#title' => __('Default link title', 'sabai'),
                '#collapsible' => false,
                'default' => array(
                    '#type' => 'textfield',
                    '#default_value' => @$settings['title']['default'],
                ),
                'no_custom' => array(
                    '#type' => 'checkbox',
                    '#title' => __('Do not allow custom link title', 'sabai'),
                    '#default_value' => @$settings['title']['no_custom'],
                    '#states' => array(
                        'visible' => array(
                            sprintf('input[name="%s[title][default]"]', $this->_addon->getApplication()->Form_FieldName($parents)) => array(
                                'type' => 'filled',
                                'value' => true,
                            ),
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
                'url' => array(
                    'type' => Sabai_Addon_Field::COLUMN_TYPE_VARCHAR,
                    'notnull' => true,
                    'was' => 'url',
                    'length' => 400,
                ),
                'title' => array(
                    'type' => Sabai_Addon_Field::COLUMN_TYPE_VARCHAR,
                    'length' => 255,
                    'notnull' => true,
                    'was' => 'title',
                ),
                'target' => array(
                    'type' => Sabai_Addon_Field::COLUMN_TYPE_VARCHAR,
                    'length' => 10,
                    'notnull' => true,
                    'was' => 'target',
                ),
            ),
            'indexes' => array(
                'url' => array(
                    'fields' => array('url' => array('sorting' => 'ascending', 'length' => 50)),
                    'was' => 'url',
                ),
                'title' => array(
                    'fields' => array('title' => array('sorting' => 'ascending', 'length' => 50)),
                    'was' => 'title',
                ),
            ),
        );
    }
    
    public function fieldTypeOnLoad(Sabai_Addon_Field_IField $field, array &$values)
    {
        foreach ($values as $key => $value) {
            $values[$key] = $value;
        }
    }
    
    public function fieldTypeOnSave(Sabai_Addon_Field_IField $field, array $values)
    {
        $ret = array();
        foreach ($values as $weight => $value) {
            if (!is_array($value) || !is_string($value['url']) || strlen($value['url']) === 0 || $value['url'] === 'http://') continue;

            $ret[] = $value + array('title' => '', 'target' => '');
        }

        return $ret;
    }
}
