<?php
class Sabai_Addon_Content_Helper_IsAuthor extends Sabai_Addon_Entity_Helper_IsAuthor
{
    protected function _getEntityGuestAuthorGuid(Sabai $application, Sabai_Addon_Entity_Entity $entity)
    {
        return $entity->getSingleFieldValue('content_guest_author', 'guid');
    }
}