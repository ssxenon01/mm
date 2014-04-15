<?php
class Sabai_Addon_Directory_Controller_Lead extends Sabai_Controller
{ 
    protected function _doExecute(Sabai_Context $context)
    {   
        $context->setInfo(sprintf('Message by %s', $this->Content_Author($context->entity)->name))->addTemplate('directory_listing_lead_single');
    }
}