<?php
class Sabai_Addon_Directory_Helper_ClaimListing extends Sabai_Helper
{
    public function help(Sabai $application, Sabai_Addon_Content_Entity $listing, SabaiFramework_User_Identity $identity, $duration = 0)
    {
        $application->Entity_LoadFields($listing);
        if (($current_claim = $listing->getFieldValue('directory_claim'))
            && isset($current_claim[$identity->id])
            && $current_claim[$identity->id]['expires_at'] > time()
        ) {
            $claimed_at = $current_claim[$identity->id]['claimed_at'];
            $expires_at = empty($duration) ? 0 : $current_claim[$identity->id]['expires_at'] + $duration * 86400; // extend expiration time
        } else {
            $claimed_at = time();
            $expires_at = empty($duration) ? 0 : time() + $duration * 86400;
        }
        $values = array(
            'directory_claim' => array(
                'claimed_by' => $identity->id,
                'claimed_at' => $claimed_at,
                'expires_at' => $expires_at
            ),
        );
        // Change author to owner if the current author is an anonymous user
        if (!$listing->getAuthorId()) {
            $values += array(
                'content_guest_author' => false,
                'content_post_user_id' => $identity->id,
            );
        }
        $application->getAddon('Entity')->updateEntity($listing, $values);
    }
}