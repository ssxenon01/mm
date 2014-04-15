<?php
abstract class Sabai_Addon_Content_Controller_FavoritePosts extends Sabai_Addon_Content_Controller_ListPosts
{
    protected $_defaultSort = 'added';
    
    protected function _getSorts(Sabai_Context $context)
    {
        return array(
            'newest' => array(
                'label' => __('Newest', 'sabai'),
                'title' => __('Sort by most recent', 'sabai'),
            ),
            'active' => array(
                'label' => __('Active', 'sabai'),
                'title' => __('Sort by most recent activity', 'sabai'),
            ),
            'added' => array(
                'label' => __('Added', 'sabai'),
                'title' => __('Sort by date added', 'sabai'),
            ),
        );
    }
    
    protected function _getBundle(Sabai_Context $context)
    {
        return null;
    }
    
    protected function _paginate(Sabai_Context $context, $sort, Sabai_Addon_Entity_Model_Bundle $bundle = null)
    {
        $gateway = $this->getModel(null, 'Content')->getGateway('Post');
        $pager = new SabaiFramework_Paginator_Custom(
            array($gateway, 'countUserFavorites'),
            array($this, 'fetchUserFavorites'),
            $this->_perPage,
            array($sort),
            array($context->identity ? $context->identity->id : $this->getUser()->id, $this->_getBundleNames($context))
        );
        return $pager->setCurrentPage($context->getRequest()->asInt(Sabai::$p, $this->_defaultPage));
    }
    
    public function fetchUserFavorites($userId, $bundles, $limit, $offset, $sort)
    {
        $entity_ids = $this->getModel(null, 'Content')->getGateway('Post')->fetchUserFavorites($userId, $bundles, $limit, $offset, $sort);
        return $this->Entity_Entities('content', $entity_ids, true, true); 
    }
    
    abstract protected function _getBundleNames(Sabai_Context $context);
}