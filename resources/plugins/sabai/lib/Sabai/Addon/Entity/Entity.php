<?php
abstract class Sabai_Addon_Entity_Entity implements Sabai_Addon_Entity_IEntity
{
    public $data = array();
    protected $_properties, $_fieldValues = array(), $_fieldTypes = array(), $_fieldsLoaded = false, $_fromCache = false;
    
    public function __construct(array $properties)
    {
        $this->_properties = $properties;
    }
    
    public function getFieldValue($name)
    {
        return @$this->_fieldValues[$name];
    }
    
    public function getSingleFieldValue($name, $key = null, $index = 0)
    {
        return isset($key) ? @$this->_fieldValues[$name][$index][$key] : @$this->_fieldValues[$name][$index];
    }

    public function getFieldValues()
    {
        return $this->_fieldValues;
    }
    
    public function getFieldType($name)
    {
        return $this->_fieldTypes[$name];
    }

    public function getFieldTypes($unique = true)
    {
        return $unique ? array_unique($this->_fieldTypes) : $this->_fieldTypes;
    }
    
    public function getFieldNamesByType($type)
    {
        return array_keys($this->_fieldTypes, $type);
    }
    
    public function initFields(array $values, array $types)
    {
        $this->_fieldValues = $values;
        $this->_fieldTypes = $types;
        $this->_fieldsLoaded = true;

        return $this;
    }
    
    public function isFieldsLoaded()
    {
        return $this->_fieldsLoaded;
    }
    
    public function __get($name)
    {
        return $this->getFieldValue($name);
    }
    
    public function __isset($name)
    {
        return isset($this->_fieldValues[$name]);
    }
    
    public function __unset($name)
    {
        unset($this->_fieldValues[$name]);
    }
    
    public function __toString()
    {
        return $this->getTitle();
    }
        
    public function serialize()
    {
        return serialize($this->_properties);
    }

    public function unserialize($serialized)
    {
        $this->_properties = unserialize($serialized);
        $this->_fromCache = true;
    }
    
    public function isFromCache()
    {
        return $this->_fromCache;
    }
}