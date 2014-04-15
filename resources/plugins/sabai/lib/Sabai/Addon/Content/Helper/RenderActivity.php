<?php
class Sabai_Addon_Content_Helper_RenderActivity extends Sabai_Addon_Entity_Helper_RenderActivity
{
    protected $_classPrefix = 'sabai-content-';
    
    protected function _getEntityActivity(Sabai $application, Sabai_Addon_Entity_Entity $entity)
    {
        return $entity->getFieldValue('content_activity');
    }
    
    protected function _getEntityAuthor(Sabai $application, Sabai_Addon_Entity_Entity $entity)
    {
        return $application->Content_Author($entity);
    }
}