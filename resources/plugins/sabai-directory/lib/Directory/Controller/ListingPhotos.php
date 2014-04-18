<?php
require_once dirname(__FILE__) . '/Photos.php';

class Sabai_Addon_Directory_Controller_ListingPhotos extends Sabai_Addon_Directory_Controller_Photos
{
    protected $_template = 'directory_listing_photo_gallery', $_displayMode = 'full';
    
    protected function _doExecute(Sabai_Context $context)
    {
        if ($this->getAddon()->getConfig('display', 'no_photo_comments')) {
            $this->_displayMode = 'summary';
        }
        parent::_doExecute($context);
        $context->link_to_listing = false;
    }
    
    protected function _createQuery(Sabai_Context $context, $sort, Sabai_Addon_Entity_Model_Bundle $bundle = null)
    {
        return parent::_createQuery($context, $sort, $bundle)
            ->fieldIs('content_parent', $context->entity->getId());
    }
    
    protected function _getLinks(Sabai_Context $context, $sort, Sabai_Addon_Entity_Model_Bundle $bundle = null)
    {
        if ($this->getUser()->isAnonymous()
            || !$this->HasPermission($context->child_bundle->name . '_add')
            || !$this->getAddon()->getConfig('photo', 'max_num_photos')
        ) {
            return array();
        }
        return array(
            $this->LinkTo(
                __('Add Photos', 'sabai-directory'),
                $this->Entity_Url($context->entity, '/' . $this->getAddon()->getSlug('photos') . '/add'),
                array('icon' => 'camera'),
                array('class' => 'sabai-btn sabai-btn-small ' . $this->getAddon()->getConfig('display', 'buttons', 'photos'))
            ),
        );
    }
}
