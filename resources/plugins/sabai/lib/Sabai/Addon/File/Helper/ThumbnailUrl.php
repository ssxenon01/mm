<?php
class Sabai_Addon_File_Helper_ThumbnailUrl extends Sabai_Helper
{
    public function help(Sabai $application, $fileName)
    {
        return $application->getAddon('File')->fileStorageGetThumbnailUrl($fileName);
    }
}