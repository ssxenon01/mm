<?php
abstract class Sabai_Addon_Voting_Controller_FlaggedPosts extends Sabai_Addon_Content_Controller_ListPosts
{    
    protected $_bundleNames = array();
    
    protected function _doExecute(Sabai_Context $context)
    {
        foreach ($this->_getBundleNames($context) as $bundle_name) {
            if ($this->HasPermission($bundle_name . '_manage')) {
                $this->_bundleNames[] = $bundle_name;
            }
        }
        if (empty($this->_bundleNames)) {
            $context->setForbiddenError();
            return;
        }
        
        parent::_doExecute($context);
        
        $context->clearTabs();
    }
    
    protected function _getBundle(Sabai_Context $context)
    {
        return;
    }
    
    protected function _getSorts(Sabai_Context $context)
    {
        return array(
            'recent' => array(
                'label' => __('Recent', 'sabai'),
                'title' => __('Sort by most recent flags', 'sabai'),
            ),
            'score' => array(
                'label' => __('Score', 'sabai'),
                'title' => __('Sort by highest spam score', 'sabai'),
                ),
            'flags' => array(
                'label' => _x('Flags', 'sort', 'sabai'),
                'title' => __('Sort by most flagged', 'sabai'),
            ),
        );
    }
    
    protected function _createQuery(Sabai_Context $context, $sort, Sabai_Addon_Entity_Model_Bundle $bundle = null)
    {
        $query = $this->Entity_Query('content')
            ->propertyIs('post_status', Sabai_Addon_Content::POST_STATUS_PUBLISHED)
            ->propertyIsIn('post_entity_bundle_name', $this->_bundleNames)
            ->fieldIsGreaterThan('voting_flag', 0, 'count');
        switch ($sort) {
            case 'score':
                return $query->sortByField('voting_flag', 'DESC', 'sum')
                    ->sortByField('voting_flag', 'DESC', 'last_voted_at');
            case 'flags':
                return $query->sortByField('voting_flag', 'DESC', 'count')
                    ->sortByField('voting_flag', 'DESC', 'last_voted_at');
            default:
                return $query->sortByField('voting_flag', 'DESC', 'last_voted_at');
        }
    }
    
    abstract protected function _getBundleNames(Sabai_Context $context);
}
