<?php
class Sabai_Addon_Markdown_FieldWidget implements Sabai_Addon_Field_IWidget
{
    private $_addon, $_name;

    public function __construct(Sabai_Addon_Markdown $addon, $name)
    {
        $this->_addon = $addon;
        $this->_name = $name;
    }

    public function fieldWidgetGetInfo($key = null)
    {
        $info = array(
            'label' => __('Textarea', 'sabai'),
            'field_types' => array('markdown_text'),
            'default_settings' => array(
                'rows' => 5,
                'hide_buttons' => false,
                'hide_preview' => false,
                'embed_external_links_perm' => null,
                'embed_external_resources_perm' => null,
                'iframe' => false,
                'iframe_urls' => array('//www.youtube.com/embed/', 'http://www.youtube.com/embed/', 'http://player.vimeo.com/video/', 'http://www.dailymotion.com/embed/video/', 'http://www.screenr.com/embed/'),
            ),
            'is_fieldset' => true,
        );
        return isset($key) ? @$info[$key] : $info;
    }

    public function fieldWidgetGetSettingsForm($fieldType, array $fieldSettings, array $settings, array $parents = array())
    {
        return array(
            'rows' => array(
                '#type' => 'textfield',
                '#title' => __('Rows', 'sabai'),
                '#size' => 5,
                '#integer' => true,
                '#default_value' => $settings['rows'],
            ),
            'hide_buttons' => array(
                '#type' => 'checkbox',
                '#title' => __('Hide editor buttons', 'sabai'),
                '#default_value' => $settings['hide_buttons'],
            ),
            'hide_preview' => array(
                '#type' => 'checkbox',
                '#title' => __('Hide preview', 'sabai'),
                '#default_value' => $settings['hide_preview'],
            ),
            'iframe' => array(
                '#type' => 'checkbox',
                '#title' => __('Allow iframe HTML tags', 'sabai'),
                '#default_value' => $settings['iframe'],
            ),
            'iframe_urls' => array(
                '#type' => 'textfield',
                '#title' => __('Iframe URL Whitelist', 'sabai'),
                '#url' => true,
                '#allow_url_no_protocol' => true,
                '#separator' => ',',
                '#default_value' => $settings['iframe_urls'],
                '#description' => __('Enter the URLs of content that are allowed to be embedded within iframes, separated with commas.', 'sabai'),
                '#states' => array(
                    'visible' => array(
                        sprintf('input[name="%s[iframe][]"]', $this->_addon->getApplication()->Form_FieldName($parents)) => array(
                            'type' => 'checked',
                            'value' => true,
                        ),
                    ),
                ),
                '#required' => array(array($this, 'isIframeUrlsRequired'), array($parents)),
            ),
        );
    }
    
    public function isIframeUrlsRequired($form, $parents)
    {
        $values = $form->getValue($parents);
        return !empty($values['iframe']);
    }

    public function fieldWidgetGetForm(Sabai_Addon_Field_IField $field, array $settings, $value = null, array $parents = array())
    {
        $config = $this->_addon->getConfig();
        return array(
            '#type' => 'markdown_textarea',
            '#rows' => $settings['rows'],
            '#default_value' => isset($value)
                ? array('text' => $value['value'], 'filtered_text' => $value['filtered_value'])
                : null,
            '#disable_external_links' => isset($settings['embed_external_links_perm'])
                ? !$this->_addon->getApplication()->HasPermission($settings['embed_external_links_perm'])
                : false,
            '#disable_external_resources' => isset($settings['embed_external_resources_perm'])
                ? !$this->_addon->getApplication()->HasPermission($settings['embed_external_resources_perm'])
                : false,
            '#enable_iframe' => !empty($settings['iframe']),
            '#enable_iframe_urls' => !empty($settings['iframe_urls']) ? (array)$settings['iframe_urls'] : null,
            '#inline_elements_only' => false,
            '#help_url' => !empty($config['help']) ? $config['help_url'] : null,
            '#help_window_w' => $config['help_window']['width'],
            '#help_window_h' => $config['help_window']['height'],
            '#hide_buttons' => !empty($settings['hide_buttons']),
            '#hide_preview' => !empty($settings['hide_preview']),
        );
    }
    
    public function fieldWidgetGetPreview(Sabai_Addon_Field_IField $field, array $settings)
    {
        return sprintf('<textarea rows="%d" disabled="disabled" style="width:100%%;">%s</textarea>', $settings['rows'], Sabai::h($field->getFieldDefaultValue()));
    }

    public function fieldWidgetGetEditDefaultValueForm($fieldType, array $fieldSettings, array $settings, array $parents = array())
    {

    }
}
