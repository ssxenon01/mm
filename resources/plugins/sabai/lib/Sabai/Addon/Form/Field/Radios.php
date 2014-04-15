<?php
class Sabai_Addon_Form_Field_Radios extends Sabai_Addon_Form_Field_AbstractField
{
    public function formFieldGetFormElement($name, array &$data, Sabai_Addon_Form_Form $form)
    {
        if (!isset($data['#options'])) {
            $data['#options'] = array();
        }
        return $this->_createRadioButtons($name, $data, $form, $data['#options']);
    }

    protected function _createRadioButtons($name, array &$data, Sabai_Addon_Form_Form $form, array $options)
    {
        $data = array(
            '#children' => array(),
            '#tree' => true,
            '#tree_allow_override' => false,
        ) + $data + $form->defaultElementSettings();
        if (isset($data['#field_prefix']) || isset($data['#field_suffix'])) {
            if (isset($data['#class'])) {
                if (false !== strpos($data['#class'], 'sabai-form-inline')) {
                    $data['#class'] .= ' sabai-form-inline';
                }
            } else {
                $data['#class'] = 'sabai-form-inline';
            }
        }
        if (isset($data['#field_prefix'])) {  
            $data['#children'][]['prefix'] = array(
                '#type' => 'markup',
                '#markup' => '<span class="sabai-form-field-prefix">' . $data['#field_prefix'] . '</span>'
            ) + $form->defaultElementSettings();
            unset($data['#field_prefix']);
        }
        foreach ($options as $option_value => $option_label) {
            $data['#children'][][0] = $this->_createRadioButton($data, $option_value, $option_label)
                + $form->defaultElementSettings();
        }
        if (isset($data['#field_suffix'])) {
            $data['#children'][]['suffix'] = array(
                '#type' => 'markup',
                '#markup' => '<span class="sabai-form-field-prefix">' . $data['#field_suffix'] . '</span>'
            ) + $form->defaultElementSettings();
            unset($data['#field_suffix']);
        }

        return $form->createFieldset($name, $data);
    }

    protected function _createRadioButton(array $data, $value, $label)
    {
        return array(
            '#type' => 'radio',
            '#title' => $label,
            '#title_no_escape' => !empty($data['#title_no_escape']),
            '#description' => @$data['#options_description'][$value],
            '#on_value' => $value,
            '#default_value' => isset($data['#default_value']) ? $data['#default_value'] : null,
            '#value' => $data['#value'],
            '#attributes' => $data['#attributes'],
            '#disabled' => !empty($data['#options_disabled']) && in_array($value, $data['#options_disabled']),
        );
    }

    public function formFieldOnSubmitForm($name, &$value, array &$data, Sabai_Addon_Form_Form $form)
    {
        // Is it a required field?
        if (is_null($value)) {
            if ($form->isFieldRequired($data)) {
                $form->setError(isset($data['#required_error_message']) ? $data['#required_error_message'] : sprintf(__('Selection required.', 'sabai'), $data['#label'][0]), $name);
            }
            $value = array();

            return;
        }

        // No options
        if (empty($data['#options'])) {
            $value = array();

            return;
        }

        // The submitted value comes wrapped with an additional layer of array,
        // so we remove that here to get the right one.
        $value = $value[0];

        // Are all the selected options valid?
        foreach ((array)$value as $_value) {
            if (!isset($data['#options'][$_value])) {
                $form->setError(sprintf(__('Invalid option selected.', 'sabai'), $data['#label'][0]), $name);

                return;
            }
        }
    }

    public function formFieldOnCleanupForm($name, array $data, Sabai_Addon_Form_Form $form)
    {

    }

    public function formFieldOnRenderForm($name, array $data, Sabai_Addon_Form_Form $form)
    {
        $form->renderElement($data);
        
        // Process child elements
        foreach (array_keys($data['#children']) as $weight) {
            if (!is_int($weight)) continue;

            foreach ($data['#children'][$weight] as $ele_key => $ele_data) {
                try {
                    $form->renderElement($ele_data);
                } catch (Sabai_IException $e) {
                    $form->setError($e->getMessage(), $ele_name);
                }
            }
        }
    }
}