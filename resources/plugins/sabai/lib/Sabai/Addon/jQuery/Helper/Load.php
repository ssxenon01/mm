<?php
class Sabai_Addon_jQuery_Helper_Load extends Sabai_Helper
{
    /**
     * Loads jQuery script file
     * @param Sabai $application
     * @param Sabai_WebResponse
     */
    public function help(Sabai $application, Sabai_WebResponse $response)
    {
        $response->addJsFile('//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js', 'jquery');
    }
}
