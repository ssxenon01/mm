<?php
class Sabai_Addon_Directory_Controller_Admin_Emails extends Sabai_Addon_System_Controller_Admin_EmailSettings
{    
    protected function _getEmailSettings(Sabai_Context $context)
    {        
        return $this->Directory_NotificationSettings();
    }
}