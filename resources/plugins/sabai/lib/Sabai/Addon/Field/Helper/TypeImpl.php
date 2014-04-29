<?php
class Sabai_Addon_Field_Helper_TypeImpl extends Sabai_Helper
{
    private $_fieldTypeHandlers, $_fieldTypeImpls = array();

    /**
     * Gets an implementation of Sabai_Addon_Field_IType interface for a given field type
     * @param Sabai $application
     * @param string $fieldType
     * @param bool $useCache
     */
    public function help(Sabai $application, $fieldType, $useCache = true)
    {
        if (!isset($this->_fieldTypeImpls[$fieldType])) {
            // Field handlers initialized?
            if (!isset($this->_fieldTypeHandlers) || !$useCache) {
                $this->_loadFieldTypeHandlers($application, $useCache);
            }
            // Valid field type?
            if (!isset($this->_fieldTypeHandlers[$fieldType])
                || (!$field_type_plugin = $application->getAddon($this->_fieldTypeHandlers[$fieldType]))
            ) {
                throw new Sabai_UnexpectedValueException(sprintf('Invalid field type: %s', $fieldType));
            }
            $this->_fieldTypeImpls[$fieldType] = $field_type_plugin->fieldGetType($fieldType);
        }

        return $this->_fieldTypeImpls[$fieldType];
    }

    private function _loadFieldTypeHandlers(Sabai $application, $useCache)
    {
        $this->_fieldTypeHandlers = array();
        foreach ($application->Field_Types($useCache) as $fieldtype_name => $fieldtype_data) {
            $this->_fieldTypeHandlers[$fieldtype_name] = $fieldtype_data['addon'];
        }
    }
}