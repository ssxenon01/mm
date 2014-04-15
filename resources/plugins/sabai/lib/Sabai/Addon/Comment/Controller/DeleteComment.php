<?php
class Sabai_Addon_Comment_Controller_DeleteComment extends Sabai_Addon_Form_Controller
{
    protected function _doGetFormSettings(Sabai_Context $context, array &$formStorage)
    {        
        $this->_cancelUrl = $this->Entity_Url($context->entity);
        $this->_submitButtons[] = array('#value' => __('Delete Comment', 'sabai'));
        $this->_ajaxOnSuccess = sprintf('function (result, target, trigger) {
  target.hide();
  jQuery("#sabai-comment-%1$d").fadeTo("fast", 0, function(){jQuery(this).slideUp("medium", function(){jQuery(this).remove();});});
}', $context->comment->id);
        $form = array();
        $form['#header'][] = sprintf(
            '<div class="sabai-warning">%s</div>',
            __('Are you sure you want to <em>permanently</em> delete this comment? This cannot be undone.', 'sabai')
        );

        return $form;
    }

    public function submitForm(Sabai_Addon_Form_Form $form, Sabai_Context $context)
    {
        $comment_is_featured = $context->comment->status == Sabai_Addon_Comment::POST_STATUS_FEATURED;
        $context->comment->markRemoved();
        $context->comment->commit();    
        if ($comment_is_featured) {
            // Update featured comments for the entity
            $this->getModel()->getGateway('Post')->updateFeaturedByEntity($context->entity->getId());
        }       
        $this->doEvent('CommentDeleteCommentSuccess', array($context->comment));
        $context->setSuccess($this->Entity_Url($context->entity));
    }
}