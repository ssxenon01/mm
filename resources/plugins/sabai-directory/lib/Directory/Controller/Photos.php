<?php
class Sabai_Addon_Directory_Controller_Photos extends Sabai_Addon_Content_Controller_ListChildPosts
{
    protected $_perPage = 6;
   
    protected function _doExecute(Sabai_Context $context)
    {
        // If a specific photo is requested, set the page to where the photo appears
        if (($current_photo_id = $context->getRequest()->asInt('photo_id'))
            && !$context->getRequest()->asInt(Sabai::$p)
            && ($current_photo = $this->Entity_Entity('content', $current_photo_id, false))
        ) {
            $newer_photo_count = $this->_createQuery($context, 'newest', $this->_getBundle($context))
                ->propertyIsOrGreaterThan('post_published', $current_photo->getTimestamp())
                ->count();
            $context->getRequest()->set(Sabai::$p, ceil($newer_photo_count/ $this->_perPage))
                ->set('sort', 'newest');
        }
        
        parent::_doExecute($context);
        
        if (!empty($context->entities)) {
            if ($current_photo_id && !empty($context->entities[$current_photo_id])) {
                $context->current_photo = $context->entities[$current_photo_id];
            } else {
                $context->current_photo = $context->entities[array_shift(array_keys($context->entities))];
            }
            $context->link_to_listing = true;
        }
    }
    
    protected function _getSorts(Sabai_Context $context)
    {
        $this->_defaultSort = $this->getAddon()->getConfig('display', 'photo_sort');
        return array(
            'newest' => array(
                'label' => __('Newest', 'sabai-directory'),
                'title' => __('Sort by most recent', 'sabai-directory'),
            ),
            'votes' => array(
                'label' => __('Votes', 'sabai-directory'),
                'title' => __('Sort by most voted', 'sabai-directory'),
            ),
        );
    }
    
    protected function _createQuery(Sabai_Context $context, $sort, Sabai_Addon_Entity_Model_Bundle $bundle = null)
    {
        $query = parent::_createQuery($context, $sort, $bundle);
        switch ($sort) {
            case 'newest':
                return $query->sortByProperty('post_published', 'DESC');
            default:
                return $query->sortByField('voting_helpful', 'DESC', 'average')
                    ->sortByField('voting_helpful', 'DESC', 'sum')
                    ->sortByProperty('post_published', 'DESC');    
        }
    }
}