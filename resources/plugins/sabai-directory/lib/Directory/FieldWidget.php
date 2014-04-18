<?php
class Sabai_Addon_Directory_FieldWidget implements Sabai_Addon_Field_IWidget
{
    private $_addon, $_name;

    public function __construct(Sabai_Addon_Directory $addon, $name)
    {
        $this->_addon = $addon;
        $this->_name = $name;
    }

    public function fieldWidgetGetInfo($key = null)
    {
        switch ($this->_name) {
            case 'directory_rating':
                $info = array(
                    'label' => __('Rating Stars', 'sabai-directory'),
                    'field_types' => array('directory_rating'),
                    'default_settings' => array(),
                    'disable_edit_required' => true,
                );
                break;
            case 'directory_contact':
                $info = array(
                    'label' => __('Contact Info', 'sabai-directory'),
                    'field_types' => array('directory_contact'),
                    'default_settings' => array(
                        'require_phone' => false,
                        'require_mobile' => false,
                        'require_fax' => false,
                        'require_email' => false,
                        'require_website' => false,
                        'hide_phone_field' => false,
                        'hide_mobile_field' => false,
                        'hide_fax_field' => false,
                        'hide_email_field' => false,
                        'hide_website_field' => false,
                        'autopopulate_email' => false,
                        'autopopulate_website' => false,
                    ),
                    'disable_edit_required' => true,
                    'is_fieldset' => true,
                );
                break;
            case 'directory_social':
                $info = array(
                    'label' => __('Social Accounts', 'sabai-directory'),
                    'field_types' => array('directory_social'),
                    'default_settings' => array(
                        'require_twitter' => false,
                        'require_facebook' => false,
                        'require_googleplus' => false,
                        //'require_pinterest' => false,
                    ),
                    'disable_edit_required' => true,
                    'enable_edit_disabled' => true,
                    'is_fieldset' => true,
                );
                break;
            case 'directory_claim':
                $info = array(
                    'label' => __('Listing Owners', 'sabai-directory'),
                    'field_types' => array('directory_claim'),
                    'default_settings' => array(
                    ),
                );
                break;
        }
        return isset($key) ? @$info[$key] : $info;
    }

