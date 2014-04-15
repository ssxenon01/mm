<?php
class Sabai_Addon_PaidDirectoryListings_Controller_Admin_Settings extends Sabai_Addon_Form_Controller
{    
    protected function _doGetFormSettings(Sabai_Context $context, array &$formStorage)
    {
        $context->clearTabs();
        $this->_submitButtons[] = array('#value' => __('Save Changes', 'sabai-directory'), '#btn_type' => 'primary');
        $this->_successFlash = __('Settings saved.', 'sabai-directory');
        return array(
            '#tree' => true,
            'paypal' => array('#collapsed' => false) + $this->getAddon('PayPal')->getPayPalSettingsForm((array)$this->getAddon()->getConfig('paypal'), array('paypal')),
        );
    }

    public function submitForm(Sabai_Addon_Form_Form $form, Sabai_Context $context)
    {
        $this->getAddon()->saveConfig($form->values);
        $context->setSuccess($this->Url('/settings', array('refresh' => 0)));
        $this->reloadAddons();
    }
}