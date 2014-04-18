<?php
class Sabai_Helper_NoImageUrl extends Sabai_Helper
{
    public function help(Sabai $application, $small = false)
    {       
        $file = $small ? 'no_image_small.png' : 'no_image.png';
        return $application->getPlatform()->getAssetsUrl() . '/images/' . $file;
    }
}