    public function fieldWidgetGetSettingsForm($fieldType, array $fieldSettings, array $settings, array $parents = array())
    {
        switch ($this->_name) {
            case 'directory_contact':
                return array(
                    'hide_phone_field' => array(
                        '#type' => 'checkbox',
                        '#title' => __('Disable phone number field', 'sabai-directory'),
                        '#default_value' => !empty($settings['hide_phone_field']),
                    ),
                    'hide_email_field' => array(
                        '#type' => 'checkbox',
                        '#title' => __('Disable e-mail field', 'sabai-directory'),
                        '#default_value' => !empty($settings['hide_email_field']),
                    ),
                    'hide_mobile_field' => array(
                        '#type' => 'checkbox',
                        '#title' => __('Disable mobile number field', 'sabai-directory'),
                        '#default_value' => !empty($settings['hide_mobile_field']),
                    ),
                    'hide_fax_field' => array(
                        '#type' => 'checkbox',
                        '#title' => __('Disable fax number field', 'sabai-directory'),
                        '#default_value' => !empty($settings['hide_fax_field']),
                    ),
                    'hide_website_field' => array(
                        '#type' => 'checkbox',
                        '#title' => __('Disable website field', 'sabai-directory'),
                        '#default_value' => !empty($settings['hide_website_field']),
                    ),
                    'require_phone' => array(
                        '#type' => 'checkbox',
                        '#title' => __('Require phone number', 'sabai-directory'),
                        '#default_value' => !empty($settings['require_phone']),
                        '#states' => array(
                            'visible' => array(
                                sprintf('input[name="%s[hide_phone_field][]"]', $this->_addon->getApplication()->Form_FieldName($parents)) => array(
                                    'type' => 'checked',
                                    'value' => false,
                                ),
                            ),
                        ),
                    ),
                    'require_mobile' => array(
                        '#type' => 'checkbox',
                        '#title' => __('Require mobile number', 'sabai-directory'),
                        '#default_value' => !empty($settings['require_mobile']),
                        '#states' => array(
                            'visible' => array(
                                sprintf('input[name="%s[hide_mobile_field][]"]', $this->_addon->getApplication()->Form_FieldName($parents)) => array(
                                    'type' => 'checked',
                                    'value' => false,
                                ),
                            ),
                        ),
                    ),
                    'require_fax' => array(
                        '#type' => 'checkbox',
                        '#title' => __('Require fax number', 'sabai-directory'),
                        '#default_value' => !empty($settings['require_fax']),
                        '#states' => array(
                            'visible' => array(
                                sprintf('input[name="%s[hide_fax_field][]"]', $this->_addon->getApplication()->Form_FieldName($parents)) => array(
                                    'type' => 'checked',
                                    'value' => false,
                                ),
                            ),
                        ),
                    ),
                    'require_email' => array(
                        '#type' => 'checkbox',
                        '#title' => __('Require e-mail address', 'sabai-directory'),
                        '#default_value' => !empty($settings['require_email']),
                        '#states' => array(
                            'visible' => array(
                                sprintf('input[name="%s[hide_email_field][]"]', $this->_addon->getApplication()->Form_FieldName($parents)) => array(
                                    'type' => 'checked',
                                    'value' => false,
                                ),
                            ),
                        ),
                    ),
                    'require_website' => array(
                        '#type' => 'checkbox',
                        '#title' => __('Require website URL', 'sabai-directory'),
                        '#default_value' => !empty($settings['require_website']),
                        '#states' => array(
                            'visible' => array(
                                sprintf('input[name="%s[hide_website_field][]"]', $this->_addon->getApplication()->Form_FieldName($parents)) => array(
                                    'type' => 'checked',
                                    'value' => false,
                                ),
                            ),
                        ),
                    ),
                    'autopopulate_email' => array(
                        '#type' => 'checkbox',
                        '#title' => __("Auto-populate e-mail address field with the current user's e-mail address", 'sabai-directory'),
                        '#default_value' => !empty($settings['autopopulate_email']),
                        '#states' => array(
                            'visible' => array(
                                sprintf('input[name="%s[hide_email_field][]"]', $this->_addon->getApplication()->Form_FieldName($parents)) => array(
                                    'type' => 'checked',
                                    'value' => false,
                                ),
                            ),
                        ),
                    ),
                    'autopopulate_website' => array(
                        '#type' => 'checkbox',
                        '#title' => __("Auto-populate website URL field with the current user's website URL", 'sabai-directory'),
                        '#default_value' => !empty($settings['autopopulate_website']),
                        '#states' => array(
                            'visible' => array(
                                sprintf('input[name="%s[hide_website_field][]"]', $this->_addon->getApplication()->Form_FieldName($parents)) => array(
                                    'type' => 'checked',
                                    'value' => false,
                                ),
                            ),
                        ),
                    ),
                );
            case 'directory_social':
                return array(
                    'require_twitter' => array(
                        '#type' => 'checkbox',
                        '#title' => __('Require Twitter username', 'sabai-directory'),
                        '#default_value' => !empty($settings['require_twitter']),
                    ),
                    'require_facebook' => array(
                        '#type' => 'checkbox',
                        '#title' => __('Require Facebook URL', 'sabai-directory'),
                        '#default_value' => !empty($settings['require_facebook']),
                    ),
                    'require_googleplus' => array(
                        '#type' => 'checkbox',
                        '#title' => __('Require Google+ URL', 'sabai-directory'),
                        '#default_value' => !empty($settings['require_googleplus']),
                    ),
                    //'require_pinterest' => array(
                    //    '#type' => 'checkbox',
                    //    '#title' => __('Require Pinterest username', 'sabai-directory'),
                    //    '#default_value' => !empty($settings['require_pinterest']),
                    //),
                );
            default:
                return array();
        }
    }

