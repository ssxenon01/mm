<?php
class Sabai_Addon_Content_Controller_ViewPost extends Sabai_Addon_Entity_Controller_ViewEntity
{    
    protected function _doExecute(Sabai_Context $context)
    {
        if (!isset($this->_template)) {
            $bundle_type = $context->entity->getBundleType();
            if ($context->entity->isFeatured()) {
                $this->_template = array($bundle_type . '_single_full', $bundle_type . '_single_full_featured');
            } else {
                $this->_template = $bundle_type . '_single_full';
            }
        }
        
        parent::_doExecute($context);
        
        // Increment view count
        $this->Content_IncrementPostView($context->entity, true);
        
        $context->setTitle($this->Filter('ContentTitle', $context->entity->getTitle(), array($context->entity->getBundleName())))
            ->setUrl($this->Entity_Url($context->entity));
        // Set HTML head title if a custom field named field_meta_title exists and its value is a valid string 
        if (isset($context->entity->field_meta_title)
            && ($meta_title = $context->entity->getSingleFieldValue('field_meta_title'))
        ) {
            $context->setHtmlHeadTitle((string)$meta_title);
        }
        // Set meta description if a custom field named field_meta_description exists and its value is a valid string, otherwise auto-generate description
        if (isset($context->entity->field_meta_description)
            && ($meta_description = $context->entity->getSingleFieldValue('field_meta_description'))
        ) {
            $context->setSummary((string)$meta_description);
        } else {
            $context->setSummary($context->entity->getSummary(100), '');
        }
        
        // Allow other scripts to access the current post being viewed
        $GLOBALS['sabai_content_entity'] = $context->entity;
    }
    
    protected function _getEntity(Sabai_Context $context)
    {
        return $context->entity;
    }
}