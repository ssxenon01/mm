<?php
class Sabai_Addon_Directory_Controller_ListingMap extends Sabai_Controller
{
    protected function _doExecute(Sabai_Context $context)
    {
        $context->addTemplate('directory_listing_map');
        $context->map_settings = $this->getAddon()->getConfig('map');
    }
}