<?php
class Sabai_Addon_Directory_Controller_Admin_AccessControl extends Sabai_Addon_Form_Controller
{
    private $_roles, $_adminRoles = array(), $_allPermissions = array();
    
    protected function _doGetFormSettings(Sabai_Context $context, array &$formStorage)
    {
        $this->_successFlash = __('Settings saved.', 'sabai-directory');
        $roles = $this->System_Roles();
        $role_options = array();
        foreach ($roles as $role_name => $role) {
            if ($role->isGuest()) {
                continue;
            }
            $role_options[$role_name] = $role->title;
            if ($this->getPlatform()->isSuperUserRole($role_name)) {
                $this->_adminRoles[$role_name] = $role_name;
            }
        }
        $form = array(
            '#tree' => true,
            'permissions' => array(
                '#title' => __('Permissions', 'sabai-directory'),
                '#collapsed' => false,
                'normal' => array(),
            ),
        );
        $this->_submitButtons[] = array('#value' => __('Save Changes', 'sabai-directory'), '#btn_type' => 'primary');
        $category_names = array(
            $this->getAddon()->getListingBundleName(),
            $this->getAddon()->getReviewBundleName(),
            $this->getAddon()->getPhotoBundleName(),
            $this->getAddon()->getLeadBundleName(),
        );     
        $categories = $this->getModel('PermissionCategory', 'System')->name_in($category_names)->fetch()->with('Permissions');
        $text_align = $this->getPlatform()->isLanguageRTL() ? 'right' : 'left';
        foreach ($categories as $category) {
            // Create grid table
            $form['permissions']['normal'][$category->name] = array(
                '#type' => 'grid',
                '#collapsible' => false,
                '#row_attributes' => array('@all' => array('label' => array('style' => 'text-align:' . $text_align . ';'))),
                '#column_attributes' => array('label' => array('style' => 'text-align:' . $text_align . '; width:40%')),
                '#weight' => array_search($category->name, $category_names),
                'label' => array(
                    '#type' => 'item',
                    '#title' => $this->Translate($category->title),
                ),
            );
            // Add columns
            $role_weight = 0;
            $role_permissions = array();
            foreach ($roles as $role_name => $role) {
                $role_permissions[$role_name] = $role->permissions;
                $is_admin_role = in_array($role_name, $this->_adminRoles);
                $role_title = $this->Translate($role->title);
                $form['permissions']['normal'][$category->name][$role->name] = array(
                    '#type' => 'checkbox',
                    '#title' => $role_title,
                    '#disabled' => $is_admin_role,
                    '#weight' => $is_admin_role ? 0 : ++$role_weight,
                );
                $form['permissions']['normal'][$category->name]['#column_attributes'][$role->name]
                    = array('style' => 'width:' . round(60 / count($roles)) .'%');
            }
            // Add rows
            foreach ($category->Permissions as $permission) {
                $form['permissions']['normal'][$category->name]['#default_value'][$permission->name]
                    = array('label' => $permission->title,);
                foreach ($roles as $role_name => $role) {
                    if (isset($this->_adminRoles[$role_name])) {
                        $form['permissions']['normal'][$category->name]['#default_value'][$permission->name][$role_name] = 1; 
                    } elseif ($role->isGuest()) {
                        if (!$permission->guest_allowed) {
                            $form['permissions']['normal'][$category->name]['#row_settings'][$permission->name][$role_name]
                                = array('#attributes' => array('disabled' => 'disabled'));
                        } else {
                            $form['permissions']['normal'][$category->name]['#default_value'][$permission->name][$role_name]
                                = !empty($role_permissions[$role_name][$permission->name]);
                        }
                    } else {
                        $form['permissions']['normal'][$category->name]['#default_value'][$permission->name][$role_name] = !empty($role_permissions[$role_name][$permission->name]);
                    }
                }
                $this->_allPermissions[] = $permission->name;
            }
        }
        
        $this->_roles = $roles;

        return $form;
    }

    public function submitForm(Sabai_Addon_Form_Form $form, Sabai_Context $context)
    {
        $roles_processed = array();
        foreach ($this->_extractPermissionsByRole($form->values['permissions']['normal']) as $role_name => $permissions) {
            $roles_processed[$role_name] = $role_name;
            if (in_array($role_name, $this->_adminRoles)) continue;

            $this->_roles[$role_name]->removePermission($this->_allPermissions)->addPermission(array_keys($permissions));
        }
        // Remove permissions from roles without any permissions selected
        foreach (array_keys($this->_roles) as $role_name) {
            if (in_array($role_name, $roles_processed)
                || in_array($role_name, $this->_adminRoles)
            ) {
                continue;
            }
                
            $this->_roles[$role_name]->removePermission($this->_allPermissions);
        }
            
        // Commit
        $this->getModel(null, 'System')->commit();
    }
    
    private function _extractPermissionsByRole($values, $excludeEmpty = true, &$max = null)
    {
        $ret = array();
        foreach ($values as $category_name => $permissions) {
            foreach ($permissions as $permission_name => $roles) {
                foreach ($roles as $role_name => $value) {
                    if (!isset($this->_roles[$role_name])
                        || ($excludeEmpty && empty($value)) 
                    ) {
                        continue;
                    }
                        
                    $ret[$role_name][$permission_name] = $value;
                    if (isset($max) && $value > $max) {
                        $max = $value;
                    }
                }
            }
        }
        return $ret;
    }
}