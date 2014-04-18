<?php
class Sabai_Addon_WordPress_Controller_Admin_Settings extends Sabai_Addon_Form_Controller
{    
    protected function _doGetFormSettings(Sabai_Context $context, array &$formStorage)
    {
        $this->_cancelUrl = null;
        $this->_submitButtons[] = array('#value' => __('Save Changes', 'sabai'), '#btn_type' => 'primary');
        $this->_successFlash = __('Settings saved.', 'sabai');
        $config = $this->getAddon()->getConfig();
        return array(
            'do_user_shortcode' => array(
                '#type' => 'checkbox',
                '#title' => __('Always process shortcodes in user content', 'sabai'),
                '#default_value' => !empty($config['do_user_shortcode']),
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