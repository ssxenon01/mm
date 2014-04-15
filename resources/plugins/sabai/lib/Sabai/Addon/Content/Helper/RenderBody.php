<?php
class Sabai_Addon_Content_Helper_RenderBody extends Sabai_Helper
{
    public function help(Sabai $application, Sabai_Addon_Content_Entity $entity)
    {
        return $application->Filter('ContentPostBody', $entity->content_body[0]['filtered_value'], array($entity));
    }
}