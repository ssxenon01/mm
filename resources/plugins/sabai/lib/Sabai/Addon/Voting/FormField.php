<?php
class Sabai_Addon_Voting_FormField implements Sabai_Addon_Form_IField
{
    private $_addon, $_name;
    private static $_renderCallbackRegistered = false;

    public function __construct(Sabai_Addon_Voting $addon, $name)
    {
        $this->_addon = $addon;
        $this->_name = $name;
    }
    
    public function formFieldGetFormElement($name, array &$data, Sabai_Addon_Form_Form $form)
    {       
        $ele_id = $form->getFieldId($name);
        $rating_html = sprintf(
            '<div class="rateit" data-rateit-backingfld="#%s" data-rateit-resetable="false"  data-rateit-ispreset="true" data-rateit-min="%d" data-rateit-max="%d"></div>',
            $ele_id,
            $data['#rateit_min'],
            $data['#rateit_max']    
        );
        if (!isset($data['#default_value'])) {
            
        }
        $data['#markup'] = sprintf(
            '<input type="hidden" id="%s" name="%s" value="%s" step="%s" />%s',
            $ele_id,
            Sabai::h($name),
            Sabai::h(isset($data['#default_value']) ? $data['#default_value'] : $data['#rateit_min']),
            Sabai::h($data['#step']),
            $rating_html
        );
        
        // Register pre render callback if this is the first map element
        if (!self::$_renderCallbackRegistered) {
            $form->settings['#pre_render'][] = array($this, 'preRenderCallback');
            self::$_renderCallbackRegistered = true;
        }

        unset($data['#default_value'], $data['#value']);
        
        return $form->createElement('item', $name, $data);
        return $form->createHTMLQuickformElement('static', $name, $data['#label'], $data['#markup']);
    }
    
    public function formFieldOnSubmitForm($name, &$value, array &$data, Sabai_Addon_Form_Form $form)
    {
        if ($value < $data['#rateit_min'] || $value > $data['#rateit_max']) {
            $form->setError(sprintf(__('The input value must be between %d and %d.', 'sabai'), $data['#rateit_min'], $data['#rateit_max']), $name);
            return;
        }
        
        if (($value * 100) % ($data['#step'] * 100)) {
            $form->setError(__('Invalid value.', 'sabai'), $name);
            return;
        }
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
        // Add js
        $form->addJs(sprintf(
            '$LAB.script("%s");',
            $this->_addon->getApplication()->getPlatform()->getAssetsUrl() . '/js/jquery.rateit.min.js'
        ));
    }
}