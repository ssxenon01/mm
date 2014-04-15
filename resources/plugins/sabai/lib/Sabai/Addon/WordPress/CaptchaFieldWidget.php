<?php
class Sabai_Addon_WordPress_CaptchaFieldWidget implements Sabai_Addon_Field_IWidget
{
    private $_addon;

    public function __construct(Sabai_Addon_WordPress $addon)
    {
        $this->_addon = $addon;
    }

    public function fieldWidgetGetInfo($key = null)
    {
        $info = array(
            'label' => 'Really Simple CAPTCHA',
            'field_types' => array('captcha'),
            'default_settings' => array(
                'image_size' => 'medium',
            ),
            'default_required' => true,
            'default_user_roles' => array('_guest_'),
        );

        return isset($key) ? @$info[$key] : $info;
    }

    public function fieldWidgetGetSettingsForm($fieldType, array $fieldSettings, array $settings, array $parents = array())
    {
        return array(
            'image_size' => array(
                '#type' => 'select',
                '#title' => __('CAPTCHA image size', 'sabai'),
                '#options' => array(
                    'small' => __('Small', 'sabai'),
                    'medium' => __('Medium', 'sabai'),
                    'large' => __('Large', 'sabai'),
                ),
                '#default_value' => $settings['image_size'],
                '#element_validate' => array(array($this, 'validateReallySimpleCaptcha')),
            ),
        );

        return $form;
    }

    public function fieldWidgetGetForm(Sabai_Addon_Field_IField $field, array $settings, $value = null, array $parents = array())
    {
        return array(
            // Just create a placeholder for the Captcha field. Captcha will be generated before render so the previous Captcha is not overwritten.
            '#type' => 'textfield',
            '#element_validate' => array(array($this, 'validateCaptcha')),
            '#pre_render' => array(array(array($this, 'renderCaptcha'), array($settings['image_size']))),
            '#size' => 10,
        );
    }

    public function fieldWidgetGetEditDefaultValueForm($fieldType, array $fieldSettings, array $settings, array $parents = array())
    {

    }
    
    public function fieldWidgetGetPreview(Sabai_Addon_Field_IField $field, array $settings)
    {
        $widths = array('small' => 100, 'medium' => 150, 'large' => 200);
        $width = $widths[$settings['image_size']];
        return sprintf(
            '<img style="vertical-align:middle;" src="%s" width="%d" height="%d"> <input type="text" disabled="disabled" size="10" />',
            $this->_addon->getApplication()->ImageUrl('really-simple-captcha.png'),
            $width,
            intval($width * 0.28)
        );
    }
    
    public function renderCaptcha($form, &$element, $size)
    {
        $width = array('small' => 100, 'medium' => 150, 'large' => 200);
        $captcha = $this->_getReallySimpleCaptcha();
        $prefix = $form->settings['#build_id'];
        $image = $captcha->generate_image($prefix, $captcha->generate_random_word());
        $image_src = rtrim(str_replace(rtrim(str_replace('\\', '/', ABSPATH), '/'), rtrim(get_option('siteurl'), '/'), str_replace('\\', '/', $captcha->tmp_dir)), '/') . '/' . $image;
        $element['#field_prefix'] = '<img style="vertical-align:middle;" src="'. $image_src .'?t='. time() .'" width="'. $width[$size] .'" height="'. intval(0.28 * $width[$size]) .'" />';
    }
    
    public function validateCaptcha($form, &$value, $element)
    {
        if (!$this->_getReallySimpleCaptcha()->check($form->settings['#build_id'], $value)) {
            $form->setError(__('You did not enter the correct characters.', 'sabai'), $element);
        }
        $value = '';
    }
    
    public function validateReallySimpleCaptcha($form, &$value, $element)
    {
        if (!is_plugin_active('really-simple-captcha/really-simple-captcha.php')) {
            $form->setError(__('The Really Simple CAPTCHA plugin must be installed and active.', 'sabai'));
        }
    }
    
    private function _getReallySimpleCaptcha()
    {
        if (!class_exists('ReallySimpleCaptcha', false)) {
            $class_file =  WP_PLUGIN_DIR . '/really-simple-captcha/really-simple-captcha.php';
            if (!file_exists($class_file)) {
                throw new Sabai_RuntimeException('Really Simple CAPTCHA was not found.');
            }
            require $class_file;
        }
        $captcha = new ReallySimpleCaptcha();
        $captcha->tmp_dir = defined('SABAI_WORDPRESS_REALLY_SIMPLE_CAPTCHA_TMPDIR') ? SABAI_WORDPRESS_REALLY_SIMPLE_CAPTCHA_TMPDIR : WP_PLUGIN_DIR . '/really-simple-captcha/tmp';
        $captcha->file_mode = 0644;
		$captcha->answer_file_mode = 0640;
        
        return $captcha;
    }
}