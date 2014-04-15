<?php
class Sabai_Addon_Entity_Helper_CustomFields extends Sabai_Helper
{    
    /**
     * Returns all custom fields of an entity
     * @param Sabai $application
     * @param Sabai_Addon_Entity_Entity
     * @return array
     */
    public function help(Sabai $application, Sabai_Addon_Entity_IEntity $entity, $excludeMeta = true)
    {
        $ret = array();
        foreach ($entity->getFieldValues() as $field_name => $field_values) {
            if (empty($field_values)
                || (!$field = $application->Entity_Field($entity, $field_name))
                || !$field->isCustomField()
                || ($excludeMeta && strpos($field_name, 'field_meta_') === 0)
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