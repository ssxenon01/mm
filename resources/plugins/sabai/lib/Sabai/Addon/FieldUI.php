<?php
class Sabai_Addon_FieldUI extends Sabai_Addon
    implements Sabai_Addon_System_IAdminRouter
{
    const VERSION = '1.2.31', PACKAGE = 'sabai';
                
    public function isUninstallable($currentVersion)
    {
        return false;
    }
    
    /* Start implementation of Sabai_Addon_System_IAdminRouter */

    public function systemGetAdminRoutes()
    {
        $routes = array();
        
        foreach ($this->_application->getModel('Bundle', 'Entity')->fetch() as $bundle) {
            if (isset($bundle->info['fieldui_enable']) && $bundle->info['fieldui_enable'] === false) {
                continue;
            }
            $routes[$bundle->getPath() . '/fields'] = array(
                'controller' => 'Fields',
                'type' => Sabai::ROUTE_TAB,
                'title_callback' => true,
                'weight' => 10,
                'callback_path' => 'fields'
            );
            $routes[$bundle->getPath() . '/fields/submit'] = array(
                'controller' => 'SubmitFields',
                'type' => Sabai::ROUTE_CALLBACK,
                'method' => 'post',
            );
            $routes[$bundle->getPath() . '/fields/create'] = array(
                'controller' => 'CreateField',
            );
            $routes[$bundle->getPath() . '/fields/edit'] = array(
                'controller' => 'EditField',
            );
            $routes[$bundle->getPath() . '/fields/edit_widget'] = array(
                'controller' => 'EditFieldWidget',
            );
        }

        return $routes;
    }

    public function systemOnAccessAdminRoute(Sabai_Context $context, $path, $accessType, array &$route)
    {

    }

    public function systemGetAdminRouteTitle(Sabai_Context $context, $path, $title, $titleType, array $route)
    {
        switch ($path) {
            case 'fields':
                return __('Manage Fields', 'sabai');
        }
    }

    /* End implementation of Sabai_Addon_System_IAdminRouter */
    
    public function onSabaiWebResponseRenderFieldUIAdminFields($context, $response, $template)
    {        
        $submit_confirm = __('One or more fields have not been saved. You must save or delete these fields first before submitting the form.', 'sabai');
        $leave_confirm = __('You have made changes but it has not been saved. You must submit the form for the changes to be saved permanently.', 'sabai');
        $delete_confirm = __('Are you sure?', 'sabai');
        $response->addJs(sprintf('SABAI.FieldUI.adminFields({submitConfirm:"%s", leaveConfirm:"%s", deleteFieldConfirm: "%s"});', $submit_confirm, $leave_confirm, $delete_confirm))
            ->addJsFile($this->_application->getPlatform()->getAssetsUrl() . '/js/sabai-fieldui-admin-fields.js', 'sabai-fieldui-admin-fields', 'sabai')
            ->addCssFile($this->_application->getPlatform()->getAssetsUrl() . '/css/sabai-fieldui-admin-fields.css', 'sabai-fieldui-admin-fields');
        if ($this->_application->getPlatform()->isLanguageRTL()) {
            $response->addCssFile($this->_application->getPlatform()->getAssetsUrl() . '/css/sabai-fieldui-admin-fields-rtl.css', 'sabai-fieldui-admin-fields-rtl');
        }
    }
    
    public function onEntityITypesInstalled(Sabai_Addon $addon, ArrayObject $log)
    {
        $this->_application->getAddon('System')->reloadRoutes($this, true);
    }

    public function onEntityITypesUninstalled(Sabai_Addon $addon, ArrayObject $log)
    {
        $this->_application->getAddon('System')->reloadRoutes($this, true);
    }

    public function onEntityITypesUpgraded(Sabai_Addon $addon, ArrayObject $log)
    {
        $this->_application->getAddon('System')->reloadRoutes($this, true);
    }
    
    public function onEntityCreateBundlesSuccess($entityType, $bundles)
    {
        $this->_application->getAddon('System')->reloadRoutes($this, true);
    }
    
    public function onEntityUpdateBundlesSuccess($entityType, $bundles)
    {
        $this->_application->getAddon('System')->reloadRoutes($this, true);
    }
    
    public function onEntityDeleteBundlesSuccess($entityType, $bundles)
    {  
        $this->_application->getAddon('System')->reloadRoutes($this, true);
    }
    
    public function isInstallable($version) {
        if (!parent::isInstallable($version)) return false;
        
        $required_addons = array(
            'Entity' => '1.1.1dev1',
        );
        return $this->_application->CheckAddonVersion($required_addons);
    }
}
