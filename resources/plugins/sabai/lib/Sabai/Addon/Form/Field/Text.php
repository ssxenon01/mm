<?php
class Sabai_Addon_Form_Field_Text extends Sabai_Addon_Form_Field_AbstractField
{
    public function formFieldGetFormElement($name, array &$data, Sabai_Addon_Form_Form $form)
    {
        if (isset($data['#separator'])) {
            // value is an array, so it must be converted to a string
            if (isset($data['#default_value'])) {
                $data['#default_value'] = implode($data['#separator'], $data['#default_value']);
            }
            if (isset($data['#value'])) {
                $data['#value'] = implode($data['#separator'], $data['#value']);
            }
        }
        
        $this->_addon->initTextFormElementSettings($form, $data);

        return $form->createHTMLQuickformElement('text', $name, $data['#label'], $data['#attributes']);
    }

    public function formFieldOnSubmitForm($name, &$value, array &$data, Sabai_Addon_Form_Form $form)
    {
        if (isset($data['#separator'])) {
            $value = explode($data['#separator'], $value);
            foreach (array_keys($value) as $key) {
                if (!$this->_addon->validateFormElementText($form, $value[$key], $data, null, false)) {
                    return;
                }
                if (!strlen($value[$key])) {
                    unset($value[$key]);
                }
            }
            if (empty($value)) {
                if ($form->isFieldRequired($data)) {
                    $form->setError(isset($data['#required_error_message']) ? $data['#required_error_message'] : __('Please fill out this field.', 'sabai'), $name);

                    return;
                }
            } else {
                if (!empty($data['#max_selection'])) {
                    if (count($value) > $data['#max_selection']) {
                        $form->setError(sprintf(__('Maximum of %d items is allowed for this field.', 'sabai'), $data['#max_selection']), $name);
                    }
                }
            }
        } else {
            $this->_addon->validateFormElementText($form, $value, $data);
        }
    }

    public function formFieldOnCleanupForm($name, array $data, Sabai_Addon_Form_Form $form)
    {

    }

    public function formFieldOnRenderForm($name, array $data, Sabai_Addon_Form_Form $form)
    {
        $form->renderElement($data);
    }
}