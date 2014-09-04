<?php
class Sabai_Addon_Markdown extends Sabai_Addon
    implements Sabai_Addon_Field_ITypes,
               Sabai_Addon_Field_IWidgets,
               Sabai_Addon_Form_IFields,
               Sabai_Addon_System_IAdminRouter
{
    const VERSION = '1.2.32', PACKAGE = 'sabai';
                
    public function isUninstallable($currentVersion)
    {
        return false;
    }
    
    /* Start implementation of Sabai_Addon_System_IAdminRouter */

    public function systemGetAdminRoutes()
    {
        return array(
            '/settings/markdown' => array(
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
                return __('Markdown Settings', 'sabai');
        }
    }

    /* End implementation of Sabai_Addon_System_IAdminRouter */
    
    /* Start implementation of Sabai_Addon_Field_ITypes */

    public function fieldGetTypeNames()
    {
        return array('markdown_text');
    }

    public function fieldGetType($name)
    {
        return new Sabai_Addon_Markdown_FieldType($this, $name);
    }

    /* End implementation of Sabai_Addon_Field_ITypes */

    /* Start implementation of Sabai_Addon_Field_IWidgets */

    public function fieldGetWidgetNames()
    {
        return array('markdown_textarea');
    }

    public function fieldGetWidget($name)
    {
        return new Sabai_Addon_Markdown_FieldWidget($this, $name);
    }

    /* End implementation of Sabai_Addon_Field_IWidgets */

    /* Start implementation of Sabai_Addon_Form_IFields */

    public function formGetFieldTypes()
    {
        return array('markdown_textarea');
    }

    public function formGetField($type)
    {
        return new Sabai_Addon_Markdown_FormField($this, $type);
    }
    
    /* End implementation of Sabai_Addon_Form_IFields */
    
    public function hasSettingsPage($currentVersion)
    {
        return array('url' => '/settings/markdown', 'modal' => true, 'modal_width' => 470);
    }
    
    public function getDefaultConfig()
    {
        return array(
            'help' => false,
            'help_url' => 'http://en.wikipedia.org/wiki/Markdown',
            'help_window' => array('width' => 720, 'height' => 480),
        );
    }
}
