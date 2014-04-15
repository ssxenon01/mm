<?php
class Sabai_Addon_Date_Helper_Scripts extends Sabai_Helper
{
    public function help(Sabai $application)
    {
        $scripts = array($application->getPlatform()->getAssetsUrl() . '/js/jquery.ui.timepicker.js');
        // Load datepicker i18N script?
        if (SABAI_LANG !== 'en_US') {
            $i18n_file = 'jquery.ui.datepicker-' . str_replace('_', '-', SABAI_LANG) . '.js';
            if (file_exists($application->getPlatform()->getAssetsDir() . '/js/' . $i18n_file)) {
                $scripts[] = $application->getPlatform()->getAssetsUrl() . '/js/' . $i18n_file;
            }
        }
        // Load script to instantiate date/time pickers
        $scripts[] = $application->getPlatform()->getAssetsUrl() . '/js/datetimepicker.js';
        
        return $scripts;
    }
}