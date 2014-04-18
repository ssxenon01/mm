<?php
class Sabai_Addon_Comment_FieldType implements Sabai_Addon_Field_IType
{
    private $_addon, $_name, $_info;

    public function __construct(Sabai_Addon_Comment $addon, $name)
    {
        $this->_addon = $addon;
        $this->_name = $name;
    }

    public function fieldTypeGetInfo($key = null)
    {
        if (!isset($this->_info)) {
            $this->_info = array(
                'label' => __('Comments', 'sabai'),
                'entity_types' => array('content'),
                'creatable' => false,
            );
        }

        return isset($key) ? @$this->_info[$key] : $this->_info;
    }

    public function fieldTypeGetSettingsForm(array $settings, array $parents = array())
    {
        return array();
    }

    public function fieldTypeGetSchema(array $settings)
    {
        return array();
    }

    public function fieldTypeOnSave(Sabai_Addon_Field_IField $field, array $values)
    {
        return array();
    }

    public function fieldTypeOnLoad(Sabai_Addon_Field_IField $field, array &$values)
    {

    }
}