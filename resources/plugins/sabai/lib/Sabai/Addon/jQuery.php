<?php
class Sabai_Addon_jQuery extends Sabai_Addon
    implements Sabai_Addon_System_IAdminRouter
{
    const VERSION = '1.2.18', PACKAGE = 'sabai';
                
    public function isUninstallable($currentVersion)
    {
        return false;
    }
    
    public function onSabaiWebResponseRenderHtmlLayout(Sabai_Context $context, Sabai_WebResponse $response, &$content)
    {
        $this->_application->jQuery_Load($response);
        $this->_application->jQuery_LoadUI($response, array('effects-highlight', 'ui-sortable', 'ui-datepicker'));
        $this->_application->jQuery_LoadJson2($response);
        $assets_url = $this->_application->getPlatform()->getAssetsUrl();
        $this->_application->LoadJs($response, $assets_url . '/js/jquery.scrollTo.min.js', 'jquery-scrollto', 'jquery');
        $this->_application->LoadJs($response, $assets_url . '/js/jquery.sabai.js', 'sabai', 'jquery');
        $this->_application->LoadJs($response, $assets_url . '/js/jquery.autosize.min.js', 'jquery-autosize', 'jquery');
        $this->_application->LoadJs($response, $assets_url . '/js/select2.min.js', 'select2', 'jquery');
        $this->_application->LoadJs($response, $assets_url . '/js/jquery.cookie.js', 'jquery-cookie', 'jquery');
        $response->addJs('SABAI.init($("' . $context->getContainer() . '")); SABAI.isRTL = ' . ($this->_application->getPlatform()->isLanguageRTL() ? 'true' : ' false') . ';', true, 0);
        // Load jQuery UI CSS if on admin side or not disabled
        if ($context->isAdmin() || !$this->_config['no_ui_css']) {
            $this->_application->LoadCss($response, $this->_application->getPlatform()->getAssetsUrl() . '/css/jquery.ui.css', 'jquery-ui');
        }
    }
    
    public function getDefaultConfig()
    {
        return array(
            'no_conflict' => false,
            'no_ui_css' => true,
        );
    }
                
    public function hasSettingsPage($currentVersion)
    {
        return array('url' => '/settings/jquery', 'modal' => true, 'modal_width' => 470);
    }
    
    /* Start implementation of Sabai_Addon_System_IAdminRouter */

    public function systemGetAdminRoutes()
    {
        return array(
            '/settings/jquery' => array(
                'controller' => 'Settings',
                'title_callback' => true,
                'callback_path' => 'settings'
            ),
        );
    }

    public function systemOnAccessAdminRoute(Sabai_Context $context, $path, $accessType, array &$route)
    {

    }

    public function systemGetAdminRouteTitle(Sabai_Context $context, $path, $title, $titleType, array $route)
    {
        switch ($path) {
            case 'settings':
                return __('jQuery Settings', 'sabai');
        }
    }

    /* End implementation of Sabai_Addon_System_IAdminRouter */
}