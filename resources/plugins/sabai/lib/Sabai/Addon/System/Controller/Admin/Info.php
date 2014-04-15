<?php
class Sabai_Addon_System_Controller_Admin_Info extends Sabai_Addon_Form_Controller
{    
    protected function _doGetFormSettings(Sabai_Context $context, array &$formStorage)
    {
        $this->_cancelUrl = null;
        $this->_submitable = false;
        
        // Init variables
        $info = array(
            'version' => array('name' => 'PHP Version', 'value' => phpversion()),
            'mbstring' => array('name' => 'PHP Mbstring Extension', 'value' => function_exists('mb_detect_encoding') ? 'On' : 'Off'),
            'memory_limit' => array('name' => 'PHP Memory Limit', 'value' => ini_get('memory_limit')),
            'upload_max_filesize' => array('name' => 'PHP Upload Maximum File Size', 'value' => ini_get('upload_max_filesize')),
            'post_max_size' => array('name' => 'PHP POST Maximum Size', 'value' => ini_get('post_max_size')),
            'sabai_install_log' => array('name' => 'Sabai Install Log', 'value' => $this->getPlatform()->getOption('install_log')),
        );
        
        // Init form
        $form = array(
            'info' => array(
                '#type' => 'tableselect',
                '#header' => array(
                    'name' => 'Name',
                    'value' => 'Value',
                ),
                '#options' => array(),
                '#disabled' => true,
            ),
        );

        foreach ($this->Filter('SystemAdminInfo', $info) as $info_key => $info_data) {
            $form['info']['#options'][$info_key] = array(
                'name' => $info_data['name'],
                'value' => $info_data['value'],
            );
        }
        
        return $form;
    }
}