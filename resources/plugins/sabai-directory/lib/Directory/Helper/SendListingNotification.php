<?php
class Sabai_Addon_Directory_Helper_SendListingNotification extends Sabai_Helper
{
    public function help(Sabai $application, $name, Sabai_Addon_Content_Entity $listing, $user = null, array $tags = array())
    {
        $bundle = $application->Entity_Bundle($listing);
        $tags += $application->Content_TemplateTags($listing, 'listing_');
        if (!isset($user)) {
            $user = $application->Content_Author($listing);
        }
        foreach ((array)$name as $notification_name) {
            $application->Directory_SendNotification($bundle->addon, 'listing_' . $notification_name, $tags, $user);
        }
    }
}