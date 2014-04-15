<?php
class Sabai_Addon_Voting_Helper_RenderFavorite extends Sabai_Helper
{
    public function help(Sabai $application, Sabai_Addon_Entity_IEntity $entity, array $settings = array())
    {
        $settings += array(
            'icon' => 'star',
            'icon_size' => 'large',
        );
        $target_id = 'sabai-voting-star-favorite-' . $entity->getId();
        if ($settings['icon_size'] === 'large') {
            $class = 'sabai-icon-large';
        } else {
            $class = '';
        }
        if (!$application->getUser()->isAnonymous()) {
            $token = $application->Token('voting_vote_entity', 1800, true);
            $class .= empty($entity->data['voting_favorite_voted']) ? ' sabai-icon-' . $settings['icon'] . '-empty' : ' sabai-icon-' . $settings['icon'];
            $link = $application->LinkToRemote(
                '<i class="' . $class . '"></i>',
                '#' . $target_id,
                $application->Entity_Url($entity, '/vote/favorite/form'),
                array('url' => $application->Entity_Url($entity, '/vote/favorite', array(Sabai_Request::PARAM_TOKEN => $token, 'value' => 1)), 'no_escape' => true, 'post' => true, 'success' => 'target.find("i").toggleClass("sabai-icon-' . $settings['icon'] . '", result.value == 1).toggleClass("sabai-icon-' . $settings['icon'] . '-empty", result.value != 1).end().find("span").text(parseInt(result.sum, 10)); return false;', 'loadingImage' => false),
                array('title' => __('Mark/unmark this post as favorite (click again to undo)', 'sabai'), 'data-sabaipopover-title' => __('Mark/unmark this post as favorite', 'sabai'))
            );
        } else {
            $class .= ' sabai-icon-' . $settings['icon'] . '-empty';  
            $link = $application->LinkToRemote(
                '<i class="' . $class . '"></i>',
                '#' . $target_id,
                $application->Entity_Url($entity, '/vote/favorite/form'),
                array('url' => $application->Entity_Url($entity, '/vote/favorite', array('value' => 1)), 'no_escape' => true, 'post' => true, 'loadingImage' => false, 'errorDisableTrigger' => true),
                array('title' => __('Mark/unmark this post as favorite (click again to undo)', 'sabai'), 'data-sabaipopover-title' => __('Mark/unmark this post as favorite', 'sabai'))
            );
        }
        
        return sprintf('<span class="sabai-voting-star" id="%s">%s <span class="sabai-number">%d</span></span>', $target_id, $link, $entity->getSingleFieldValue('voting_favorite', 'count'));
    }
}