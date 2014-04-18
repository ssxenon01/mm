<?php
class Sabai_Addon_Entity_Helper_CustomFields extends Sabai_Helper
{    
    /**
     * Returns all custom fields of an entity
     * @param Sabai $application
     * @param Sabai_Addon_Entity_Entity
     * @param array $exclude
     * @param array $include
     * @return array
     */
    public function help(Sabai $application, Sabai_Addon_Entity_IEntity $entity, array $exclude = null, array $include = null)
    {
        $ret = array();
        $fields = $application->doFilter('EntityCustomFields', $entity->getFieldValues(), array($entity));
        foreach ($fields as $field_name => $field_values) {
            if (empty($field_values)
                || strpos($field_name, 'field_') !== 0
                || strpos($field_name, 'field_meta_') === 0
                || (isset($exclude) && in_array($field_name, $exclude))
                || (isset($include) && !in_array($field_name, $include))
                || (!$field = $application->Entity_Field($entity, $field_name))
            ) continue;
            
            $ret[$field->getFieldWeight()] = array(
                'name' => $field_name,
                'values' => $field_values,
                'title' => $field->getFieldTitle(),
                'type' => $field->getFieldType(),
                'settings' => $field->getFieldSettings(),
            );
        }
        
        if (!empty($ret)) {
            ksort($ret);
        }
        
        return $ret;
    }
}
