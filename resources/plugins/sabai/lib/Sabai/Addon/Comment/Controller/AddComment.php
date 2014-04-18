<?php
class Sabai_Addon_Comment_Controller_AddComment extends Sabai_Addon_Form_Controller
{
    protected function _doGetFormSettings(Sabai_Context $context, array &$formStorage)
    {
        $this->_cancelUrl = $this->Entity_Url($context->entity);
        $this->_submitButtons[] = array('#value' => __('Add Comment', 'sabai'));
        $this->_ajaxOnSuccess = sprintf('function (result, target, trigger) {
  target.hide();
  jQuery("#sabai-comment-comments-%1$d").append(result.comment_html).show().find("> li").last().effect("highlight", {}, 3000).sabai();
  jQuery("#sabai-comment-comments-%1$d-add").show();
}', $context->entity->getId());
        $this->_ajaxOnCancel = sprintf('function (cancel, target) {jQuery(\'#sabai-comment-comments-%d-add\').show();}', $context->entity->getId());
        $form = array(
            'body' => array(
                '#type' => 'markdown_textarea',
                '#rows' => 2,
                '#inline_elements_only' => true, // do not allow block tags
                '#required' => true,
            ),
        );
        if ($this->getUser()->isAdministrator()) {
            $form['disable_flag'] = array(
                '#type' => 'hidden',
                '#default_value' => true,
            );
        }

        return $form;
    }

    public function submitForm(Sabai_Addon_Form_Form $form, Sabai_Context $context)
    {
        $comment = $this->getModel()->create('Post')->markNew();
        $comment->entity_id = $context->entity->getId();
        $comment->entity_bundle_name = $context->entity->getBundleName();
        $comment->body = $form->values['body']['text'];
        $comment->body_html = $form->values['body']['filtered_text'];
        $comment->User = $this->getUser();
        $comment->status = Sabai_Addon_Comment::POST_STATUS_PUBLISHED;
        $comment->published_at = time();
        if (isset($form->settings['disable_vote'])) {
            $comment->vote_disabled = !empty($form->values['disable_vote']);
        }
        if (isset($form->settings['disable_flag'])) {
            $comment->flag_disabled = !empty($form->values['disable_flag']);
        }
        $this->getModel()->commit();
        
        // Update featured comments for the entity
        $this->getModel()->getGateway('Post')->updateFeaturedByEntity($context->entity->getId());
        
        $this->doEvent('CommentSubmitCommentSuccess', array($comment, /*$isEdit*/ false, $context->entity));
        $context->setSuccess($this->Entity_Url($context->entity, '', array(), 'sabai-comment-' . $comment->id))
            ->setSuccessAttributes(array(
                'comment_html' => $this->Comment_Render($comment->toArray(), $context->entity),
            ));
    }
}
