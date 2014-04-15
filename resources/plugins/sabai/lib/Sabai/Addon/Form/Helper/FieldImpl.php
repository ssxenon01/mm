<?php
class Sabai_Addon_Form_Helper_FieldImpl extends Sabai_Helper
{
    private $_handlers, $_impls = array();

    /**
     * Gets an implementation of Sabai_Addon_Form_IField interface for a field type
     * @param Sabai $application
     * @param string $fieldType
     */
    public function help(Sabai $application, $fieldType)
    {
        if (!isset($this->_impls[$fieldType])) {
            // Field handlers initialized?
            if (!isset($this->_handlers)) {
                $this->_loadFieldHandlers($application);
            }
            // Valid field type?
            if (!isset($this->_handlers[$fieldType])) {
                throw new Sabai_UnexpectedValueException(sprintf('Invalid form field type: %s', $fieldType));
            }
            $this->_impls[$fieldType] = $application->getAddon($this->_handlers[$fieldType])->formGetField($fieldType);
        }

        return $this->_impls[$fieldType];
    }

    private function _loadFieldHandlers(Sabai $application)
    {
        if (!class_exists('SabaiFramework_HTMLQuickForm', false)) {
            require 'SabaiFramework/HTMLQuickForm.php';
        }

        $this->_handlers = array();
        foreach ($application->getModel('Field', 'Form')->fetch(0, 0, 'priority', 'ASC') as $field) {
            if (!$application->isAddonLoaded($field->addon)) continue;

            $this->_handlers[$field->type] = $field->addon;
        }
    }
}