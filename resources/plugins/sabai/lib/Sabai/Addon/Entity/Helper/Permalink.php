<?php
class Sabai_Addon_Entity_Helper_Permalink extends Sabai_Helper
{
    public function help(Sabai $application, Sabai_Addon_Entity_IEntity $entity, array $options = array(), $fragment = '')
    {
        $atts = isset($options['atts']) ? $options['atts'] : array();
        $atts += array('class' => str_replace('_', '-', 'sabai-entity-type-' . $entity->getType() . ' sabai-entity-bundle-name-' . $entity->getBundleName() . ' sabai-entity-bundle-type-' . $entity->getBundleType()));
        return $application->LinkTo(
            isset($options['title']) ? $options['title'] : $entity->getTitle(),
            $application->Entity_Url($entity, $fragment),
            $options,
            $atts
        );
    }
}