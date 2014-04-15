<?php
class Sabai_Addon_Entity_Helper_Type extends Sabai_Helper
{
    private $_info = array();

    /**
     * Gets info of a given entity type
     * @param Sabai $application
     * @param string $entityType
     * @return array
     */
    public function help(Sabai $application, $entityType)
    {
        if (!isset($this->_info[$entityType])) {
            $this->_info[$entityType] = $application->Entity_TypeImpl($entityType)->entityTypeGetInfo();
        }
        return $this->_info[$entityType];
    }
}