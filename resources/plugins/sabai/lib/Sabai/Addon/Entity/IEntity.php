<?php
interface Sabai_Addon_Entity_IEntity extends Serializable
{
    /**
     * @return int
     */
    public function getId();
    /**
     * @return string
     */
    public function getType();
    /**
     * @return int
     */
    public function getTimestamp();
    /**
     *@return string 
     */
    public function getBundleName();
    /**
     *@return string 
     */
    public function getBundleType();
    /**
     * @return string
     */
    public function getTitle();
    /**
     * @return int
     */
    public function getAuthorId();
    /**
     * Get het value of a property for use in form or display
     * @return mixed 
     * @param $name string
     */
    public function getProperty($name);
    
    public function getFieldValue($name);        
    /**
     * @return string
     */
    public function getUrlPath(Sabai_Addon_Entity_Model_Bundle $bundle, $path);
    /**
     * Sets extra field values for the entity.
     * @return Sabai_Addon_Entity_IEntity
     * @param array $values
     * @param array $types
     */
    public function initFields(array $values, array $types);
    /**
     * Returns extra field values for the entity.
     * @return array
     */
    public function getFieldValues();
}