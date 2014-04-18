<?php
abstract class Sabai_Addon_Entity_Model_WithField extends SabaiFramework_Model_EntityCollection_Decorator
{
    protected $_fields, $_fieldIdVar, $_fieldObjectVarName;

    public function __construct(SabaiFramework_Model_EntityCollection $collection, $fieldIdVar = 'entity_field_id', $fieldObjectVarName = 'EntityField')
    {
        parent::__construct($collection);
        $this->_fieldIdVar = $fieldIdVar;
        $this->_fieldObjectVarName = $fieldObjectVarName;
    }

    public function rewind()
    {
        $this->_collection->rewind();
        if (!isset($this->_fields)) {
            $this->_fields = array();
            if ($this->_collection->count() > 0) {
                $field_ids = array();
                while ($this->_collection->valid()) {
                    if ($field_id = $this->_collection->current()->{$this->_fieldIdVar}) {
                        $field_ids[] = $field_id;
                    }
                    $this->_collection->next();
                }
                if (!empty($field_ids)) {
                    $this->_fields = $this->_model->ModelEntities('Entity', 'Field', array_unique($field_ids))->getArray();
                }
                $this->_collection->rewind();
            }
        }
    }

    public function current()
    {
        $current = $this->_collection->current();
        if (($field_id = $current->{$this->_fieldIdVar})
            && isset($this->_fields[$field_id])
        ) {
            $current->assignObject($this->_fieldObjectVarName, $this->_fields[$field_id]);
        } else {
            $current->assignObject($this->_fieldObjectVarName);
        }

        return $current;
    }
}