<?php
class Sabai_Addon_Field_Helper_Types extends Sabai_Helper
{
    private $_fieldFeatures = array();

    /**
     * Returns all available field types
     * @param Sabai $application
     */
    public function help(Sabai $application, $useCache = true)
    {
        if (!$useCache
            || (!$field_types = $application->getPlatform()->getCache('field_types'))
        ) {
            $field_types = array();
            foreach ($application->getModel('Type', 'Field')->fetch() as $type) {
                if (!$application->isAddonLoaded($type->addon)) continue;

                $field_type = $application->getAddon($type->addon)->fieldGetType($type->name);
                if (!is_object($field_type)) {
                    continue;
                }
                $info = $field_type->fieldTypeGetInfo();
                if (!isset($info)) {
                    continue;
                }
                $creatable = isset($info['creatable']) && !$info['creatable'] ? false : true;
                $editable = $creatable || !isset($info['editable']) || $info['editable'] ? true : false;
                $deletable = isset($info['deletable']) && !$info['deletable'] ? false : true;
                $widgets = $this->_getFeatureByFieldType($application, isset($info['act_as']) ? $info['act_as'] : $type->name, 'Widget');
                $field_types[$type->name] = array(
                    'addon' => $type->addon,
                    'type' => $type->name,
                    'default_widget' => isset($info['default_widget']) && isset($widgets[$info['default_widget']]) ? $info['default_widget'] : array_shift(array_keys($widgets)),
                    'widgets' => $widgets,
                    'label' => (string)@$info['label'],
                    'description' => (string)@$info['description'],
                    'creatable' => $creatable,
                    'editable' => $editable,
                    'deletable' => $deletable,
                );
                $field_types[$type->name] += $info;
            }
            uasort($field_types, array(__CLASS__, '_sortFieldTypesCallback'));
            $application->getPlatform()->setCache($field_types, 'field_types', 0);
        }

        return $field_types;
    }

    private static function _sortFieldTypesCallback($a, $b)
    {
        return strcmp($a['label'], $b['label']);
    }

    private function _getFeatureByFieldType(Sabai $application, $fieldType, $feature)
    {
        if (!isset($this->_fieldFeatures[$feature])) {
            $this->_fieldFeatures[$feature] = array();
            foreach ($application->getModel($feature, 'Field')->fetch(0, 0, 'name', 'ASC') as $entity) {
                if (!$application->isAddonLoaded($entity->addon)) {
                    $entity->markRemoved()->commit();
                    continue;
                }
                $get_feature_func = 'fieldGet' . $feature;
                $ifeature = $application->getAddon($entity->addon)->$get_feature_func($entity->name);
                if (!is_object($ifeature)) {
                    $entity->markRemoved()->commit();
                    continue;
                }
                $get_feature_info_func = 'field' . $feature . 'GetInfo';
                $info = $ifeature->$get_feature_info_func();
                foreach ((array)@$info['field_types'] as $field_type) {
                    $this->_fieldFeatures[$feature][$field_type][$entity->name] = $info['label'];
                }
            }
        }

        return isset($this->_fieldFeatures[$feature][$fieldType]) ? $this->_fieldFeatures[$feature][$fieldType] : array();
    }
}