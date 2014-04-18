<?php
class Sabai_Addon_Taxonomy_Helper_RenderBody extends Sabai_Helper
{
    public function help(Sabai $application, Sabai_Addon_Taxonomy_Entity $entity)
    {
        return $application->Filter('TaxonomyTermBody', $entity->taxonomy_body[0]['filtered_value'], array($entity));
    }
}