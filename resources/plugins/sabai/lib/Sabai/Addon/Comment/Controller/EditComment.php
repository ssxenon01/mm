<?php
class Sabai_Addon_Comment_Controller_EditComment extends Sabai_Addon_Form_Controller
{
    protected function _doGetFormSettings(Sabai_Context $context, array &$formStorage)
    {
        $this->_cancelUrl = $this->Entity_Url($context->entity);
        $this->_submitButtons[] = array('#value' => __('Save Changes', 'sabai'), '#btn_type' => 'primary');
        $this->_ajaxOnSuccess = sprintf('function (result, target, trigger) {
  target.hide();
  jQuery("#sabai-comment-%d").html(result.comment_html).effect("highlight", {}, 3000).sabai();
}', $context->comment->id);
        $form = array(
            'body' => array(
                '#type' => 'markdown_textarea',
                '#rows' => 2,
                '#required' => true,
                '#inline_elements_only' => true, // do not allow block tags
                '#default_value' => array('text' => $context->comment->body, 'filtered_text' => $context->comment->body_html),
            ),
        );
        if ($this->getUser()->isAdministrator()) {
            $form['disable_vote'] = array(
                '#type' => 'hidden',
                '#default_value' => $context->comment->vote_disabled,
            );
        }
        $this->_ajaxOnCancel = sprintf('function (target) {
    jQuery(\'#sabai-comment-%d\').find(\'.sabai-comment-main\').show();target.text(\'\').hide();
}', $context->comment->id);
        return $form;
    }

    public function submitForm(Sabai_Addon_Form_Form $form, Sabai_Context $context)
    {
        if (md5($form->values['body']['text']) !== md5($context->comment->body)) {
            $context->comment->body = $form->values['body']['text'];
            $context->comment->body_html = $form->values['body']['filtered_text'];
            $context->comment->edit_last_at = time();
            $context->comment->edit_last_by = $this->getUser()->id;
            $context->comment->edit_count++;
        }
        if (isset($form->settings['disable_vote'])) {
            $context->comment->vote_disabled = !empty($form->values['disable_vote']);
        }
        if (isset($form->settings['disable_flag'])) {
            $context->comment->flag_disabled = !empty($form->values['disable_flag']);
        }
        $this->getModel()->commit();
        $this->doEvent('CommentSubmitCommentSuccess', array($context->comment, /*$isEdit*/ true, $context->entity));

        $voted = $this->getModel('Vote')->postId_is($context->comment->id)->userId_is($this->getUser()->id)->count();
        $context->setSuccess($this->Entity_Url($context->entity, '', array(), 'sabai-comment-' . $context->comment->id))
            ->setSuccessAttributes(array(
                'comment_html' => $this->Comment_Render($context->comment->toArray(), $context->entity, null, $voted ? false : null, null),
            ));
    }
}
