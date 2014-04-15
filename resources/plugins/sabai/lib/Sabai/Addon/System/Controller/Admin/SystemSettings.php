<?php
class Sabai_Addon_System_Controller_Admin_SystemSettings extends Sabai_Addon_Form_Controller
{    
    protected function _doGetFormSettings(Sabai_Context $context, array &$formStorage)
    {
        $this->_cancelUrl = null;
        $this->_submitButtons[] = array('#value' => __('Save Changes', 'sabai'), '#btn_type' => 'primary');
        $this->_successFlash = __('Settings saved.', 'sabai');
        
        $config = $this->getAddon()->getConfig();
        $form = array(
            'no_perms_in_session' => array(
                '#type' => 'checkbox',
                '#title' => __('Disable caching permissions in session', 'sabai'),
                '#default_value' => !empty($config['no_perms_in_session']),
            ),
        );
        if (defined('SABAI_SYSTEM_DISABLE_PERMISSIONS_IN_SESSIONS') && SABAI_SYSTEM_DISABLE_PERMISSIONS_IN_SESSIONS) {
            $form['deprecated'] = array(
                '#type' => 'markup',
                '#markup' => '<div class="sabai-warning">The constant SABAI_SYSTEM_DISABLE_PERMISSIONS_IN_SESSIONS has been deprecated. Please use the checkbox above to enable/disable caching permissions in the session instead of using the constant in wp-config.php.</div>',
            );
        }
        return $form;
    }

    public function submitForm(Sabai_Addon_Form_Form $form, Sabai_Context $context)
    {
        $this->getAddon()->saveConfig($form->values);
        $context->setSuccess($this->Url('/settings', array('refresh' => 0)));
        $this->reloadAddons();
    }
}