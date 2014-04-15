<?php
class Sabai_Addon_Markdown_Helper_RenderEntityField extends Sabai_Helper
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
            case 'markdown_text':
                $ret = array();
                foreach ($fieldValues as $value) {
                    $ret[] = $value['filtered_value'];
                }
                return implode(PHP_EOL, $ret);
        }
    }
}