<?php
class Sabai_Addon_Directory_Controller_ListingTab extends Sabai_Controller
{
    protected function _doExecute(Sabai_Context $context)
    {
        $context->addTemplate('directory_listing_tab')
            ->addTemplate('directory_listing_tab_' . $context->tab_name);
    }
}