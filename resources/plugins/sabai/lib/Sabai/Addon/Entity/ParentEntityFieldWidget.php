<?php
abstract class Sabai_Addon_Entity_ParentEntityFieldWidget implements Sabai_Addon_Field_IWidget
{
    protected $_addon, $_entityType, $_fieldTypes, $_info;

    public function __construct(Sabai_Addon $addon, $entityType, $fieldTypes)
    {
        $this->_addon = $addon;
        $this->_entityType = $entityType;
        $this->_fieldTypes = (array)$fieldTypes;
    }

    public function fieldWidgetGetInfo($key = null)
    {
        if (!isset($this->_info)) {
            $this->_info = array(
                'label' => __('Autocomplete text field', 'sabai'),
                'field_types' => $this->_fieldTypes,
                'accept_multiple' => true,
                'default_settings' => array(
                ),
            );
        }
        return isset($key) ? @$this->_info[$key] : $this->_info;
    }

    public function fieldWidgetGetSettingsForm($fieldType, array $fieldSettings, array $settings, array $parents = array())
    {
        return array();
    }

    public function fieldWidgetGetForm(Sabai_Addon_Field_IField $field, array $settings, $value = null, array $parents = array())
    {
        if (!$bundle = $this->_getParentBundle($field)) {
            return array();
        }
        return array(
            '#type' => 'autocomplete_default',
            '#default_value' => $this->_getDefaultValue($value),
            '#ajax_url' => $this->_addon->getApplication()->MainUrl($bundle->getPath() . '/_autocomplete.json'),
            '#default_items_callback' => array($this, 'getAutocompleteDefaultItems'),
            '#multiple' => $field->getFieldMaxNumItems() != 1,
            '#max_selection' => $field->getFieldMaxNumItems(),
            '#noscript' => array('#type' => 'select'),
        );
    }
    
    public function fieldWidgetGetPreview(Sabai_Addon_Field_IField $field, array $settings)
    {
        return '<input type="text" disabled="disabled" style="width:100%;" />';
    }

    public function fieldWidgetGetEditDefaultValueForm($fieldType, array $fieldSettings, array $settings, array $parents = array())
    {

    }
    
    public function getAutocompleteDefaultItems($defaultValue, &$defaultItems, &$noscriptOptions)
    {
        foreach ($this->_addon->getApplication()->Entity_TypeImpl($this->_entityType)->entityTypeGetEntitiesByIds($defaultValue) as $entity) {
            $id = $entity->getId();
            $title = $entity->getTitle();
            $defaultItems[] = array('id' => $id, 'text' => Sabai::h($title));
            $noscriptOptions[$id] = $title;
        }
    }
    
    private function _getDefaultValue($value)
    {
        if (isset($value)) {
            $default_value = array();
            foreach ($value as $entity) {
                $default_value[] = is_object($entity) ? $entity->getId() : $entity;
            }
        } else {
            $default_value = null;
        }
        return $default_value;
    }
    
    private function _getParentBundle($field)
    {        
        if (empty($field->Bundle->info['parent'])
            || (!$parent_bundle = $field->Bundle->info['parent'])
        ) {
            return false;
        }
        return $this->_addon->getApplication()->Entity_Bundle($parent_bundle);
    }
}