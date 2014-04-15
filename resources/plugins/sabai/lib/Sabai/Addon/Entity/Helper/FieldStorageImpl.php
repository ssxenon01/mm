<?php
class Sabai_Addon_Entity_Helper_FieldStorageImpl extends Sabai_Helper
{
    private $_handlers, $_impls = array();

    /**
     * Gets an implementation of Sabai_Addon_Entity_IFieldStorage interface for a given field type
     * @param Sabai $application
     * @param string $storage
     */
    public function help(Sabai $application, $storage)
    {
        if (!isset($this->_impls[$storage])) {
            // Storage handlers initialized?
            if (!isset($this->_handlers)) {
                $this->_loadFieldStorageHandlers($application);
            }
            // Valid storage type?
            if (!isset($this->_handlers[$storage])
                || (!$storage_plugin = $application->getAddon($this->_handlers[$storage]))
            ) {
                throw new Sabai_UnexpectedValueException(sprintf('Invalid field storage: %s', $storage));
            }
            $this->_impls[$storage] = $storage_plugin->entityGetFieldStorage($storage);
        }

        return $this->_impls[$storage];
    }

    private function _loadFieldStorageHandlers(Sabai $application)
    {
        $this->_handlers = array();
        foreach ($application->getModel('FieldStorage', 'Entity')->fetch() as $storage) {
            $this->_handlers[$storage->name] = $storage->addon;
        }
    }
    
    public function reset(Sabai $application)
    {
        $this->_handlers = null;
    }
}