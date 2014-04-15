<?php
abstract class Sabai_Addon_Entity_Model_WithEntityType extends SabaiFramework_Model_EntityCollection_Decorator
{
    protected $_entityTypes, $_entityTypeNameVar, $_entityTypeObjectVarName;

    public function __construct(SabaiFramework_Model_EntityCollection $collection, $entityTypeNameVar = 'entitytype_name', $entityTypeObjectVarName = 'EntityType')
    {
        parent::__construct($collection);
        $this->_entityTypeNameVar = $entityTypeNameVar;
        $this->_entityTypeObjectVarName = $entityTypeObjectVarName;
    }

    public function rewind()
    {
        $this->_collection->rewind();
        if (!isset($this->_entityTypes)) {
            $this->_entityTypes = array();
            if ($this->_collection->count() > 0) {
                $entity_type_names = array();
                while ($this->_collection->valid()) {
                    if ($entity_type_name = $this->_collection->current()->{$this->_entityTypeNameVar}) {
                        $entity_type_names[] = $entity_type_name;
                    }
                    $this->_collection->next();
                }
                if (!empty($entity_type_names)) {
                    $this->_entityTypes = $this->_model->ModelEntities('Entity', 'EntityType', $entity_type_names)->getArray();
                }
                $this->_collection->rewind();
            }
        }
    }

    public function current()
    {
        $current = $this->_collection->current();
        if (($entity_type_name = $current->{$this->_entityTypeNameVar})
            && isset($this->_entityTypes[$entity_type_name])
        ) {
            $current->assignObject($this->_entityTypeObjectVarName, $this->_entityTypes[$entity_type_name]);
        } else {
            $current->assignObject($this->_entityTypeObjectVarName);
        }

        return $current;
    }
}