    public function fieldWidgetGetForm(Sabai_Addon_Field_IField $field, array $settings, $value = null, array $parents = array())
    {
        switch ($this->_name) {
            case 'directory_rating':
                return array(
                    '#type' => 'voting_rateit',
                    '#rateit_min' => 0,
                    '#rateit_max' => 5,
                    '#step' => 0.5,
                    '#default_value' => isset($value) ? $value : null,
                );
            case 'directory_contact':
                $form = array(
                    '#type' => 'fieldset',
                );
                if (!$settings['hide_phone_field']) {
                    $form['phone'] = array(
                        '#type' => 'textfield',
                        '#default_value' => isset($value) ? $value['phone'] : null,
                        '#title' => __('Phone Number', 'sabai-directory'),
                        '#max_length' => 50,
                        '#size' => 40,
                        '#required' => !empty($settings['require_phone']),
                        '#weight' => 1,
                    );
                }
                if (!$settings['hide_email_field']) {
                    $form['email'] = array(
                        '#type' => 'textfield',
                        '#title' => __('E-mail', 'sabai-directory'),
                        '#default_value' => isset($value) ? $value['email'] : null,
                        '#email' => true,
                        '#max_length' => 100,
                        '#size' => 40,
                        '#required' => !empty($settings['require_email']),
                        '#weight' => 15,
                        '#auto_populate' => empty($settings['autopopulate_email']) ? null : 'email',
                    );
                }
                if (!$settings['hide_website_field']) {
                    $form['website'] = array(
                        '#type' => 'textfield',
                        '#title' => __('Website', 'sabai-directory'),
                        '#default_value' => isset($value) ? $value['website'] : null,
                        '#url' => true,
                        '#required' => !empty($settings['require_website']),
                        '#weight' => 20,
                        '#auto_populate' => empty($settings['autopopulate_website']) ? null : 'url',
                    );
                }
                if (!$settings['hide_mobile_field']) {
                    $form['mobile'] = array(
                        '#type' => 'textfield',
                        '#title' => __('Mobile Number', 'sabai-directory'),
                        '#default_value' => isset($value) ? $value['mobile'] : null,
                        '#max_length' => 50,
                        '#size' => 40,
                        '#required' => !empty($settings['require_mobile']),
                        '#weight' => 5,
                    );
                }
                if (!$settings['hide_fax_field']) {
                    $form['fax'] = array(
                        '#type' => 'textfield',
                        '#title' => __('Fax Number', 'sabai-directory'),
                        '#default_value' => isset($value) ? $value['fax'] : null,
                        '#max_length' => 50,
                        '#size' => 40,
                        '#required' => !empty($settings['require_fax']),
                        '#weight' => 10,
                    );
                }
                return $form;
                
            case 'directory_social':
                return array(
                    '#type' => 'fieldset',
                    'twitter' => array(
                        '#type' => 'textfield',
                        '#default_value' => isset($value) ? $value['twitter'] : null,
                        '#regex' => '/^(\w){1,15}$/',
                        '#title' => '<i class="sabai-icon-twitter-sign"></i> ' . __('Twitter', 'sabai-directory'),
                        '#title_no_escape' =>true,
                        '#size' => 30,
                        '#min_length' => 1,
                        '#max_length' => 15,
                        '#required' => !empty($settings['require_twitter']),
                    ),
                    'facebook' => array(
                        '#type' => 'textfield',
                        '#title' => '<i class="sabai-icon-facebook-sign"></i> ' . __('Facebook URL', 'sabai-directory'),
                        '#default_value' => isset($value) ? $value['facebook'] : null,
                        '#url' => true,
                        '#title_no_escape' =>true,
                        '#required' => !empty($settings['require_facebook']),
                    ),
                    'googleplus' => array(
                        '#type' => 'textfield',
                        '#title' => '<i class="sabai-icon-google-plus-sign"></i> ' . __('Google+ URL', 'sabai-directory'),
                        '#default_value' => isset($value) ? $value['googleplus'] : null,
                        '#url' => true,
                        '#title_no_escape' =>true,
                        '#required' => !empty($settings['require_googleplus']),
                    ),
                    //'pinterest' => array(
                    //    '#type' => 'textfield',
                    //    '#default_value' => isset($value) ? $value['pinterest'] : null,
                    //    '#regex' => '/^(\w){1,15}$/',
                    //    '#title' => '<i class="sabai-icon-pinterest-sign"></i> ' . __('Pinterest', 'sabai-directory'),
                    //    '#title_no_escape' =>true,
                    //    '#size' => 30,
                    //    '#min_length' => 1,
                    //    '#max_length' => 15,
                    //    '#required' => !empty($settings['require_pinterest']),
                    //),
                );
            case 'directory_claim':
                return array(
                    '#type' => 'fieldset',
                    'claimed_by' => array(
                        '#type' => 'autocomplete_user',
                        '#default_value' => isset($value['claimed_by']) ? array($value['claimed_by'] => $value['claimed_by']) : null,
                        '#multiple' => false,
                    ),
                    'expires_at' => array(
                        '#field_prefix' => sprintf('<strong><i class="sabai-icon-calendar"></i> %s</strong>', __('Expires on:', 'sabai-directory')),
                        '#type' => 'date_datepicker',
                        '#default_value' => !empty($value['expires_at']) ? $value['expires_at'] : null,
                    ),
                );
        }
    }
    
