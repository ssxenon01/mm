<?php
class Sabai_Addon_File_Helper_Url extends Sabai_Helper
{
    public function help(Sabai $application, Sabai_Addon_Entity_IEntity $entity, $file, $size = null)
    {
        if (!is_array($file)) {
            $file = $entity->getSingleFieldValue($file);
        }
        $route = '/file/' . $file['id'];
        if (!$application->getAddon('File')->getConfig('no_pretty_url')) {
            $route .= '/' . $file['title'];
        }
        return $application->Entity_Url($entity, $route, $file['is_image'] && isset($size) ? array('size' => $size) : array());
    }
}
