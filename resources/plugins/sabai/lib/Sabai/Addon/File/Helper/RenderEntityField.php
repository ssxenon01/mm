<?php
class Sabai_Addon_File_Helper_RenderEntityField extends Sabai_Helper
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
            case 'file_image':
                $i = 0;
                $ret = array();
                while ($images = array_slice($fieldValues, $i * 4, 4)) {
                    $ret[] = '<div class="sabai-row-fluid">';
                    foreach ($images as $image) {
                        $ret[] = '<div class="sabai-span3">' . $application->File_ThumbnailLink($entity, $image) . '</div>';
                    }
                    $ret[] = '</div>';
                    $i++;
                }
                return implode(PHP_EOL, $ret);

            case 'file_file':
                $ret = array();
                foreach ($fieldValues as $value) {
                    $ret[] = '<div>' . $application->File_Link($entity, $value) . '</div>';
                }
                return implode(PHP_EOL, $ret);
        }
    }
}