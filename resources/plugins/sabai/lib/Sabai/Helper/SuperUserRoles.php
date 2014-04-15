<?php
class Sabai_Helper_SuperUserRoles extends Sabai_Helper
{
    public function help(Sabai $application)
    {
        $ret = array();
        foreach ($application->getPlatform()->getUserRoles() as $role_name => $role_title) {
            if ($application->getPlatform()->isSuperUserRole($role_name)) {
                $ret[$role_name] = $role_title;
            }
        }
        return $ret;
    }
}
