<?php
/**
 * Short description for class
 *
 * @package    SabaiFramework
 * @subpackage SabaiFramework_Application
 * @copyright  Copyright (c) 2006-2010 Kazumi Ono
 * @author     Kazumi Ono <onokazu@gmail.com>
 * @license    http://opensource.org/licenses/lgpl-license.php GNU LGPL
 * @version    CVS: $Id:$
 */
class SabaiFramework_Application_HelperBroker
{
    protected $_application, $_helpers = array(), $_helperDir = array();

    public function __construct(SabaiFramework_Application $application)
    {
        $this->_application = $application;
    }

    public function addHelperDir($dir, $prefix)
    {
        $this->_helperDir = array($dir => $prefix) + $this->_helperDir;

        return $this;
    }

    public function callHelper($name, array $args)
    {
        array_unshift($args, $this->_application);

        return call_user_func_array($this->getHelper($name), $args);
    }

    public function getHelper($name)
    {
        if (!isset($this->_helpers[$name])) {
            if (!$this->helperExists($name)) {
                throw new SabaiFramework_Exception(sprintf('Call to undefined application helper %s.', $name));
            }
        }

        return $this->_helpers[$name];
    }

    public function helperExists($name)
    {
        foreach ($this->_helperDir as $helper_dir => $helper_prefix) {
            $class_path = sprintf('%s/%s.php', $helper_dir, $name);
            if (file_exists($class_path)) {
                require $class_path;
                $class = $helper_prefix . $name;
                $this->setHelper($name, array(new $class(), 'help'));

                return true;
            }
        }

        return false;
    }

    /**
     * Set an application helper
     * @param $name string
     * @param $helper Callable method or function
     */
    public function setHelper($name, $helper)
    {
        $this->_helpers[$name] = $helper;

        return $this;
    }

    public function hasHelper($name)
    {
        return isset($this->_helpers[$name]);
    }
}