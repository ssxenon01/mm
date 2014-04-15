<?php
class Sabai_Addon_jQuery_Helper_LoadJson2 extends Sabai_Helper
{
    /**
     * Loads json2.js script file
     * @param Sabai $application
     * @param Sabai_WebResponse
     */
    public function help(Sabai $application, Sabai_WebResponse $response)
    {
        $response->addJsFile($application->getPlatform()->getAssetsUrl() . '/js/json2.js', 'json2');
    }
}
