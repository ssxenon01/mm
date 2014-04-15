<?php
class Sabai_Addon_Content_Controller_ListChildPosts extends Sabai_Addon_Content_Controller_ListPosts
{    
    protected function _getBundle(Sabai_Context $context)
    {
        return $context->child_bundle;
    }
}