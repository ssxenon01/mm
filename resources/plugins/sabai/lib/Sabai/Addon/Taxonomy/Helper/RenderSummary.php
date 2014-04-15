<?php
class Sabai_Addon_Taxonomy_Helper_RenderSummary extends Sabai_Helper
{
    public function help(Sabai $application, Sabai_Addon_Taxonomy_Entity $entity, $length = 150, $trimmarker = '...')
    {
        return ($summary = $entity->getSummary($length, $trimmarker)) ? Sabai::h($summary) : '';
    }
}