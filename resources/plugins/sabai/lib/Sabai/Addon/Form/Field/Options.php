<?php
class Sabai_Addon_Form_Field_Options extends Sabai_Addon_Form_Field_AbstractField
{
    public function formFieldGetFormElement($name, array &$data, Sabai_Addon_Form_Form $form)
    {
        if (!isset($data['#template'])) {
        // Modify template slightly so that the field decription is displayed at the top of the table.
            $data['#template'] = '<div<!-- BEGIN id --> id="{id}"<!-- END id --> class="{class_prefix}form-field<!-- BEGIN class --> {class}<!-- END class -->">
  <!-- BEGIN label --><div class="{class_prefix}form-field-label"><span>{label}</span><!-- BEGIN required --><span class="{class_prefix}form-field-required">*</span><!-- END required --></div><!-- END label -->
  <!-- BEGIN label_2 --><div class="{class_prefix}form-field-description {class_prefix}form-field-description-top">{label_2}</div><!-- END label_2 -->
  <!-- BEGIN error_msg --><span class="{class_prefix}form-field-error">{error}</span><!-- END error_msg -->
  {element}
</div>';
        }
        $ele_id = $form->getFieldId($name);
        if (!empty($data['#multiple'])) {
            $type = 'checkbox';
            $default_name = '[default][]';
        } else {
            $type = 'radio';
            $default_name = '[default]';
        }
        $input = '
    <li class="sabai-form-field-option"><input type="%9$s" name="%1$s%10$s" value="%2$d"%5$s />
        <input type="text" name="%1$s[options][%2$d][label]" value="%3$s" size="15" placeholder="%7$s" />
        <input type="text" name="%1$s[options][%2$d][value]" value="%4$s" size="15" placeholder="%8$s" />
        <a href="#" class="sabai-btn sabai-btn-mini sabai-btn-success" onclick="SABAI.addOption(\'#%6$s\', \'%1$s\', this, %11$s); return false;"><i class="sabai-icon-plus"></i></a>
        <a href="#" class="sabai-btn sabai-btn-mini sabai-btn-danger" onclick="SABAI.removeOption(\'#%6$s\', this); return false;"><i class="sabai-icon-minus"></i></a>
        <span class="sabai-btn sabai-btn-mini sabai-btn-inverse"><i class="sabai-icon-resize-vertical"></i></span>
    </li>';
    
        $inputs = array();
        $label_title = isset($data['#label_title']) ? $data['#label_title'] : __('Label', 'sabai');
        $value_title = isset($data['#value_title']) ? $data['#value_title'] : __('Value', 'sabai');
        if (isset($data['#default_value']['options']) &&
            ($options_count = count($data['#default_value']['options']))
        ) {
            $default_values = isset($data['#default_value']['default']) ? $data['#default_value']['default'] : array();
            if ($options_disabled = isset($data['#options_disabled']) ? $data['#options_disabled'] : array()) {
                $input_disabled = '
    <li class="sabai-form-field-option sabai-form-field-option-disabled"><input type="%9$s" name="%1$s%10$s" value="%2$d"%5$s />
        <input type="text" value="%3$s" size="15" placeholder="%7$s" disabled="disabled" />
        <input type="hidden" name="%1$s[options][%2$d][label]" value="%3$s" />
        <input type="text" value="%4$s" size="15" placeholder="%8$s" disabled="disabled" />
        <input type="hidden" name="%1$s[options][%2$d][value]" value="%4$s" />
        <a href="#" class="sabai-btn sabai-btn-mini sabai-btn-success sabai-disabled" onclick="return false;"><i class="sabai-icon-plus"></i></a>
        <a href="#" class="sabai-btn sabai-btn-mini sabai-btn-danger sabai-disabled" onclick="return false;"><i class="sabai-icon-minus"></i></a>
        <span class="sabai-btn sabai-btn-mini sabai-btn-inverse"><i class="sabai-icon-resize-vertical"></i></span>
    </li>';
            }
            $key = 0;
            foreach ($data['#default_value']['options'] as $value => $label) {
                $inputs[] = sprintf(
                    in_array($value, $options_disabled) ? $input_disabled : $input,
                    $name,
                    $key,
                    Sabai::h($label),
                    Sabai::h($value),
                    in_array($value, $default_values) ? ' checked="checked"' : '',
                    $ele_id,
                    Sabai::h($label_title),
                    Sabai::h($value_title),
                    $type,
                    $default_name,
                    $type === 'checkbox' ? 'true' : 'false'
                );
                ++$key;
            }
            $inputs[] = sprintf(
                $input,
                $name,
                $key,
                '',
                '',
                '',
                $ele_id,
                Sabai::h($label_title),
                Sabai::h($value_title),
                $type,
                $default_name,
                $type === 'checkbox' ? 'true' : 'false'
            );
            $data['#options'] = array_keys($data['#default_value']);
        } else {
            for ($i = 0; $i < 3; $i++) {
                $inputs[] = sprintf(
                    $input,
                    $name,
                    $i,
                    '',
                    '',
                    $i === 0 ? ' checked="checked"' : '',
                    $ele_id,
                    Sabai::h($label_title),
                    Sabai::h($value_title),
                    $type,
                    $default_name,
                    $type === 'checkbox' ? 'true' : 'false'
                );
            }
            $data['#options'] = array();
        }
        $data['#markup'] = '<ul id="' . $ele_id .'">' . implode(PHP_EOL, $inputs) . '</ul>';
        $data['#default_value'] = $data['#value'] = null;
        
        $form->addJs('jQuery(document).ready(function(){jQuery("#'. $ele_id .'").sortable({handle:".sabai-btn-inverse", containment:"parent", axis:"y"});});');
        
        return $form->createHTMLQuickformElement('static', $name, $data['#label'], $data['#markup']);
    }

    public function formFieldOnSubmitForm($name, &$value, array &$data, Sabai_Addon_Form_Form $form)
    {        
        if (empty($value['options'])) {
            if ($form->isFieldRequired($data)) {
                $form->setError(isset($data['#required_error_message']) ? $data['#required_error_message'] : __('Please fill out this field.', 'sabai'), $name);
            }
            $value = array('options' => array(), 'default' => array());

            return;
        }
        
        $options = array();
        $default_value = array();
        foreach ($value['options'] as $key => $option) {
            $option['value'] = trim($option['value']);
            if (!strlen($option['value'])) {
                continue;
            }
            if (isset($data['#value_regex'])) {
                if (!preg_match($data['#value_regex'], $option['value'])) {
                    $error = isset($data['#value_regex_error_message'])
                        ? $data['#value_regex_error_message']
                        : sprintf(__('The input value did not match the regular expression: %s', 'sabai'), $data['#value_regex']);
                    $form->setError($error, $name);
                }
            }
            $options[$option['value']] = $option['label'];
            if (isset($value['default'])) {
                if (in_array($key, (array)$value['default'])) {
                    $default_value[] = $option['value'];
                }
            }
        }
        
        if (empty($options)
            && $form->isFieldRequired($data)
        ) {
            $form->setError(isset($data['#required_error_message']) ? $data['#required_error_message'] : __('Please fill out this field.', 'sabai'), $name);
        }
        
        $value = array('options' => $options, 'default' => $default_value);
    }

    public function formFieldOnCleanupForm($name, array $data, Sabai_Addon_Form_Form $form)
    {

    }

    public function formFieldOnRenderForm($name, array $data, Sabai_Addon_Form_Form $form)
    {
        $form->renderElement($data);
    }
}