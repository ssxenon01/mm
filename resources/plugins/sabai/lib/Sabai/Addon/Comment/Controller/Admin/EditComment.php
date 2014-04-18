<?php
class Sabai_Addon_Comment_Controller_Admin_EditComment extends Sabai_Addon_Form_Controller
{
    protected function _doGetFormSettings(Sabai_Context $context, array &$formStorage)
    {
        $this->_submitButtons[] = array('#value' => __('Save Changes', 'sabai'), '#btn_type' => 'primary');
        $form = array(
            'body' => array(
                '#type' => 'markdown_textarea',
                '#rows' => 5,
                '#required' => true,
                '#inline_elements_only' => true, // do not allow block tags
                '#default_value' => array('text' => $context->comment->body, 'filtered_text' => $context->comment->body_html),
            ),
        );
        $form['disable_vote'] = array(
            '#type' => 'checkbox',
            '#title' => __('Disable voting on this comment', 'sabai'),
            '#default_value' => $context->comment->vote_disabled,
        );
        $form['disable_flag'] = array(
            '#type' => 'checkbox',
            '#title' => __('Disable flagging this comment', 'sabai'),
            '#default_value' => $context->comment->flag_disabled,
        );
        return $form;
    }

    public function submitForm(Sabai_Addon_Form_Form $form, Sabai_Context $context)
    {
        $context->comment->body = $form->values['body']['text'];
        $context->comment->body_html = $form->values['body']['filtered_text'];
        $context->comment->vote_disabled = !empty($form->values['disable_vote']);
        $context->comment->flag_disabled = !empty($form->values['disable_flag']);
        $this->getModel()->commit();
        $this->doEvent('CommentSubmitCommentSuccess', array($context->comment, /*$isEdit*/ true));
    }
}