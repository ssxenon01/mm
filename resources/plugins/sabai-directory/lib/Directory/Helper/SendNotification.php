<?php
class Sabai_Addon_Directory_Helper_SendNotification extends Sabai_Addon_System_Helper_SendEmail
{
    protected function _getEmailSettings(Sabai $application, $addonName)
    {
        return $application->Directory_NotificationSettings();
    }
}