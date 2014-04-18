<?php
class Sabai_Addon_Directory_Controller_SearchForm extends Sabai_Controller
{ 
    protected function _doExecute(Sabai_Context $context)
    {   
        if (empty($context->action_url)) {
            $addon = null;
            if (isset($context->addon)) {
                try {
                    $addon = $this->getAddon($context->addon);
                } catch (Sabai_IException $e) {
                    $this->LogError($e);
                }
            }
            if (!$addon instanceof Sabai_Addon_Directory) {
                $addon = $this->getAddon('Directory');
            }
            $context->action_url = $this->Url('/' . $addon->getDirectorySlug());
            $context->category_bundle = $addon->getCategoryBundleName();
        } else {
            $context->category_bundle = $this->Directory_DirectoryList('category');
        }
        if (!isset($context->no_loc)) {
            $context->no_loc = false;
        }
        if (!isset($context->button)) {
            $context->button = 'sabai-btn-primary';
        }
        $context->addTemplate('directory_searchform');
    }
}
