<?php
abstract class Sabai_Addon_Entity_Helper_SetGuestAuthorCookie extends Sabai_Helper
{
    public function help(Sabai $application, Sabai_Addon_Entity_Entity $entity, $lifetime = 8640000 /* 100 days */)
    {
        // Set cookie to track guest user
        if ($entity->getAuthorId()) {
            return;
        } else {
            $application->Entity_LoadFields($entity);
            if (!$guid = $this->_getEntityGuestAuthorGuid($application, $entity)) {
                return;
            }
        }
        
        $cookie = $application->getPlatform()->getCookie('sabai_entity_guids', '');
        if (is_string($cookie)
            && strlen($cookie)
            && ($content_guids = explode(',', $cookie))
        ) {
            if (false !== $key = array_search($guid, $content_guids)) {
                // remove from array so that the guid is always appended
                unset($content_guids[$key]);
            }
        } else {
            $content_guids = array();
        }
        $content_guids[] = $guid;
        if (count($content_guids) > 10) {
            $content_guids = array_slice($content_guids, -10, 10); // maximum of 10 guest posts
        }
        $application->getPlatform()->setCookie('sabai_entity_guids', implode(',', $content_guids), time() + $lifetime);
    }
    
    abstract protected function _getEntityGuestAuthorGuid(Sabai $application, Sabai_Addon_Entity_Entity $entity);
}