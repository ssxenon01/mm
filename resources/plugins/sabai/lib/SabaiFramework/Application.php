<?php
require_once 'SabaiFramework.php';

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
abstract class SabaiFramework_Application
{
    private $_id, $_routeParam, $_helperBroker;

    /**
     * Constructor
     */
    protected function __construct($id, $routeParam)
    {
        $this->_id = $id;
        $this->_routeParam = $routeParam;
    }

    public function getId()
    {
        return $this->_id;
    }

    public function getRouteParam()
    {
        return $this->_routeParam;
    }

    /**
     *
     * @return SabaiFramework_Application_HelperBroker
     */
    public function getHelperBroker()
    {
        return $this->_helperBroker;
    }

    public function setHelperBroker(SabaiFramework_Application_HelperBroker $helperBroker)
    {
        $this->_helperBroker = $helperBroker;
    }

    /**
     * Call a helper method with the application object prepended to the arguments
     */
    public function __call($name, $args)
    {
        if (!isset($this->_helperBroker)) {
            throw new SabaiFramework_Exception(sprintf('Call to undefined method %s cannot be made without a valid application helper broker.', $method));
        }

        return $this->_helperBroker->callHelper($name, $args);
    }

    public function run(SabaiFramework_Application_Controller $controller, SabaiFramework_Application_Context $context, $route = null)
    {
        register_shutdown_function(array($this, 'shutdown'), $context);

        // Fetch route from request if none specified
        if (!isset($route)) $route = $context->getRequest()->asStr($this->_routeParam);

        $controller->setApplication($this)->setRoute($route)->execute($context);

        return $this->_createResponse()->setApplication($this);
    }

    public function shutdown(SabaiFramework_Application_Context $context)
    {
        if (($error = error_get_last())
            && $error['type'] === E_ERROR
        ) {
            $context->setError(sprintf('Fatal error: %s in %s on line %d.', $error['message'], $error['file'], $error['line']));
            $this->_createResponse()->setApplication($this)->send($context);
        }
    }

    abstract protected function _createResponse();
}