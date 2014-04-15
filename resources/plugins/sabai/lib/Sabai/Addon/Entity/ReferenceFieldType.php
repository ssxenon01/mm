<?php
abstract class Sabai_Addon_Entity_ReferenceFieldType implements Sabai_Addon_Field_IType
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
                'label' => 'Entity Reference',
                'entity_types' => array($this->_entityType),
                'creatable' => false,
                'editable' => false,
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
                'value' => array(
                    'type' => Sabai_Addon_Field::COLUMN_TYPE_INTEGER,
                    'notnull' => true,
                    'unsigned' => true,
                    'was' => 'value',
                    'default' => 0,
                ),
            ),
        );
    }

    public function fieldTypeOnSave(Sabai_Addon_Field_IField $field, array $values, array $currentValues = null)
    {
        $ret = $entity_ids = array();
        foreach ($values as $weight => $value) {
            if (is_array($value)) {  // autocomplete field widget
                foreach ($value as $entity_id) {
                    if (empty($entity_id)) {
                        continue;
                    }
                    $entity_ids[$entity_id] = $entity_id;
                }
            } elseif (!empty($value)) {
                $entity_ids[$value] = $value;
            }
        }
        foreach ($entity_ids as $entity_id) {
            $ret[]['value'] = $entity_id;
        }
        return $ret;
    }

    public function fieldTypeOnLoad(Sabai_Addon_Field_IField $field, array &$values)
    {
        $entities = array();
        foreach ($values as $key => $value) {
            $entities[$value['value']] = $key;
        }
        $values = array();
        foreach ($this->_addon->getApplication()
            ->Entity_TypeImpl($this->_entityType)
            ->entityTypeGetEntitiesByIds(array_keys($entities))
        as $entity) {
            $key = $entities[$entity->getId()];
            $values[$key] = $entity;
        }
        ksort($values); // re-order as it was saved
    }
    
    public function fieldTypeIsModified($field, $valueToSave, $currentLoadedValue)
    {
        return $valueToSave !== $currentLoadedValue;
    }
}