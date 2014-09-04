<?php
class Sabai_Addon_Form extends Sabai_Addon
    implements Sabai_Addon_Form_IFields
{
    const VERSION = '1.2.32', PACKAGE = 'sabai';
    const FORM_BUILD_ID_NAME = '_sabai_form_build_id', FORM_SUBMIT_BUTTON_NAME = '_sabai_form_submit';

    private static $_forms = array();
                
    public function isUninstallable($currentVersion)
    {
        return false;
    }

    /* Start implementation of Sabai_Addon_Form_IFields */

    public function formGetFieldTypes()
    {
        return array('textarea', 'radio', 'radios', 'checkbox', 'checkboxes', 'select',
            'hidden', 'item', 'markup', 'password', 'textfield', 'fieldset', 'submit',
            'grid', 'tableselect', 'token', 'options', 'sectionbreak');
    }

    public function formGetField($type)
    {
        switch ($type) {
            case 'textarea':
                return new Sabai_Addon_Form_Field_Textarea($this);
            case 'radio':
                return new Sabai_Addon_Form_Field_Radio($this);
            case 'radios':
                return new Sabai_Addon_Form_Field_Radios($this);
            case 'checkbox':
                return new Sabai_Addon_Form_Field_Checkbox($this);
            case 'checkboxes':
                return new Sabai_Addon_Form_Field_Checkboxes($this);
            case 'select':
                return new Sabai_Addon_Form_Field_Select($this);
            case 'hidden':
                return new Sabai_Addon_Form_Field_Hidden($this);
            case 'item':
                return new Sabai_Addon_Form_Field_Item($this);
            case 'markup':
                return new Sabai_Addon_Form_Field_Markup($this);
            case 'password':
                return new Sabai_Addon_Form_Field_Password($this);
            case 'textfield':
                return new Sabai_Addon_Form_Field_Text($this);
            case 'fieldset':
                return new Sabai_Addon_Form_Field_Fieldset($this);
            case 'submit':
                return new Sabai_Addon_Form_Field_Submit($this);
            case 'grid':
                return new Sabai_Addon_Form_Field_Grid($this);
            case 'tableselect':
                return new Sabai_Addon_Form_Field_TableSelect($this);
            case 'token':
                return new Sabai_Addon_Form_Field_Token($this);
            case 'options':
                return new Sabai_Addon_Form_Field_Options($this);
            case 'sectionbreak':
                return new Sabai_Addon_Form_Field_SectionBreak($this);
        }
    }

    public function initTextFormElementSettings(Sabai_Addon_Form_Form $form, array &$data)
    {
        $data['#attributes']['maxlength'] = !empty($data['#max_length']) ? $data['#max_length'] : 255;
        if (!empty($data['#size'])) {
            $data['#attributes']['size'] = $data['#size'];
            $style_width = 'width:' . ceil($data['#size'] * 0.7) . 'em;';
        }
        if (false !== @$data['#auto_style_width']) { // auto styling with can be disabled by setting #auto_style_width to false
            if (!isset($style_width)) {
                if (isset($data['#field_prefix']) || isset($data['#field_suffix'])) {
                    // make room for field prefix/suffix
                    $style_width = 'width:90%;';
                } else {
                    $style_width = 'width:98%;';
                }
            }
            if (!isset($data['#attributes']['style'])) {
                $data['#attributes']['style'] = $style_width;
            } else {
                $data['#attributes']['style'] .= $style_width;
            }
        }
        // Auto populate field?
        if (!isset($data['#default_value'])) {
            if (isset($data['#auto_populate'])) {
                switch ($data['#auto_populate']) {
                    case 'email':
                        $data['#default_value'] = $this->_application->getUser()->email;
                        break;
                    case 'url':
                        $data['#default_value'] = $this->_application->getUser()->url;
                        break;
                    case 'username':
                        $data['#default_value'] = $this->_application->getUser()->username;
                        break;
                    case 'name':
                        $data['#default_value'] = $this->_application->getUser()->name;
                        break;
                }
            }
        }
        
        if (!isset($data['#attributes']['placeholder'])) {
            if (!empty($data['#url']) || @$data['#char_validation'] === 'url') {
                $data['#attributes']['placeholder'] = 'http://';
            }
        }
    }

    public function validateFormElementText(Sabai_Addon_Form_Form $form, &$value, array $element, $errorElementName = null, $checkRequired = true)
    {
        if (!isset($errorElementName)) $errorElementName = $element['#name'];

        if (!empty($element['#char_validation'])
            && in_array($element['#char_validation'], array('integer', 'numeric', 'alnum', 'alpha', 'lower', 'upper', 'url', 'email'))
        ) {
            $element['#' . $element['#char_validation']] = true;
        }

        if (empty($element['#no_trim'])) {
            $value = $this->_application->Trim($value);
        }
        
        // Remove value sent from placeholder
        if (!empty($element['#url']) && $value === 'http://') {
            $value = '';
        }
        
        if (strlen($value) === 0) {
            if ($checkRequired) {
                if ($form->isFieldRequired($element)) {
                    $form->setError(isset($element['#required_error_message']) ? $element['#required_error_message'] : __('Please fill out this field.', 'sabai'), $errorElementName);
                    return false;
                }
            }
            return true;
        }

        if (!empty($element['#integer'])) {
            if (!preg_match('/^-?\d+$/', $value)) {
                $form->setError(__('The input value must be an integer.', 'sabai'), $errorElementName);
                return false;
            }
        } elseif (!empty($element['#numeric'])) {
            if (!is_numeric($value)) {
                $form->setError(__('The input value must be numeric.', 'sabai'), $errorElementName);
                return false;
            }
        } elseif (!empty($element['#alpha'])) {
            if (!ctype_alpha($value)) {
                $form->setError(__('The input value must consist of alphabets only.', 'sabai'), $errorElementName);
                return false;
            }
        } elseif (!empty($element['#alnum'])) {
            if (!ctype_alnum($value)) {
                $form->setError(__('The input value must consist of alphanumeric characters only.', 'sabai'), $errorElementName);
                return false;
            }
        } elseif (!empty($element['#lower'])) {
            if (!ctype_lower($value)) {
                $form->setError(__('The input value must consist of lowercasae characters only.', 'sabai'), $errorElementName);
                return false;
            }
        } elseif (!empty($element['#upper'])) {
            if (!ctype_upper($value)) {
                $form->setError(__('The input value must consist of uppercase characters only.', 'sabai'), $errorElementName);
                return false;
            }
        } elseif (!empty($element['#url'])) {
            $value_to_check = $value;
            if (0 === strpos($value, '//')) {
                if (!empty($element['#allow_url_no_protocol'])) {
                    $value_to_check = 'http:' . $value;
                }
            } elseif (false === strpos($value, '://')) {
                $value_to_check = $value = 'http://' . $value;
            }
            // Add a temporary fix for php 5.2.13/5.3.2 returning false for URLs containing hyphens
            $php_version = substr(PHP_VERSION, 0, strpos(PHP_VERSION, '-')); // remove extra version info
            if (version_compare($php_version, '5.3.2', '==') || version_compare($php_version, '5.2.13', '==')) {
                $value_to_check = str_replace('-', '', $value_to_check);
            }
            if (!$result = filter_var($value_to_check, FILTER_VALIDATE_URL)) {
                $form->setError(__('The input value is not a valid URL.', 'sabai'), $errorElementName);
                return false;
            }
        } elseif (!empty($element['#email'])) {
            if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                $form->setError(__('The input value is not a valid E-mail address.', 'sabai'), $errorElementName);
                return false;
            }
        }

        // Check min/max length
        $min_length = empty($element['#min_length']) ? null : (int)$element['#min_length'];
        $max_length = empty($element['#max_length']) ? null : (int)$element['#max_length'];
        $value_length = mb_strlen($value, SABAI_CHARSET);
        if ($max_length && $min_length) {
            if ($max_length === $min_length) {
                if ($value_length !== $max_length) {
                    $form->setError(sprintf(__('The input value must be %d characters.', 'sabai'), $max_length), $errorElementName);
                    return false;
                }
            } else {
                if ($value_length < $min_length || $value_length > $max_length) {
                    $form->setError(sprintf(__('The input value must be between %d and %d characters.', 'sabai'), $min_length, $max_length), $errorElementName);
                    return false;
                }
            }
        } elseif ($max_length) {
            if ($value_length > $max_length) {
                $form->setError(sprintf(__('The input value must be shorter than %d characters.', 'sabai'), $max_length), $errorElementName);
                return false;
            }
        } elseif ($min_length) {
            if ($value_length < $min_length) {
                $form->setError(sprintf(__('The input value must be longer than %d characters.', 'sabai'), $min_length), $errorElementName);
                return false;
            }
        }
        
        if (!empty($element['#integer']) || !empty($element['#numeric'])) {
            if (!empty($element['#min_value'])) {
                if ($value < $element['#min_value']) {
                    $form->setError(sprintf(__('The value must be equal or greater than %s.', 'sabai'), $element['#min_value']), $errorElementName);
                }
            }
            if (!empty($element['#max_value'])) {
                if ($value > $element['#max_value']) {
                    $form->setError(sprintf(__('The value must not be greater than %s.', 'sabai'), $element['#max_value']), $errorElementName);
                }
            }
        }
        
        // Validate against regex?
        if (!empty($element['#regex'])) {
            if (!preg_match($element['#regex'], $value, $matches)) {
                $form->setError(sprintf(__('The input value did not match the regular expression: %s', 'sabai'), $element['#regex']), $errorElementName);
                return false;
            }
        }

        return true;
    }

    /* End implementation of Sabai_Addon_Form_IFields */

    public function buildForm(array $settings, $useCache = true, array $values = null, array $errors = array())
    {
        if (!isset($settings['#build_id']) || strlen($settings['#build_id']) !== 32) {
            $settings['#build_id'] = md5(uniqid(mt_rand(), true));
        } else {
            // Is the form already built and cached?
            if (isset(self::$_forms[$settings['#build_id']])) {
                // Return cached form if rebuild is not necessary
                if ($useCache) return self::$_forms[$settings['#build_id']];
            }
        }
        // Set id if not already set
        if (!isset($settings['#id'])) {
            $settings['#id'] = 'sabai-form-' . $settings['#build_id'];
        }

        if ((isset($settings['#method']) && $settings['#method'] !== 'get')
            || !empty($settings['#enable_storage']))
        { 
            // Embed build ID in hidden field
            $settings[self::FORM_BUILD_ID_NAME] = array(
                '#type' => 'hidden',
                '#value' => $settings['#build_id']
            );
        }

        // Initialize form storage
        $storage = array();
        if (!empty($settings['#enable_storage'])) {
            if (isset($settings['#initial_storage'])) $storage = $settings['#initial_storage'];

            $this->setFormStorage($settings['#build_id'], $storage);
        }
        
        // Define submit buttons fieldset
        if (!isset($settings[self::FORM_SUBMIT_BUTTON_NAME])) {
            $settings[self::FORM_SUBMIT_BUTTON_NAME] = array();
        }

        // Allow other plugins to modify form settings and storage
        $this->_application->doEvent(
            'FormBuildForm',
            array(&$settings, &$storage)
        );
        // Call with inherited form names
        if (!empty($settings['#inherits'])) {
            foreach (array_reverse($settings['#inherits']) as $inherited_form_name) {
                $this->_application->doEvent(
                    'FormBuild' . $this->_application->Camelize($inherited_form_name),
                    array(&$settings, &$storage)
                );
            }
        }
        // Call with the name of current form
        if (!empty($settings['#name'])) {
            $this->_application->doEvent(
                'FormBuild' . $this->_application->Camelize($settings['#name']),
                array(&$settings, &$storage)
            );
        }

        $form = new Sabai_Addon_Form_Form($this, $settings, $storage, $errors);
        $form->build($values);

        // Add built form to cache
        self::$_forms[$settings['#build_id']] = $form;

        return $form;
    }

    public function setFormStorage($formBuildId, $storage)
    {
        $this->_application->getPlatform()->setSessionVar('form_' . $formBuildId, $storage);
    }

    public function getFormStorage($formBuildId)
    {
        return $this->_application->getPlatform()->getSessionVar('form_' . $formBuildId);
    }

    public function clearFormStorage($formBuildId)
    {
        return $this->_application->getPlatform()->deleteSessionVar('form_' . $formBuildId);
    }

    public function onFormIFieldsInstalled(Sabai_Addon $addon, ArrayObject $log)
    {
        if ($fields = $addon->formGetFieldTypes()) {
            $this->_createFields($addon, $fields);
        }
    }

    public function onFormIFieldsUninstalled(Sabai_Addon $addon, ArrayObject $log)
    {
        $this->_deleteFields($addon);
    }

    public function onFormIFieldsUpgraded(Sabai_Addon $addon, ArrayObject $log)
    {
        if (!$fields = $addon->formGetFieldTypes()) {
            $this->_deleteFields($addon);
        } else {            
            $already_installed = $removed = array();
            foreach ($this->getModel('Field')->addon_is($addon->getName())->fetch() as $current) {
                if (!in_array($current->type, $fields)) {
                    $removed[] = $current->type;
                } else {
                    $already_installed[] = $current->type;
                }
            }
            if (!empty($removed)) {
                $this->_deleteFields($addon, $removed);
            }
            if ($new = array_diff($fields, $already_installed)) {
                $this->_createFields($addon, $new);
            }
        }
    }

    private function _createFields(Sabai_Addon $addon, array $fieldTypes)
    {
        $parent_addon_name = $addon->hasParent();
        foreach ($fieldTypes as $field_type) {
            if ($addon->getName() !== $this->_name) {
                if (!preg_match(sprintf('/^%s_[a-z]+[a-z0-9_]*[a-z0-9]+$/', strtolower($addon->getName())), $field_type)) {
                    continue;
                }
                if ($parent_addon_name && stripos($field_type, $parent_addon_name . '_') === 0) {
                    // should be handled when dealing with the parent addon
                    continue;
                }
            }
            $field = $this->getModel()->create('Field')->markNew();
            $field->type = $field_type;
            $field->addon = $addon->getName();
        }
        $this->getModel()->commit();
    }

    private function _deleteFields(Sabai_Addon $addon, array $fieldTypes = null)
    {
        if (!empty($fieldTypes)) {
            // Is this a child addon?
            if ($parent_addon_name = $addon->hasParent()) {
                foreach ($fieldTypes as $k => $field_type) {
                    if (stripos($field_type, $parent_addon_name . '_') === 0) {
                        // should be handled when dealing with the parent addon
                        unset($fieldTypes[$k]);
                    }
                }
                if (empty($fieldTypes)) {
                    return;
                }
            }
            $fields = $this->getModel('Field')->addon_is($addon->getName())->type_in($fieldTypes)->fetch();
        } else {
            $fields = $this->getModel('Field')->addon_is($addon->getName())->fetch();
        }
        $fields->delete(true);
    }
}
