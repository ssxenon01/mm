<?php
abstract class Sabai_Addon_Entity_FeaturedEntityFieldType implements Sabai_Addon_Field_IType
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
                'label' => 'Featured',
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
                'value' => array(
                    'type' => Sabai_Addon_Field::COLUMN_TYPE_BOOLEAN,
                    'notnull' => true,
                    'unsigned' => true,
                    'was' => 'value',
                    'default' => false,
                ),
                'featured_at' => array(
                    'type' => Sabai_Addon_Field::COLUMN_TYPE_INTEGER,
                    'notnull' => true,
                    'unsigned' => true,
                    'was' => 'featured_at',
                    'default' => 0,
                ),
                'expires_at' => array(
                    'type' => Sabai_Addon_Field::COLUMN_TYPE_INTEGER,
                    'notnull' => true,
                    'unsigned' => true,
                    'was' => 'expires_at',
                    'default' => 0,
                ),
            ),
            'indexes' => array(
                'value_featured_at' => array(
                    'fields' => array(
                        'value' => array('sorting' => 'ascending'),
                        'featured_at' => array('sorting' => 'ascending')
                    ),
                    'was' => 'value_featured_at',
                ),
                'expires_at' => array(
                    'fields' => array(
                        'expires_at' => array('sorting' => 'ascending'),
                    ),
                    'was' => 'expires_at',
                ),
            ),
        );
    }

    public function fieldTypeOnSave(Sabai_Addon_Field_IField $field, array $values, array $currentValues = null)
    {
        $value = array_shift($values); // single entry allowed for this field
        if (!is_array($value) || empty($value['value'])) {
            $value = false; // delete
        } else {
            $value['value'] = true;
            if (empty($value['featured_at'])) {
                $value['featured_at'] = time(); 
                if (!empty($currentValues)) {
                    $current_value = array_shift($currentValues);
                    if (!empty($current_value['featured_at'])) {
                        $value['featured_at'] = $current_value['featured_at'];
                    }
                }  
            }
        }
        return array($value);
    }

    public function fieldTypeOnLoad(Sabai_Addon_Field_IField $field, array &$values)
    {

    }
    
    public function fieldTypeIsModified($field, $valueToSave, $currentLoadedValue)
    {
        return $valueToSave !== $currentLoadedValue;
    }
}