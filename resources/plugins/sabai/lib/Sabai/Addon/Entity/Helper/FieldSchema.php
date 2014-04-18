<?php
class Sabai_Addon_Entity_Helper_FieldSchema extends Sabai_Helper
{
    /**
     * Returns schema for available field types
     * @param Sabai $application
     */
    public function help(Sabai $application)
    {
        $field_schema = array();
        foreach ($application->getModel('FieldConfig', 'Entity')->fetch() as $field_config) {
            try {
                $field_type = $application->Field_TypeImpl($field_config->type);
            } catch (Sabai_IException $e) {
                $application->LogError($e);
                continue;
            }
            if (!$_field_schema = $field_type->fieldTypeGetSchema($field_config->settings)) continue;
            
            $field_schema[$field_config->name] = (array)$_field_schema;
        }

        return $field_schema;
    }
}
