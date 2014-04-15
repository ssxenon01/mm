<?php
class Sabai_Addon_System_Controller_Admin_Settings extends Sabai_Addon_Form_Controller
{
    protected function _doExecute(Sabai_Context $context)
    {        
        parent::_doExecute($context);
        if ($context->isSuccess()) {
            $context->setSuccess($this->Url('/settings', array('refresh' => 0)));
            $this->reloadAddons();
        }
    }
    
    protected function _doGetFormSettings(Sabai_Context $context, array &$formStorage)
    {
        $this->_cancelUrl = null;
        $this->_submitButtons[] = array('#value' => __('Save Changes', 'sabai'), '#btn_type' => 'primary');
        $this->_successFlash = __('Settings saved.', 'sabai');
        //$context->addTemplate('system_admin_settings');
        
        return array();
    }

    public function submitForm(Sabai_Addon_Form_Form $form, Sabai_Context $context)
    {

    }
}