<?php
class Sabai_Addon_Entity_Helper_Url extends Sabai_Helper
{
    public function help(Sabai $application, Sabai_Addon_Entity_IEntity $entity, $path = '', array $params = array(), $fragment = '', $separator = '&amp;')
    {     
        $bundle = $application->Entity_Bundle($entity);
        return $application->Url(array(
            'route' => $entity->getUrlPath($bundle, $path),
            'params' => $params,
            'fragment' => $fragment,
            'script' => 'main',
            'separator' => $separator,
        ));
    }
}