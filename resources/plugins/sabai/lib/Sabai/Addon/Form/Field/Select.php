<?php
class Sabai_Addon_Form_Field_Select extends Sabai_Addon_Form_Field_AbstractField
{
    public function formFieldGetFormElement($name, array &$data, Sabai_Addon_Form_Form $form)
    {
        $options = $this->_getOptions($data);
        if ($data['#multiple'] = !empty($data['#multiple'])) {
            $attr = array(
                'size' => isset($data['#size']) ? $data['#size'] : ((10 < $count = count($options)) ? 10 : $count),
                'multiple' => 'multiple'
            );
        } else {
            $attr = array('size' => 1);
        }

        return $form->createHTMLQuickformElement('select', $name, $data['#label'], $options, array_merge($data['#attributes'], $attr));
    }

    public function formFieldOnSubmitForm($name, &$value, array &$data, Sabai_Addon_Form_Form $form)
    {
        // Is it a required field?
        if (is_null($value)) {
            if ($form->isFieldRequired($data)) {
                $form->setError(isset($data['#required_error_message']) ? $data['#required_error_message'] : __('Selection is required for this field.', 'sabai'), $name);
            }
            $value = array();

            return;
        }

        // No options
        if (!$options = $this->_getOptions($data)) {
            $value = array();
            
            return;
        }

        // Are all the selected options valid?
        $value = (array)$value;
        foreach ($value as $k => $_value) {
            if (!isset($options[$_value])) {
                $form->setError(__('Invalid option selected.', 'sabai'), $name);

                return;
            }
            if (isset($data['#empty_value']) && $_value == $data['#empty_value']) {
                unset($value[$k]);
            }
        }
        
        if (empty($value) && $form->isFieldRequired($data)) {
            $form->setError(isset($data['#required_error_message']) ? $data['#required_error_message'] : __('Selection is required for this field.', 'sabai'), $name);
            return;
        }
        
        if (!$data['#multiple']) {
            $value = isset($value[0]) ? $value[0] : null;
            return;
        }

        if (!empty($data['#max_selection']) && count($value) > $data['#max_selection']) {
            $form->setError(sprintf(__('Maximum of %d selections is allowed for this field.', 'sabai'), $data['#max_selection']), $name);
        }
    }

    public function formFieldOnCleanupForm($name, array $data, Sabai_Addon_Form_Form $form)
    {

    }

    public function formFieldOnRenderForm($name, array $data, Sabai_Addon_Form_Form $form)
    {
        $form->renderElement($data);
    }
    
    protected function _getOptions(array $data)
    {
        return isset($data['#options']) ? array_map(array('Sabai', 'h'), $data['#options']) : array();
    }
}