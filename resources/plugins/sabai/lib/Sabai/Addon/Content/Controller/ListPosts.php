<?php
class Sabai_Addon_Content_Controller_ListPosts extends Sabai_Addon_Entity_Controller_ListEntities
{    
    final protected function _getEntityType(Sabai_Context $context)
    {
        return 'content';
    }

    protected function _getBundle(Sabai_Context $context)
    {
        return $context->bundle;
    }
    
    protected function _getSorts(Sabai_Context $context)
    {
        return array();
    }

    protected function _getLinks(Sabai_Context $context, $sort, Sabai_Addon_Entity_Model_Bundle $bundle = null)
    {
        return array();
    }

    protected function _getUrlParams(Sabai_Context $context, Sabai_Addon_Entity_Model_Bundle $bundle = null)
    {
        return array();
    }
    
    protected function _createQuery(Sabai_Context $context, $sort, Sabai_Addon_Entity_Model_Bundle $bundle = null)
    {
        return $this->Entity_Query('content')
            ->propertyIs('post_entity_bundle_name', $bundle->name)
            ->propertyIs('post_status', Sabai_Addon_Content::POST_STATUS_PUBLISHED);
    }
}