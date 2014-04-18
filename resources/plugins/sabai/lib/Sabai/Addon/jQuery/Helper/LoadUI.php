<?php
class Sabai_Addon_jQuery_Helper_LoadUI extends Sabai_Helper
{
    /**
     * Loads jQuery UI script files
     * @param Sabai $application
     * @param Sabai_WebResponse
     */
    public function help(Sabai $application, Sabai_WebResponse $response, $components)
    {
        $response->addJsFile('//ajax.googleapis.com/ajax/libs/jqueryui/1.10.3/jquery-ui.min.js', 'jquery-ui', 'jquery');
    }
}
