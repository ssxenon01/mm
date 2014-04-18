<?php
class Sabai_Addon_Markdown_FormField implements Sabai_Addon_Form_IField
{
    private $_addon, $_name;
    private static $_elements = array();

    public function __construct(Sabai_Addon_Markdown $addon, $name)
    {
        $this->_addon = $addon;
        $this->_name = $name;
    }
    
    public function formFieldGetFormElement($name, array &$data, Sabai_Addon_Form_Form $form)
    {
        if (!isset(self::$_elements[$form->settings['#id']])) {
            self::$_elements[$form->settings['#id']] = array();
        }
        $data['#inline_elements_only'] = !empty($data['#inline_elements_only']);
        $editor_id_suffix = $form->getFieldId($name);
        $data['#prefix'] = '<div class="sabai-markdown-editor">';
        if (empty($data['#hide_buttons']) && !$data['#inline_elements_only']) {
            $data['#prefix'] .= '<div id="wmd-button-bar-'. $editor_id_suffix .'"></div>';
        }
        if (empty($data['#hide_preview']) && !$data['#inline_elements_only']) {
            $data['#suffix'] = '<div id="wmd-preview-'. $editor_id_suffix .'" class="sabai-markdown-preview"></div></div>';
        } else {
            $data['#suffix'] = '</div>';
        }
        $data['#attributes']['id'] = 'wmd-input-' . $editor_id_suffix;
        if (isset($data['#default_value'])
            && is_array($data['#default_value'])
            && isset($data['#default_value']['text'])
        ) {
            $data['#default_value'] = $data['#default_value']['text'];
        }
        
        // Register pre render callback if this is the first map element
        if (empty(self::$_elements[$form->settings['#id']])) {
            $form->settings['#pre_render'][] = array($this, 'preRenderCallback');
        }

        self::$_elements[$form->settings['#id']][$editor_id_suffix] = array(
            'suffix' => $editor_id_suffix,
            'help_url' => isset($data['#help_url']) ? $data['#help_url'] : null,
            'help_window_w' => isset($data['#help_window_w']) ? $data['#help_window_w'] : 720,
            'help_window_h' => isset($data['#help_window_h']) ? $data['#help_window_h'] : 480,
        );
        
        $data['#attributes']['rows'] = !empty($data['#rows']) ? $data['#rows'] : 8;
        if (!empty($data['#cols'])) {
            $data['#attributes']['cols'] = $data['#cols'];
            $style_width = 'width:' . ceil($data['#cols'] * 0.7) . 'em;';
        } else {
            $style_width = "width:98%;";
        }
        if (!isset($data['#attributes']['style']['width'])) {
            $data['#attributes']['style'] = $style_width;
        } else {
            $data['#attributes']['style'] .= $style_width;
        }

        return $form->createHTMLQuickformElement('textarea', $name, $data['#label'], $data['#attributes']);
    }
    
    public function formFieldOnSubmitForm($name, &$value, array &$data, Sabai_Addon_Form_Form $form)
    {
        // Do not mess with markdown formatted text
        $data['#no_trim'] = true;
        
        // Validate required/min_length/max_length settings if any
        if (!$this->_addon->getApplication()->getAddon('Form')->validateFormElementText($form, $value, $data)) {
            return;
        }
        
        $value = array(
            'text' => $value,
            'filtered_text' => self::filter($this->_addon->getApplication(), $data, $value),
        );
    }

    public function formFieldOnCleanupForm($name, array $data, Sabai_Addon_Form_Form $form)
    {

    }

    public function formFieldOnRenderForm($name, array $data, Sabai_Addon_Form_Form $form)
    {
        $form->renderElement($data);
    }
    
