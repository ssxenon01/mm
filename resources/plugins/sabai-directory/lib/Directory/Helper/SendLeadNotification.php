<?php
class Sabai_Addon_Directory_Helper_SendLeadNotification extends Sabai_Helper
{
    public function help(Sabai $application, $name, Sabai_Addon_Content_Entity $lead, $user = null, array $tags = array())
    {
        if (!$listing = $application->Content_ParentPost($lead)) {
            return;
        }
        $bundle = $application->Entity_Bundle($lead);
        $tags += array('{lead_url}' => $application->Url('/' . $application->getAddon('Directory')->getDashboardSlug() . '/leads/' . $lead->getId()));
        $tags += $application->Content_TemplateTags($lead, 'lead_');
        $tags += $application->Content_TemplateTags($listing, 'listing_');
        foreach ((array)$name as $notification_name) {
            $application->Directory_SendNotification($bundle->addon, 'lead_' . $notification_name, $tags, $user);
        }
    }
}