<?php
class Sabai_Addon_Taxonomy_Controller_ViewTerm extends Sabai_Addon_Entity_Controller_ViewEntity
{
    protected function _doExecute(Sabai_Context $context)
    {   
        parent::_doExecute($context);        
        $context->clearTabs()->setUrl($this->Entity_Url($context->entity));
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
    }
    
    protected function _getEntity(Sabai_Context $context)
    {
        return $context->entity;
    }
}