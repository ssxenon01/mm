<?php
class Sabai_Addon_Directory_Controller_UserBookmarks extends Sabai_Addon_Content_Controller_FavoritePosts
{
    protected $_template = 'directory_bookmarks', $_displayMode = 'favorited';
    
    protected function _getSorts(Sabai_Context $context)
    {
        $sorts = parent::_getSorts($context);
        unset($sorts['active']);
        $this->_perPage = $this->getAddon()->getConfig('display', 'bookmark_perpage');
        $this->_defaultSort = $this->getAddon()->getConfig('display', 'bookmark_sort');
        return $sorts;
    }
    
    protected function _getBundleNames(Sabai_Context $context)
    {
        return array($this->getAddon()->getListingBundleName(), $this->getAddon()->getReviewBundleName(), $this->getAddon()->getPhotoBundleName());
    }
}