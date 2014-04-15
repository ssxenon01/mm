<?php
class Sabai_Addon_Directory_Helper_ListingOwner extends Sabai_Helper
{
    public function help(Sabai $application, Sabai_Addon_Entity_Entity $entity)
    {
        $application->Entity_LoadFields($entity);
        
        if (empty($entity->directory_claim)) {
            return;
        }
        
        foreach (array_keys($entity->directory_claim) as $user_id) {
            if (!empty($entity->directory_claim[$user_id]['expires_at'])
                && $entity->directory_claim[$user_id]['expires_at'] <= time()
            ) {
                continue;
            }
            return $application->UserIdentity($user_id);
        }
    }
}