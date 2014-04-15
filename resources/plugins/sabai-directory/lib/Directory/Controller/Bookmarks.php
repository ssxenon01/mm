<?php
class Sabai_Addon_Directory_Controller_Bookmarks extends Sabai_Addon_Content_Controller_FavoritePosts
{
    protected $_template = 'directory_bookmarks', $_displayMode = 'favorited';
    
    protected function _getSorts(Sabai_Context $context)
    {
        $sorts = parent::_getSorts($context);
        unset($sorts['active']);
        return $sorts;
    }
    
    protected function _getBundleNames(Sabai_Context $context)
    {
        return $this->getModel('Bundle', 'Entity')
            ->type_in(array('directory_listing', 'directory_listing_review', 'directory_listing_photo'))
            ->fetch()
            ->getArray('name');
    }
}