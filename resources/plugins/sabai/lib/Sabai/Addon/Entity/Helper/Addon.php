<?php
class Sabai_Addon_Entity_Helper_Addon extends Sabai_Helper
{
    public function help(Sabai $application, Sabai_Addon_Entity_IEntity $entity)
    {
        return $application->getAddon($application->Entity_Bundle($entity)->addon);
    }
}