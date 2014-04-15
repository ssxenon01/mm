<?php
class Sabai_Helper_LoadJs extends Sabai_Helper
{
    /**
     * Loads javscript file
     * @param Sabai $application
     * @param Sabai_WebResponse
     * @param string $fileUrl
     * @param string $handle
     * @param string|array $dependency
     */
    public function help(Sabai $application, Sabai_WebResponse $response, $fileUrl, $handle, $dependency = null)
    {
        $response->addJsFile($fileUrl, $handle, $dependency);
    }
}