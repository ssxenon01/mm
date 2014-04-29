<?php
class Sabai_Addon_File_Controller_Admin_Settings extends Sabai_Addon_Form_Controller
{    
    protected function _doGetFormSettings(Sabai_Context $context, array &$formStorage)
    {
        $this->_cancelUrl = null;
        $this->_submitButtons[] = array('#value' => __('Save Changes', 'sabai'), '#btn_type' => 'primary');
        $this->_successFlash = __('Settings saved.', 'sabai');
        //$context->addTemplate('system_admin_settings');
        
        $config = $this->getAddon()->getConfig();
        return array(
            'upload_dir' => array(
                '#type' => 'textfield',
                '#default_value' => $config['upload_dir'],
                '#title' => __('File upload directory', 'sabai'),
                '#element_validate' => array(array(array($this, 'validateDir'), array('upload_dir'))),
                '#description' => sprintf(
                    __('Enter the path to a directory where uploaded files are stored. Leave blank to use the system default (%s). This directory must be writeable by the server.', 'sabai'),
                    $this->getAddon()->getVarDir('files')
                ),
            ),
            'tmp_dir' => array(
                '#type' => 'textfield',
                '#default_value' => $config['tmp_dir'],
                '#title' => __('Temporary file upload directory', 'sabai'),
                '#element_validate' => array(array(array($this, 'validateDir'), array('tmp_dir'))),
                '#description' => sprintf(
                    __('Enter the path to a directory where temporary uploaded files are stored. Leave blank to use the system default (%s). This directory must be writeable by the server.', 'sabai'),
                    $this->getAddon()->getVarDir('tmp')
                ),
            ),
            'thumbnail_dir' => array(
                '#type' => 'textfield',
                '#default_value' => $config['thumbnail_dir'],
                '#title' => __('Thumbnail directory', 'sabai'),
                '#element_validate' => array(array(array($this, 'validateDir'), array('thumbnail_dir'))),
                '#description' => sprintf(
                    __('Enter the path to a directory where thumbnail files are stored. Leave blank to use the system default (%s). This directory must be writeable by the server and accessible by the web browser.', 'sabai'),
                    $this->getAddon()->getVarDir('thumbnails')
                ),
            ),
            'thumbnail_size' => array(
                '#class' => 'sabai-form-inline',
                '#tree' => false,
                '#title' => __('Thumbnail size', 'sabai'),
                '#description' => __('Enter the dimension of thumbnail files in pixels.', 'sabai'),
                '#collapsible' => false,
                'thumbnail_width' => array(
                    '#type' => 'textfield',
                    '#size' => 5,
                    '#integer' => true,
                    '#min_value' => 0,
                    '#default_value' => $config['thumbnail_width'],
                    '#field_suffix' => ' x ',
                ),
                'thumbnail_height' => array(
                    '#type' => 'textfield',
                    '#size' => 5,
                    '#integer' => true,
                    '#min_value' => 0,
                    '#default_value' => $config['thumbnail_height'],
                ),
            ),
            'resize_method' => array(
                '#title' => __('Thumbnail resize method', 'sabai'),
                '#type' => 'radios',
                '#options' => array('crop' => __('Crop', 'sabai'), 'scale' => __('Scale', 'sabai')),
                '#default_value' => $config['resize_method'],
                '#class' => 'sabai-form-inline',
            ),
            'no_pretty_url' => array(
                '#type' => 'checkbox',
                '#title' => __('Disable pretty URLs', 'sabai'),
                '#default_value' => !empty($config['no_pretty_url']),
            ),
        );
    }
    
    public function validateDir($form, &$value, $element, $self)
    {
        $value = trim($value);
        if (!strlen($value)) {
            return;
        }
        foreach (array('upload_dir', 'thumbnail_dir', 'tmp_dir') as $dir) {
            if ($dir !== $self && $form->values[$dir] === $value) {
                $form->setError(__('The path must be different fom the other two.', 'sabai'), $element);
                return;
            }
        }
        $this->ValidateDirectory($value, true);
    }

    public function submitForm(Sabai_Addon_Form_Form $form, Sabai_Context $context)
    {
        $this->getAddon()->saveConfig($form->values);
        $context->setSuccess($this->Url('/settings', array('refresh' => 0)));
        $this->reloadAddons();
    }
}
