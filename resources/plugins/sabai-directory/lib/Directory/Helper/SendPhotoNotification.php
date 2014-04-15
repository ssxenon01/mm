<?php
class Sabai_Addon_Directory_Helper_SendPhotoNotification extends Sabai_Helper
{
    public function help(Sabai $application, $name, Sabai_Addon_Content_Entity $photo, $user = null, array $tags = array())
    {
        if (!$listing = $application->Content_ParentPost($photo)) {
            return;
        }
        $bundle = $application->Entity_Bundle($photo);
        $tags += $application->Content_TemplateTags($photo, 'photo_') + $application->Content_TemplateTags($listing, 'listing_');
        if (!isset($user)) {
            $user = $application->Content_Author($photo);
        }
        foreach ((array)$name as $notification_name) {
            $application->Directory_SendNotification($bundle->addon, 'photo_' . $notification_name, $tags, $user);
        }
    }
}