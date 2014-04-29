<?php
class Sabai_Addon_Entity_Helper_TypeImpl extends Sabai_Helper
{
    private $_handlers, $_impls = array();

    /**
     * Gets an implementation of Sabai_Addon_Entity_IType interface for a given entity type
     * @param Sabai $application
     * @param string $entityType
     */
    public function help(Sabai $application, $entityType, $useCache = true)
    {
        if (!isset($this->_impls[$entityType])) {
            // Entity type handlers initialized?
            if (!isset($this->_handlers) || !$useCache) {
                $this->_loadEntityTypeHandlers($application);
            }
            // Valid entity type?
            if (!isset($this->_handlers[$entityType])
                || (!$entity_type_plugin = $application->getAddon($this->_handlers[$entityType]))
            ) {
                throw new Sabai_UnexpectedValueException(sprintf('Invalid entity type: %s', $entityType));
            }
            $this->_impls[$entityType] = $entity_type_plugin->entityGetType($entityType);
        }

        return $this->_impls[$entityType];
    }

    private function _loadEntityTypeHandlers(Sabai $application)
    {
        $this->_handlers = array();
        foreach ($application->getModel('EntityType', 'Entity')->fetch() as $entity_type) {
            $this->_handlers[$entity_type->name] = $entity_type->addon;
        }
    }
    
    public function reset(Sabai $application)
    {
        $this->_handlers = null;
    }
}