<?php
class Sabai_Addon_Directory_Controller_Reviews extends Sabai_Addon_Content_Controller_ListChildPosts
{    
    protected function _getSorts(Sabai_Context $context)
    {
        $this->_perPage = $this->getAddon()->getConfig('display', 'review_perpage');
        $this->_defaultSort = $this->getAddon()->getConfig('display', 'review_sort');
        return array(
            'newest' => array(
                'label' => __('Newest', 'sabai-directory'),
                'title' => __('Sort by most recent', 'sabai-directory'),
            ),
            'rating' => array(
                'label' => __('Rating', 'sabai-directory'),
                'title' => __('Sort by highest rated', 'sabai-directory'),
            ),
            'helpfulness' => array(
                'label' => __('Helpfulness', 'sabai-directory'),
                'title' => __('Sort by most helpful', 'sabai-directory'),
            ),
        );
    }
    
    protected function _createQuery(Sabai_Context $context, $sort, Sabai_Addon_Entity_Model_Bundle $bundle = null)
    {
        $query = parent::_createQuery($context, $sort, $bundle);
        switch ($sort) {
            case 'newest':
                return $query->sortByProperty('post_published', 'DESC');
            case 'rating':
                return $query->sortByField('directory_rating', 'DESC')
                    ->sortByProperty('post_published', 'DESC');
            default:
                return $query->sortByField('voting_helpful', 'DESC', 'average')
                    ->sortByField('voting_helpful', 'DESC', 'sum')
                    ->sortByProperty('post_published', 'DESC');    
        }
    }
}