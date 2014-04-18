<?php
class Sabai_Addon_Field_Helper_Schema extends Sabai_Helper
{
    /**
     * Returns schema for available field types
     * @param Sabai $application
     */
    public function help(Sabai $application, $useCache = true)
    {
        if (!$useCache
            || (!$field_schema = $application->getPlatform()->getCache('field_schema'))
        ) {
            $field_schema = array();
            foreach ($application->getModel('Type', 'Field')->fetch() as $type) {
                if (!$application->isAddonLoaded($type->addon)) continue;

                $field_type = $application->getAddon($type->addon)->fieldGetType($type->name);
                $field_schema[$type->name] = (array)$field_type->fieldTypeGetSchema();
            }
            $application->getPlatform()->setCache($field_schema, 'field_schema', 0);
        }

        return $field_schema;
    }
}