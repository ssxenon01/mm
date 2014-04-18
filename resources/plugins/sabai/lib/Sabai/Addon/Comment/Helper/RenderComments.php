<?php
class Sabai_Addon_Comment_Helper_RenderComments extends Sabai_Helper
{
    public function help(Sabai $application, Sabai_Addon_Entity_IEntity $entity, $containerId)
    {
        if (empty($entity->data['comment_count'])) {
            return '<ul id="sabai-comment-comments-' . $entity->getId() . '" class="sabai-comment-comments" style="display:none;"></ul>'
                . PHP_EOL . '<div class="sabai-comment-form sabai-comment-form-new"></div>'
                . PHP_EOL . $this->_getCommentsActionLinks($application, $entity, 0, $containerId, false);
        }

        if ($application->HasPermission($entity->getBundleName() . '_comment_vote')) {
            $vote_token = $application->Token('comment_vote_comment', 1800, true);
        } else {
            $vote_token = null;
        }
        $comments_voted = empty($entity->data['comment_comments_voted']) ? array() : $entity->data['comment_comments_voted'];

        $li = array();
        if (!empty($entity->data['comment_comments'])) {
            $comments = $entity->data['comment_comments'];
            usort($comments, array($this, '_sortByTimestamp')); // sort comments by chronological order
            $parent_entity = $application->Content_ParentPost($entity, false);
            foreach (array_keys($comments) as $key) {
                if (in_array($comments[$key]['id'], $comments_voted)) {
                    $_vote_token = false;
                } else {
                    $_vote_token = $vote_token;
                }           
                $li[] = $application->Comment_Render($comments[$key], $entity, $parent_entity, $_vote_token);
            }
        }
        $ret = array();
        $ret[] = '<ul id="sabai-comment-comments-' . $entity->getId() . '" class="sabai-comment-comments">';
        $ret[] = implode(PHP_EOL, $li);
        $ret[] = '</ul>';
        $ret[] = '<div class="sabai-comment-form sabai-comment-form-new"></div>';
        $ret[] = $this->_getCommentsActionLinks($application, $entity, $entity->data['comment_count'] - count($comments), $containerId, empty($entity->data['comment_comments']));
        
        return implode(PHP_EOL, $ret);
    }
    
    private function _getCommentsActionLinks(Sabai $application, Sabai_Addon_Entity_IEntity $entity, $moreCommentCount, $containerId, $hidden = false)
    {
        $links = array();
        if ($moreCommentCount) {
            $more_text = $hidden // Are all comments hidden?
                ? _n('%d hidden comment', '%d hidden comments', $moreCommentCount, 'sabai')
                : _n('%d more comment', '%d more comments', $moreCommentCount, 'sabai');
            $links[] = $application->LinkToRemote(
                sprintf($more_text, $moreCommentCount),
                '#sabai-comment-comments-' . $entity->getId(),
                $application->Entity_Url($entity, '/comments'),
                array('content' => 'trigger.closest("li").remove();', 'scroll' => true, 'replace' => true),
                array('title' => __('Expand to show all comments on this post', 'sabai'))
            );
        }
        if (!$application->getUser()->isAnonymous()) {
            if ($application->HasPermission($entity->getBundleName() . '_comment_add')
                || $entity->getAuthorId() === $application->getUser()->id // Owner of entity can always add comment
            ) {
                $links[] = $application->LinkToRemote(
                    __('add comment', 'sabai'),
                    '#' . $containerId . ' .sabai-comment-form-new',
                    $application->Entity_Url($entity, '/comments/add'),
                    array('slide' => true, 'content' => 'target.focusFirstInput(); trigger.hide();'),
                    array('title' => __('Add a new comment', 'sabai'), 'id' => 'sabai-comment-comments-' . $entity->getId() .'-add')
                );
            }
        } else {
            if ($application->getAddon('Comment')->getConfig('show_login_link')) {
                $links[] = sprintf(
                    __('<a href="%s">Login</a> to post comments', 'sabai'),
                    $application->LoginUrl($application->Entity_Url($entity, '/comments/add'))
                );
            }
        }
        
        return empty($links) ? '' : '<ul class="sabai-comment-comments-actions"><li>' . implode('</li><li>', $links) . '</li></ul>';
    }
    
    private static function _sortByTimestamp($a, $b)
    {
        return $a['published_at'] < $b['published_at'] ? -1 : 1;
    }
}
