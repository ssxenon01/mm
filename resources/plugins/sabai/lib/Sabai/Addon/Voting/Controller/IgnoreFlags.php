<?php
class Sabai_Addon_Voting_Controller_IgnoreFlags extends Sabai_Addon_Form_Controller
{    
    protected function _doGetFormSettings(Sabai_Context $context, array &$formStorage)
    {
        $form = array();
        $form['#header'][] = '<div class="sabai-warning">'. __('Are you sure you want to ignore all the flags submitted?', 'sabai') .'</div>';
        $form['#entity'] = $context->entity;
        
        $this->_cancelUrl = $this->Entity_Url($context->entity);
        $this->_submitButtons['submit'] = array(
            '#value' => __('Ignore Flags', 'sabai'),
            '#btn_type' => 'primary',
        );
        $this->_ajaxCancelType = 'none';
        $this->_ajaxOnSuccess = sprintf('function (result, target, trigger) {
    target.hide();
    jQuery("#sabai-entity-content-%d").fadeTo("fast", 0, function(){jQuery(this).slideUp("medium", function(){jQuery(this).remove();});});
}', $context->entity->getId());
        
        return $form;
    }
    
    public function submitForm(Sabai_Addon_Form_Form $form, Sabai_Context $context)
    {
        $this->Voting_DeleteVotes($context->entity, 'flag');
    }
}
