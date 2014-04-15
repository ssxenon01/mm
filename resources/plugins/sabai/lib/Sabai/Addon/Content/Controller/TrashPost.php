<?php
class Sabai_Addon_Content_Controller_TrashPost extends Sabai_Addon_Form_Controller
{
    protected function _doGetFormSettings(Sabai_Context $context, array &$formStorage)
    {
        if (!$this->getUser()->hasPermission($context->entity->getBundleName() . '_manage')) {
            $bundle = $this->Entity_Bundle($context->entity);
            if (!$bundle) {
                return false;
            }
            $is_trashable = $this->getAddon($bundle->addon)
                ->contentGetContentType($bundle->name)
                ->contentTypeIsPostTrashable($context->entity, $this->getUser());
            if (!$is_trashable) {
                return false;
            }
        }
        
        $form = array();
        $form['#header'][] = sprintf(
            '<div class="sabai-warning">%s</div>',
            __('Are you sure you want to delete this post?', 'sabai')
        );
        $form['#entity'] = $context->entity;
        
        $form['type'] = array(
            '#type' => 'radios',
            '#options' => $this->Content_TrashPostOptions(),
            '#title' => __('Reason for deletion', 'sabai'),
            '#default_value' => Sabai_Addon_Content::TRASH_TYPE_SPAM,
            '#required' => true,
        );
        $form['comment'] = array(
            '#type' => 'textfield',
            '#title' => __('Comment', 'sabai'),
            '#states' => array(
                'visible' => array(
                    'input[name="type[0]"]' => array('type' => 'value', 'value' => Sabai_Addon_Content::TRASH_TYPE_OTHER),
                ),
            ),
            '#required' => array($this, 'isCommentRequired'),
        );
        
        $this->_cancelUrl = $this->Entity_Url($context->entity);
        $this->_submitButtons['submit'] = array(
            '#value' => sprintf(__('Delete %s', 'sabai'), $this->Entity_BundleLabel($this->Entity_Bundle($context->entity), true)),
            '#btn_type' => 'primary',
        );
        $this->_ajaxCancelType = 'none';
        if ($delete_target_id = $context->getRequest()->asStr('delete_target_id')) {
            $this->_ajaxOnSuccess = sprintf('function (result, target, trigger) {
  target.hide();
  jQuery("#%s").fadeTo("fast", 0, function(){jQuery(this).slideUp("medium", function(){jQuery(this).remove();});});
}', Sabai::h($delete_target_id));
        }
        
        return $form;
    }

    public function submitForm(Sabai_Addon_Form_Form $form, Sabai_Context $context)
    {
        $this->Content_TrashPosts($context->entity, $form->values['type'], @$form->values['comment']);
        $context->setSuccess($context->bundle->getPath());
    }
    
    public function isCommentRequired($form)
    {
        return $form->values['type'] == Sabai_Addon_Content::TRASH_TYPE_OTHER;
    }
}
