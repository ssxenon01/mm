<?php
class Sabai_Addon_Markdown_FieldType implements Sabai_Addon_Field_IType
{
    private $_addon, $_name;

    public function __construct(Sabai_Addon_Markdown $addon, $name)
    {
        $this->_addon = $addon;
        $this->_name = $name;
    }

    public function fieldTypeGetInfo($key = null)
    {
        switch ($this->_name) {
            case 'markdown_text';
                $info = array(
                    'label' => __('Markdown Text', 'sabai'),
                    'default_widget' => 'markdown_textarea',
                );
                break;
            default:
                return;
        }

        return isset($key) ? @$info[$key] : $info;
    }

    public function fieldTypeGetSettingsForm(array $settings, array $parents = array())
    {
        return array();
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
                'filtered_value' => array(
                    'type' => Sabai_Addon_Field::COLUMN_TYPE_TEXT,
                    'notnull' => true,
                    'was' => 'filtered_value',
                ),
            ),
        );
    }

    public function fieldTypeOnSave(Sabai_Addon_Field_IField $field, array $values)
    {
        $ret = array();
        foreach ($values as $value) {
            if (!is_array($value) || strlen((string)@$value['text']) === 0) continue;

            $ret[] = array(
                'value' => $value['text'],
                'filtered_value' => $value['filtered_text'],
            );
        }

        return $ret;
    }

    public function fieldTypeOnLoad(Sabai_Addon_Field_IField $field, array &$values)
    {
        foreach ($values as $key => $value) {
            $values[$key] = array('value' => $value['value'], 'filtered_value' => $value['filtered_value']);
        }
    }
    
    public function fieldTypeIsModified($field, $valueToSave, $currentLoadedValue)
    {
        return $currentLoadedValue !== $valueToSave;
    }
}