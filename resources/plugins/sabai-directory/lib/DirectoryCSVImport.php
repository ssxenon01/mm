<?php
class Sabai_Addon_DirectoryCSVImport extends Sabai_Addon
    implements Sabai_Addon_System_IAdminRouter
{
    const VERSION = '1.2.30', PACKAGE = 'sabai-directory';
    
    /* Start implementation of Sabai_Addon_System_IAdminRouter */

    public function systemGetAdminRoutes()
    {
        $routes = array();
        foreach ($this->_application->getModel('Bundle', 'Entity')->type_is('directory_listing')->fetch() as $bundle) {
            $routes['/' . $this->_application->getAddon($bundle->addon)->getDirectorySlug() . '/import_csv'] = array(
                'controller' => 'Import',
                'title_callback' => true,
                'callback_path' => 'import',
                'controller_addon' => $this->_name,
                'priority' => 5,
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
            case 'import':
                return __('Import Listings from CSV', 'sabai-directory');
        }
    }

    /* End implementation of Sabai_Addon_System_IAdminRouter */
    
    public function onDirectoryInstallSuccess($addon)
    {        
        $this->_application->getAddon('System')->reloadRoutes($this, true);
    }
  
    public function onDirectoryUninstallSuccess($addon)
    {
        $this->_application->getAddon('System')->reloadRoutes($this, true);
    }
    
    public function onDirectoryUpgradeSuccess(Sabai_Addon $addon, $log, $previousVersion)
    {
        $this->_application->getAddon('System')->reloadRoutes($this, true);
    }
  
    public function onContentAdminPostsLinksFilter(&$links, $bundle)
    {
        if ($bundle->type !== 'directory_listing') return;
        
        $links[] = $this->_application->LinkTo(
            __('Import CSV', 'sabai-directory'),
            $this->_application->Url($bundle->getPath() . '/import_csv'),
            array('icon' => 'table'),
            array('class' => 'sabai-btn sabai-btn-success sabai-btn-small')
        );
    }
}