    public function fieldWidgetGetPreview(Sabai_Addon_Field_IField $field, array $settings)
    {
        switch ($this->_name) {
            case 'directory_contact':
                $required_label = '<span class="sabai-fieldui-widget-required">*</span>';
                return sprintf(
                    '<div%s>
    <div class="sabai-fieldui-widget-label">%s%s</div>
    <div><input type="textfield" disabled="disabled" size="40" /></div>
</div>
<div%s>
    <div class="sabai-fieldui-widget-label">%s%s</div>
    <div><input type="textfield" disabled="disabled" size="40" /></div>
</div>
<div%s>
    <div class="sabai-fieldui-widget-label">%s%s</div>
    <div><input type="textfield" disabled="disabled" size="40" /></div>
</div>
<div%s>
    <div class="sabai-fieldui-widget-label">%s%s</div>
    <div><input type="textfield" disabled="disabled" size="40" value="%s" /></div>
</div>
<div%s>
    <div class="sabai-fieldui-widget-label">%s%s</div>
    <div><input type="textfield" disabled="disabled" style="width:100%%;" value="%s" /></div>
</div>',
                    $settings['hide_phone_field'] ? ' style="display:none;"' : '',
                    __('Phone Number', 'sabai-directory'),
                    $settings['require_phone'] ? $required_label : '',
                    $settings['hide_mobile_field'] ? ' style="display:none;"' : '',
                    __('Mobile Number', 'sabai-directory'),
                    $settings['require_mobile'] ? $required_label : '',
                    $settings['hide_fax_field'] ? ' style="display:none;"' : '',
                    __('Fax Number', 'sabai-directory'),
                    $settings['require_fax'] ? $required_label : '',
                    $settings['hide_email_field'] ? ' style="display:none;"' : '',
                    __('E-mail', 'sabai-directory'),
                    $settings['require_email'] ? $required_label : '',
                    empty($settings['autopopulate_email']) ? '' : 'me@mydomain.com',
                    $settings['hide_website_field'] ? ' style="display:none;"' : '',
                    __('Website', 'sabai-directory'),
                    $settings['require_website'] ? $required_label : '',
                    empty($settings['autopopulate_website']) ? '' : 'http://www.mydomain.com/'
                );
            case 'directory_social':
                $required_label = '<span class="sabai-fieldui-widget-required">*</span>';
                return sprintf(
                    '<div>
    <div class="sabai-fieldui-widget-label"><i class="sabai-icon-twitter-sign"></i> %s%s</div>
    <div><input type="textfield" disabled="disabled" size="30" /></div>
</div>
<div>
    <div class="sabai-fieldui-widget-label"><i class="sabai-icon-facebook-sign"></i> %s%s</div>
    <div><input type="textfield" disabled="disabled" style="width:100%%;" /></div>
</div>
<div>
    <div class="sabai-fieldui-widget-label"><i class="sabai-icon-google-plus-sign"></i> %s%s</div>
    <div><input type="textfield" disabled="disabled" style="width:100%%;" /></div>
</div>',
                    __('Twitter', 'sabai-directory'),
                    $settings['require_twitter'] ? $required_label : '',
                    __('Facebook URL', 'sabai-directory'),
                    $settings['require_facebook'] ? $required_label : '',
                    __('Google+ URL', 'sabai-directory'),
                    $settings['require_googleplus'] ? $required_label : ''
                );
        }
    }
    
    public function fieldWidgetGetEditDefaultValueForm($fieldType, array $fieldSettings, array $settings, array $parents = array())
    {

    }
}
