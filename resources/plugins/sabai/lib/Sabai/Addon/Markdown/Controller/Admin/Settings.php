<?php
class Sabai_Addon_Markdown_Controller_Admin_Settings extends Sabai_Addon_Form_Controller
{    
    protected function _doGetFormSettings(Sabai_Context $context, array &$formStorage)
    {
        $this->_cancelUrl = null;
        $this->_submitButtons[] = array('#value' => __('Save Changes', 'sabai'), '#btn_type' => 'primary');
        $this->_successFlash = __('Settings saved.', 'sabai');
        
        $config = $this->getAddon()->getConfig();
        return array(
            'help' => array(
                '#type' => 'checkbox',
                '#title' => __('Enable markdown help', 'sabai'),
                '#default_value' => !empty($config['help']),
            ),
            'help_url' => array(
                '#type' => 'textfield',
                '#url' => true,
                '#default_value' => $config['help_url'],
                '#title' => __('Help URL', 'sabai'),
                '#description' => __('Enter the URL of a page that will open up in a popup window when the help button on the Markdown editor is clicked.', 'sabai'),
                '#states' => array(
                    'visible' => array(
                        'input[name="help[]"]' => array(
                            'type' => 'checked',
                            'value' => true,
                        ),
                    ),
                ),
            ),
            'help_window' => array(
                '#class' => 'sabai-form-inline',
                '#description' => __('Enter the dimension of the popup help window in pixels.', 'sabai'),
                '#collapsible' => false,
                'width' => array(
                    '#type' => 'textfield',
                    '#default_value' => $config['help_window']['width'],
                    '#size' => 4,
                    '#integer' => true,
                    '#title' => __('Help window dimension:', 'sabai'),
                    '#field_suffix' => 'x',
                ),
                'height' => array(
                    '#type' => 'textfield',
                    '#default_value' => $config['help_window']['height'],
                    '#size' => 4,
                    '#integer' => true,
                ),
                '#states' => array(
                    'visible' => array(
                        'input[name="help[]"]' => array(
                            'type' => 'checked',
                            'value' => true,
                        ),
                    ),
                ),
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