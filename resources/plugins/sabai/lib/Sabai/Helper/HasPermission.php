<?php
class Sabai_Helper_HasPermission extends Sabai_Helper
{
    protected $_permissions = array();
    
    /**
     * Checks whether the user has a certain permission, e.g. hasPermission('A').
     * Pass in an array of permission names to check if the user has one of the supplied
     * permissions, e.g. hasPermission(array('A', 'B')).
     * It is also possible to check whether the user has a group of certain permissions
     * by passing in an array of permission array, e.g. hasPermission(array(array('A', 'B', 'C'))).
     * For another example, in order to see whether the user has permission A or both permissions B and C
     * would be: hasPermission(array('A', array('B', 'C')))
     *
     * @param Sabai $application
     * @param string|array $permission
     * @param SabaiFramework_User_Identity|null
     * @return bool
     */
    public function help(Sabai $application, $permission, SabaiFramework_User_Identity $identity = null)
    {
        if (!isset($identity)) {
            if ($application->getUser()->isAdministrator()) return true;
            
            $identity = $application->getUser()->getIdentity();
        } else {
            if ($application->IsAdministrator($identity)) return true;
        }
        
        if (!isset($this->_permissions[$identity->id])) {
            $this->_permissions[$identity->id] = $this->_getUserPermissions($application, $identity);
        }

        if (empty($this->_permissions[$identity->id])) return false;

        if (is_string($permission)) {
            return isset($this->_permissions[$identity->id][$permission]);
        }
        
        foreach ($permission as $_perm) {
            foreach ((array)$_perm as $__perm) {
                if (!isset($this->_permissions[$identity->id][$__perm])) continue 2;
            }
            return true;
        }
        return false;
    }
    
    protected function _getUserPermissions(Sabai $application, SabaiFramework_User_Identity $identity)
    {
        $permissions = array();
        
        if (!$identity->isAnonymous()) {
            // Fetch permissions by roles the user belongs to
            if ($roles = $application->getPlatform()->getUserRolesByUser($identity)) {
                foreach ($application->getAddon('System')->getModel('Role')->name_in($roles)->fetch() as $role) {
                    if (!$role->permissions) continue;
                
                    $permissions += $role->permissions;
                }
            }
        } else {
            // Fetch permission of guest roles
            if (($guest_role = $application->getAddon('System')->getModel('Role')->name_is('_guest_')->fetchOne())
                && $guest_role->permissions
            ) {                
                $permissions += $guest_role->permissions;
            }
        }
        // Allow addons to set permissions
        $application->doEvent('SystemLoadPermissions', array($identity, &$permissions));
        
        return $permissions;
    }
}