    public function preRenderCallback($form)
    {
        if (empty(self::$_elements[$form->settings['#id']])) return;
        
        $langs = array(
            'bold' => __('Strong <strong> Ctrl+B  4', 'sabai'),
            'boldexample' => __('strong text', 'sabai'),
            'italic' => __('Emphasis <em> Ctrl+I', 'sabai'),
            'italicexample' => __('emphasized text', 'sabai'),
            'link' => __('Hyperlink <a> Ctrl+L', 'sabai'),
            'linkdescription' => __('enter link description here', 'sabai'),
            'linkdialog' => __('<h2>Insert Hyperlink</h2><p>http://example.com/ "optional title"</p>', 'sabai'),
            'quote' => __('Blockquote <blockquote> Ctrl+Q', 'sabai'),
            'quoteexample' => __('Blockquote', 'sabai'),
            'code' => __('Code Sample <pre><code> Ctrl+K', 'sabai'),
            'codeexample' => __('enter code here', 'sabai'),
            'image' => __('Image <img> Ctrl+G', 'sabai'),
            'imagedescription' => __('enter image description here', 'sabai'),
            'imagedialog' => __('<h2>Insert Image</h2><p>http://example.com/images/diagram.jpg "optional title"</p>', 'sabai'),
            'olist' => __('Numbered List <ol> Ctrl+O', 'sabai'),
            'ulist' => __('Bulleted List <ul> Ctrl+U', 'sabai'),
            'litem' => __('List item', 'sabai'),
            'heading' => __('Heading <h1>/<h2> Ctrl+H', 'sabai'),
            'headingexample' => __('Heading', 'sabai'),
            'hr' => __('Horizontal Rule <hr> Ctrl+R', 'sabai'),
            'undo' => __('Undo - Ctrl+Z', 'sabai'),
            'redo' => __('Redo - Ctrl+Y', 'sabai'),
            'redomac' => __('Redo - Ctrl+Shift+Z', 'sabai'),
            'help' => __('Markdown Editing Help', 'sabai'),
        );
        $js = array(sprintf('var sabai_markdown_langs = %s;', json_encode($langs)));
        foreach (self::$_elements[$form->settings['#id']] as $ele_data) {
            $js[] = sprintf(
                'SABAI.Markdown.editor("%s", sabai_markdown_langs, {url: %s, width: %d, height: %d});',
                $ele_data['suffix'],
                $ele_data['help_url'] ? '"'. $ele_data['help_url'] .'"' : 'null',
                $ele_data['help_window_w'],
                $ele_data['help_window_h']
            );
        }
        // Add js
        $form->addJs(sprintf(
            '$LAB.script("%s")
    .script("%s")
    .script("%s")
    .script("%s").wait(function () {
    %s
});',
            $this->_addon->getApplication()->getPlatform()->getAssetsUrl() . '/js/Markdown.Converter.js',
            $this->_addon->getApplication()->getPlatform()->getAssetsUrl() . '/js/Markdown.Sanitizer.js',
            $this->_addon->getApplication()->getPlatform()->getAssetsUrl() . '/js/Markdown.Editor.js',
            $this->_addon->getApplication()->getPlatform()->getAssetsUrl() . '/js/sabai-markdown-editor.js',
            implode(PHP_EOL, $js)
        ));
    }
    
    public static function filter(Sabai $application, array $settings, $value)
    {
        if (!strlen($value)) {
            return '';
        }
        $options = array(
            'URI.DisableExternal' => !empty($settings['#disable_external_links']),
            'URI.DisableExternalResources' => !empty($settings['#disable_external_resources']),
            'AutoFormat.Linkify' => true,
        );
        if (!empty($settings['#enable_iframe']) && !empty($settings['#enable_iframe_urls'])) {
            $options += array(
                'HTML.SafeIframe' => true,
                'URI.SafeIframeRegexp' => '%^(' . implode('|', $settings['#enable_iframe_urls']) . ')%',
            );
            $options['URI.DisableExternalResources'] = false;
        }
        return $application->HTML_Filter(
            $application->Markdown_Transform($value),
            $options,
            !$settings['#inline_elements_only']
        );
    }
}
