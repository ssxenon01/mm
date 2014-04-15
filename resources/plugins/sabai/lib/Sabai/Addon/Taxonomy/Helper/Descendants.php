<?php
class Sabai_Addon_Taxonomy_Helper_Descendants extends Sabai_Helper
{
    public function help(Sabai $application, $entity)
    {
        $entity_id = $entity instanceof Sabai_Addon_Taxonomy_Entity ? $entity->getId() : $entity;
        return $application->getModel('Term', 'Taxonomy')->fetchDescendantsByParent($entity_id);
    }
}