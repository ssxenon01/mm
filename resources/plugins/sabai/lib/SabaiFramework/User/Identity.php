<?php
/**
 * Short description for class
 *
 * @package    SabaiFramework
 * @subpackage SabaiFramework_User
 * @copyright  Copyright (c) 2006-2010 Kazumi Ono
 * @author     Kazumi Ono <onokazu@gmail.com>
 * @license    http://opensource.org/licenses/lgpl-license.php GNU LGPL
 * @version    CVS: $Id:$
 */
abstract class SabaiFramework_User_Identity implements Serializable
{
    protected $_data;

    /**
     * Constructor
     *
     * @param array $data Data associated with the identity
     * @return SabaiFramework_User_Identity
     */
    protected function __construct(array $data = array())
    {
        $this->_data = $data;
    }

    public function serialize()
    {
        return serialize($this->_data);
    }

    public function unserialize($serialized)
    {
        $this->_data = unserialize($serialized);
    }

    /**
     * Returns the data associated with the identity
     * @return array
     */
    public function getData()
    {
        return $this->_data;
    }

    /**
     * Checks if the identity is anonymous
     * @return bool
     */
    abstract public function isAnonymous();

    /**
     * Magic method
     *
     * @param string $key
     */
    public function __get($key)
    {
        return array_key_exists($key, $this->_data) ? $this->_data[$key] : null;
    }

    /**
     * Magic method
     *
     * @param string $key
     */
    public function __isset($key)
    {
        return array_key_exists($key, $this->_data) && isset($this->_data[$key]);
    }
}