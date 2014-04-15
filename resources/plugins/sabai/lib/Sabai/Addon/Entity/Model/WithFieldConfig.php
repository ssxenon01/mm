<?php
abstract class Sabai_Addon_Entity_Model_WithFieldConfig extends SabaiFramework_Model_EntityCollection_Decorator
{
    protected $_fieldConfigs, $_fieldConfigIdVar, $_fieldObjectVarName;

    public function __construct(SabaiFramework_Model_EntityCollection $collection, $fieldConfigIdVar = 'entity_fieldconfig_id', $fieldConfigObjectVarName = 'EntityFieldConfig')
    {
        parent::__construct($collection);
        $this->_fieldConfigIdVar = $fieldConfigIdVar;
        $this->_fieldConfigObjectVarName = $fieldConfigObjectVarName;
    }

    public function rewind()
    {
        $this->_collection->rewind();
        if (!isset($this->_fieldConfigs)) {
            $this->_fieldConfigs = array();
            if ($this->_collection->count() > 0) {
                $field_config_ids = array();
                while ($this->_collection->valid()) {
                    if ($field_config_id = $this->_collection->current()->{$this->_fieldConfigIdVar}) {
                        $field_config_ids[] = $field_config_id;
                    }
                    $this->_collection->next();
                }
                if (!empty($field_config_ids)) {
                    $this->_fieldConfigs = $this->_model->ModelEntities('Entity', 'FieldConfig', array_unique($field_config_ids))->getArray();
                }
                $this->_collection->rewind();
            }
        }
    }

    public function current()
    {
        $current = $this->_collection->current();
        if (($field_config_id = $current->{$this->_fieldConfigIdVar})
            && isset($this->_fieldConfigs[$field_config_id])
        ) {
            $current->assignObject($this->_fieldConfigObjectVarName, $this->_fieldConfigs[$field_config_id]);
        } else {
            $current->assignObject($this->_fieldConfigObjectVarName);
        }

        return $current;
    }
}