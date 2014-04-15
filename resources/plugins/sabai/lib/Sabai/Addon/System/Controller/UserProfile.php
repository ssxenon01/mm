<?php
class Sabai_Addon_System_Controller_UserProfile extends Sabai_Controller
{    
    protected function _doExecute(Sabai_Context $context)
    {
        $counts = $this->Entity_Query('content')
            ->propertyIs('post_status', Sabai_Addon_Content::POST_STATUS_PUBLISHED)
            ->propertyIs('post_user_id', $context->identity->id)
            ->groupByProperty('post_entity_bundle_name')
            ->count();
        $activity = $this->doFilter('SystemUserProfileActivity', array(), array($context->identity, $counts));
        foreach (array_keys($activity) as $key) {
            foreach ($activity[$key]['stats'] as $stat_name => $stat) {
                if (isset($stat['count'])) {
                    $count = $stat['count'];
                } elseif (isset($counts[$stat_name])) {
                    $count = $counts[$stat_name];
                } else {
                    $count = 0;
                }
                $activity[$key]['stats'][$stat_name] += array(
                    'count' => $count,
                    'formatted' => sprintf($stat['format'], '<strong>' . $count . '</strong>'),
                );
                if (isset($stat['url'])) {
                    $activity[$key]['stats'][$stat_name]['url'] = $this->Url($stat['url']);
                }
            }
        }
        $context->addTemplate('system_userprofile')
            ->setAttributes(array('activities' => $activity));
    }
}