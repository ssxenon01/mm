<?php
class Sabai_Addon_System_Controller_Admin_Info extends Sabai_Addon_Form_Controller
{    
    protected function _doGetFormSettings(Sabai_Context $context, array &$formStorage)
    {
        $this->_cancelUrl = null;
        $this->_submitable = false;
        
        // Init variables
        $info = array(
            'php_version' => array('name' => 'PHP Version', 'value' => phpversion()),
            'php_mbstring' => array('name' => 'PHP Mbstring Extension', 'value' => function_exists('mb_detect_encoding') ? 'On' : 'Off'),
            'php_memory_limit' => array('name' => 'PHP Memory Limit', 'value' => ini_get('memory_limit')),
            'php_upload_max_filesize' => array('name' => 'PHP Upload Maximum File Size', 'value' => ini_get('upload_max_filesize')),
            'php_post_max_size' => array('name' => 'PHP POST Maximum Size', 'value' => ini_get('post_max_size')),
            'sabai_install_log' => array('name' => 'Sabai Install Log', 'value' => $this->getPlatform()->getOption('install_log')),
            'site_url' => array('name' => 'Site URL', 'value' => $this->getPlatform()->getSiteUrl()),
            'home_url' => array('name' => 'Home URL', 'value' => $this->getPlatform()->getHomeUrl()),
            'site_admin_url' => array('name' => 'Site Admin URL', 'value' => $this->getPlatform()->getSiteAdminUrl()),
            'site_path' => array('name' => 'Site Path', 'value' => $this->SitePath()),
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
