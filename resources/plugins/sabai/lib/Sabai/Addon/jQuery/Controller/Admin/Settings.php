<?php
class Sabai_Addon_jQuery_Controller_Admin_Settings extends Sabai_Addon_Form_Controller
{    
    protected function _doGetFormSettings(Sabai_Context $context, array &$formStorage)
    {
        $this->_cancelUrl = null;
        $this->_submitButtons[] = array('#value' => __('Save Changes', 'sabai'), '#btn_type' => 'primary');
        $this->_successFlash = __('Settings saved.', 'sabai');
        
        $config = $this->getAddon()->getConfig();
        return array(
            'no_conflict' => array(
                '#type' => 'checkbox',
                '#default_value' => !empty($config['no_conflict']),
                '#title' => __('Issue jQuery.noConflict() on startup', 'sabai'),
            ),
            'no_ui_css' => array(
                '#type' => 'checkbox',
                '#default_value' => !empty($config['no_ui_css']),
                '#title' => __('Do not load jQuery UI CSS', 'sabai'),
                '#description' => __('Check this if jQuery UI CSS being loaded by Sabai is causing conflict with other plugins or themes.', 'sabai'),
            ),
        );
    }

    public function submitForm(Sabai_Addon_Form_Form $form, Sabai_Context $context)
    {
        $this->getAddon()->saveConfig($form->values);
        $context->setSuccess($this->Url('/settings', array('refresh' => 0)));
        $this->reloadAddons();
    }
}