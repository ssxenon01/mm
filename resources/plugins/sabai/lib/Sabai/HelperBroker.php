<?php
class Sabai_HelperBroker extends SabaiFramework_Application_HelperBroker
{
    protected $_instances = array();
    
    public function helperExists($name)
    {
        if (parent::helperExists($name)) return true; // helper found

        if (!strpos($name, '_', 1)) return false; // global helper not found

        // Search plugin's helper directory
        if ((list($addon_name, $helper_name) = explode('_', $name))
            //&& $this->_application->isAddonLoaded($addon_name) // the plugin must be active
        ) {
            // We do not use autoloading here since the addon may be outside the core
            require_once $this->_application->getAddonPath($addon_name) . '/Helper/' . $helper_name . '.php';
            $class = 'Sabai_Addon_' . $addon_name . '_Helper_' . $helper_name;
            $this->_instances[$name] = new $class();
            $this->setHelper($name, array($this->_instances[$name], 'help'));

            return true;
        }

        return false;
    }
    
    public function callHelper($name, array $args)
    {
        array_unshift($args, $this->_application);
        $callback = $this->getHelper($name);
        // Append additional args if any
        if (is_array($callback) && is_array($callback[1])) {
            $args = empty($args) ? $callback[1] : array_merge($args, $callback[1]);
            $callback = $callback[0];
        }
        return call_user_func_array($callback, $args);
    }
    
    public function getHelperInstance($name)
    {
        return isset($this->_instances[$name]) ? $this->_instances[$name] : null;
    }
    
    public function resetHelper($name)
    {
        if (isset($this->_instances[$name])) {
            $this->_instances[$name]->reset($this->_application);
        }
    }
}