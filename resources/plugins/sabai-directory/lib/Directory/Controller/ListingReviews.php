<?php
require_once dirname(__FILE__) . '/Reviews.php';

class Sabai_Addon_Directory_Controller_ListingReviews extends Sabai_Addon_Directory_Controller_Reviews
{
    protected $_displayMode = 'full';
    
    protected function _getLinks(Sabai_Context $context, $sort, Sabai_Addon_Entity_Model_Bundle $bundle = null)
    {
        if (!$this->getUser()->isAnonymous() && !$this->HasPermission($context->child_bundle->name . '_add')) {
            return array();
        }
        return array(
            $this->LinkTo(
                __('Write a Review', 'sabai-directory'),
                $this->Entity_Url($context->entity, '/' . $this->getAddon()->getSlug('reviews') . '/add'),
                array('icon' => 'pencil'),
                array('class' => 'sabai-btn sabai-btn-small ' . $this->getAddon()->getConfig('display', 'buttons', 'review'))
            ),
        );
    }
    
    protected function _createQuery(Sabai_Context $context, $sort, Sabai_Addon_Entity_Model_Bundle $bundle = null)
    {
        return parent::_createQuery($context, $sort, $bundle)
            ->fieldIs('content_parent', $context->entity->getId());
    }
}
