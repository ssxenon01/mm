<?php
class Sabai_Helper_LoadCss extends Sabai_Helper
{
    /**
     * Loads css file
     * @param Sabai $application
     * @param Sabai_WebResponse
     * @param string $fileUrl
     * @param string $handle
     * @param string $version
     * @param string $media
     */
    public function help(Sabai $application, Sabai_WebResponse $response, $fileUrl, $handle, $version = null, $media = 'all')
    {
        $response->addCssFile($fileUrl, $handle, $media);
    }
}