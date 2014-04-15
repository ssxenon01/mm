<?php
class Sabai_Addon_Date_Helper_RenderEntityField extends Sabai_Helper
{
    /**
     * Renders an entity field
     * @param Sabai $application
     * @param Sabai_Addon_Entity_IEntity $entity
     * @param string $fieldType
     * @param array $fieldSettings
     * @param array $fieldValues
     * @param array $options
     */
    public function help(Sabai $application, Sabai_Addon_Entity_IEntity $entity, $fieldType, array $fieldSettings, array $fieldValues, array $options = array())
    {
        switch ($fieldType) {
            case 'date_timestamp':
                $ret = array();
                foreach ($fieldValues as $value) {
                    $ret[] = !empty($fieldSettings['enable_time']) ? $application->DateTime($value) : $application->Date($value);
                }
                if (!isset($options['separator'])) {
                    $options['separator'] = ', ';
                } elseif ($options['separator'] === false) {
                    return $ret;
                }
                return implode($options['separator'], $ret);
        }
    }
}