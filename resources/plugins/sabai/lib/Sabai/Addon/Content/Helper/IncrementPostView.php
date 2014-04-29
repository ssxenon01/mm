<?php
class Sabai_Addon_Content_Helper_IncrementPostView extends Sabai_Helper
{
    public function help(Sabai $application, Sabai_Addon_Content_Entity $entity, $countGuestViews = false, $guestCookieLifetime = 8640000 /* 100 days */)
    {
        if (!$countGuestViews && $application->getUser()->isAnonymous()) {
            return;   
        }
        
        $viewed = $this->_getSavedViews($application);
        if (empty($viewed)) {
            $viewed = array('posts' => array(), 'ts' => time(), 'pending' => array($entity->getId()));
        } else {
            if (in_array($entity->getId(), $viewed['posts'])) {
                // Veiwed already
                return;
            }
            $viewed['pending'][] = $entity->getId();
            // Allow view count update once per minute
            if ($viewed['ts'] > time() - 60) {
                $this->_saveViews($application, $viewed, $guestCookieLifetime);
                return;
            }
        }
        $application->getModel(null, 'Content')->getGateway('Post')->incrementView($viewed['pending']);
        foreach ($viewed['pending'] as $entity_id) {
            $viewed['posts'][] = $entity_id;
        }
        $viewed['posts'] = array_unique($viewed['posts']);
        if (count($viewed['posts']) > 100) {
            $viewed['posts'] = array_slice($viewed['posts'], 0, 100, true); // maximum of 100 posts
        }
        // Reset counters
        $viewed['pending'] = array();
        $viewed['ts'] = time();
        // Save
        $this->_saveViews($application, $viewed, $guestCookieLifetime);
    }
    
    protected function _getSavedViews(Sabai $application)
    {
        if ($application->getUser()->isAnonymous()) {
            $cookie = $application->getPlatform()->getCookie('sabai_content_viewed', array());
            if (!isset($cookie['posts'])) {
                return;
            }
            return array(
                'posts' => explode('.', $cookie['posts']),
                'ts' => $cookie['ts'],
                'pending' => isset($cookie['pending']) ? explode('.', $cookie['pending']) : array(),
            );
        }
        return $application->getPlatform()->getUserOption($application->getUser()->id, 'content_viewed');
    }
    
    protected function _saveViews(Sabai $application, array $views, $guestCookieLifetime)
    {
        if ($application->getUser()->isAnonymous()) {
            $cookie_expires = time() + $guestCookieLifetime;
            $application->getPlatform()->setCookie('sabai_content_viewed[posts]', implode('.', $views['posts']), $cookie_expires)
                ->setCookie('sabai_content_viewed[ts]', $views['ts'], $cookie_expires)
                ->setCookie('sabai_content_viewed[pending]', implode('.', $views['pending']), $cookie_expires);
        } else {
            $application->getPlatform()->setUserOption($application->getUser()->id, 'content_viewed', $views);
        }
    }
}