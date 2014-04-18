<?php
class Sabai_Addon_Directory_Controller_ListingMap extends Sabai_Controller
{
    protected function _doExecute(Sabai_Context $context)
    {
        $context->addTemplate('directory_listing_map');
        $config = $this->getAddon()->getConfig();
        $context->map_settings = $config['map'];
        $context->button = $config['display']['buttons']['directions'];
        $context->country = @$config['search']['country'];
    }
}
