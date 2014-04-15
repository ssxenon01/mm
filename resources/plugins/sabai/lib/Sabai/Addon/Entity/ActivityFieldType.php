<?php
abstract class Sabai_Addon_Entity_ActivityFieldType implements Sabai_Addon_Field_IType
{
    protected $_addon, $_entityType, $_info;

    public function __construct(Sabai_Addon $addon, $entityType)
    {
        $this->_addon = $addon;
        $this->_entityType = $entityType;
    }

    public function fieldTypeGetInfo($key = null)
    {
        if (!isset($this->_info)) {
            $this->_info = array(
                'label' => 'Activity',
                'entity_types' => array($this->_entityType),
                'creatable' => false,
            );
        }
        return isset($key) ? @$this->_info[$key] : $this->_info;
    }

    public function fieldTypeGetSettingsForm(array $settings, array $parents = array())
    {

    }

    public function fieldTypeGetSchema(array $settings)
    {
        return array(
            'columns' => array(
                'active_at' => array(
                    'type' => Sabai_Addon_Field::COLUMN_TYPE_INTEGER,
                    'notnull' => true,
                    'unsigned' => true,
                    'was' => 'active_at',
                    'default' => 0,
                ),
                'edited_at' => array(
                    'type' => Sabai_Addon_Field::COLUMN_TYPE_INTEGER,
                    'notnull' => true,
                    'unsigned' => true,
                    'was' => 'edited_at',
                    'default' => 0,
                ),
            ),
            'indexes' => array(
                'active_at' => array(
                    'fields' => array('active_at' => array('sorting' => 'ascending')),
                    'was' => 'active_at',
                ),
                'edited_at' => array(
                    'fields' => array('edited_at' => array('sorting' => 'ascending')),
                    'was' => 'edited_at',
                ),
            ),
        );
    }

    public function fieldTypeOnSave(Sabai_Addon_Field_IField $field, array $values, array $currentValues = null)
    {
        $ret = array();
        foreach ($values as $value) {
            if (!is_array($value)) {
                $ret[] = false; // delete
            } else {
                $ret[] = $value;
            }
        }
        return $ret;
    }

    public function fieldTypeOnLoad(Sabai_Addon_Field_IField $field, array &$values)
    {

    }
    
    public function fieldTypeIsModified($field, $valueToSave, $currentLoadedValue)
    {
        return $valueToSave !== $currentLoadedValue;
    }
}