<?php
abstract class Sabai_Addon_Entity_Helper_RenderActivity extends Sabai_Helper
{
    protected $_classPrefix = 'sabai-entity-';
    
    public function help(Sabai $application, Sabai_Addon_Entity_Entity $entity, array $settings = array())
    {
        $settings += array(
            'action_label' => __('%s posted %s', 'sabai'),
            'show_last_active' => true,
            'show_last_edited' => false,
            'last_edited_label' => __('last edited %s', 'sabai'),
            'last_active_label' => __('last active %s', 'sabai'),
            'permalink' => true,
        );
        $values = $this->_getEntityActivity($application, $entity);
        $entity_timestamp = $entity->getTimestamp();
        $datediff = $application->DateDiff($entity_timestamp);
        $datetime = $application->DateTime($entity_timestamp);
        if ($settings['permalink']) {
            $date = sprintf('<a href="%s" title="%s">%s</a>', $application->Entity_Url($entity), $datetime, $datediff);
        } else {
            $date = '<span title="' . $datetime . '">' . $datediff . '</span>';
        }
        $li = array(
            sprintf($settings['action_label'], $application->UserIdentityLinkWithThumbnailSmall($this->_getEntityAuthor($application, $entity)), $date),
        );
        if ($settings['show_last_active']) {
            if (!empty($values[0]['active_at']) && $values[0]['active_at'] != $entity_timestamp) {
                $li[] = '<i class="sabai-icon-time"></i>' . sprintf($settings['last_active_label'], $application->DateDiff($values[0]['active_at']));
            }
        }
        if ($settings['show_last_edited']) {
            if (!empty($values[0]['edited_at']) && $values[0]['edited_at'] != $entity_timestamp) {
                $li[] = '<i class="sabai-icon-time"></i>' . sprintf($settings['last_edited_label'], $application->DateDiff($values[0]['edited_at']));
            }
        }
        
        return '<ul class="'. $this->_classPrefix .'activity"><li>' . implode('</li><li>', $li) . '</li></ul>';
    }
    
    abstract protected function _getEntityActivity(Sabai $application, Sabai_Addon_Entity_Entity $entity);
    abstract protected function _getEntityAuthor(Sabai $application, Sabai_Addon_Entity_Entity $entity);
}