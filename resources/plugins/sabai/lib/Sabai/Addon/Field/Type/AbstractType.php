<?php
abstract class Sabai_Addon_Field_Type_AbstractType implements Sabai_Addon_Field_IType
{
    protected $_addon, $_name, $_info;

    public function __construct(Sabai_Addon $addon, $name)
    {
        $this->_addon = $addon;
        $this->_name = $name;
    }

    public function fieldTypeGetInfo($key = null)
    {
        if (!isset($this->_info)) {
            $this->_info = $this->_fieldTypeGetInfo();
        }

        return isset($key) ? @$this->_info[$key] : $this->_info;
    }

    public function fieldTypeOnSave(Sabai_Addon_Field_IField $field, array $values)
    {
        $ret = array();
        foreach ($values as $weight => $value) {
            $value = is_array($value) && isset($value['value']) ? (string)$value['value'] : (string)$value;
            if (strlen($value) === 0) continue;

            $ret[]['value'] = $value;
        }

        return $ret;
    }

    public function fieldTypeOnLoad(Sabai_Addon_Field_IField $field, array &$values)
    {
        foreach ($values as $key => $value) {
            $values[$key] = $value['value'];
        }
    }
    
    public function fieldTypeIsModified($field, $valueToSave, $currentLoadedValue)
    {   
        $new = array();
        foreach ($valueToSave as $value) {
            $new[] = $value['value'];
        }
        return $currentLoadedValue !== $new;
    }

    public function validateMinMaxSettings($form, $value, $element)
    {
        if (!empty($value['min']) && !empty($value['max'])) {
            if ($value['min'] >= $value['max']) {
                $form->setError(__('The value must be greater than the "Minimum" value.', 'sabai'), $element['#name'] . '[max]');
            }
        }
    }

    abstract protected function _fieldTypeGetInfo();
}