<?php
class Sabai_Addon_Directory_Helper_SendClaimNotification extends Sabai_Helper
{
    public function help(Sabai $application, $name, Sabai_Addon_Directory_Model_Claim $claim)
    {
        if (!$listing = @$claim->fetchObject('Entity')) {
            $claim->with('Entity'); // load listing entity associated with the claim
            $listing = $claim->Entity;
        }
        $tags = array(
            '{claim_id}' => $claim->id,
            '{claim_comment}' => $claim->comment,
            '{claim_user_name}' => $claim->User->name,
            '{claim_user_email}' => $claim->User->email,
            '{claim_date}' => $application->Date($claim->created),
            '{claim_admin_note}' => $claim->admin_note,
            '{claim_admin_url}' => $application->AdminUrl('/' . $application->Entity_Addon($listing)->getDirectorySlug() . '/claims/' . $claim->id, array(), '', '&'), 
        );
        $tags += $application->Content_TemplateTags($listing, 'listing_');
        foreach ((array)$name as $notification_name) {
            $application->Directory_SendNotification($application->Entity_Bundle($listing)->addon, 'claim_' . $notification_name, $tags, $claim->User);
        }
    }
}