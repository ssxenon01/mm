<?php
class Sabai_Addon_Content_Helper_SetGuestAuthorCookie extends Sabai_Addon_Entity_Helper_SetGuestAuthorCookie
{
    protected function _getEntityGuestAuthorGuid(Sabai $application, Sabai_Addon_Entity_Entity $entity)
    {
        return $entity->getSingleFieldValue('content_guest_author', 'guid');
    }
}