<?php
class Sabai_Addon_Directory_Helper_IsListingOwner extends Sabai_Helper
{
    public function help(Sabai $application, Sabai_Addon_Entity_Entity $entity, $checkExpired = true, SabaiFramework_User $user = null)
    {
        if (!isset($user)) {
            $user = $application->getUser();
        }
        
        if ($user->isAnonymous()) {
            return false;
        }
        
        $application->Entity_LoadFields($entity);
        
        if (empty($entity->directory_claim[$user->id])) {
            return false;
        }
        
        if (!$checkExpired) {
            return true;
        }
        
        return (empty($entity->directory_claim[$user->id]['expires_at'])
            || $entity->directory_claim[$user->id]['expires_at'] > time());
    }
}