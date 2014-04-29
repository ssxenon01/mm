<?php
class Sabai_Addon_Directory_Helper_SendReviewNotification extends Sabai_Helper
{
    public function help(Sabai $application, $name, Sabai_Addon_Content_Entity $review, $user = null, array $tags = array())
    {
        if (!$listing = $application->Content_ParentPost($review)) {
            return;
        }
        $bundle = $application->Entity_Bundle($review);
        $tags += $application->Content_TemplateTags($review, 'review_') + $application->Content_TemplateTags($listing, 'listing_');
        if (!isset($user)) {
            $user = $application->Content_Author($review);
        }
        foreach ((array)$name as $notification_name) {
            $application->Directory_SendNotification($bundle->addon, 'review_' . $notification_name, $tags, $user);
        }
    }
}