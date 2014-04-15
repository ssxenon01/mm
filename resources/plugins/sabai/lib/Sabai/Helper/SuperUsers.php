<?php
class Sabai_Helper_SuperUsers extends Sabai_Helper
{
    public function help(Sabai $application)
    {
        return $application->getPlatform()->getUsersByUserRole(array_keys($application->SuperUserRoles()));
    }
}