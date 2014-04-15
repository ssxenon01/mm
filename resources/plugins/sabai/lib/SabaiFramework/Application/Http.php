<?php
require_once 'SabaiFramework/Application.php';

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
abstract class SabaiFramework_Application_Http extends SabaiFramework_Application
{
    protected $_scriptUrls = array(), $_currentScriptName = 'main';

    /**
     * Constructor
     */
    protected function __construct($id, $routeParam)
    {
        parent::__construct($id, $routeParam);
    }

    public function run(SabaiFramework_Application_Controller $controller, SabaiFramework_Application_HttpContext $context, $route = null)
    {
        return parent::run($controller, $context, $route);
    }

    public function setCurrentScriptName($name)
    {
        $this->_currentScriptName = $name;

        return $this;
    }

    public function getCurrentScriptName()
    {
        return $this->_currentScriptName;
    }

    public function getUrl($route = '', array $params = array(), $fragment = '', $separator = '&amp;')
    {
        return $this->createUrl(array(
            'route' => $route,
            'params' => $params,
            'fragment' => $fragment,
            'separator' => $separator,
        ));
    }

    public function getScriptUrl($name = 'main')
    {
        return $this->_scriptUrls[$name];
    }
    
    public function setScriptUrl($url, $name = 'main')
    {
        $this->_scriptUrls[$name] = $url;

        return $this;
    }

    /**
     * Creates an application URL from an array of options.
     *
     * @param array $options
     * @return string
     */
    public function createUrl(array $options = array())
    {
        $options += array(
            'script_url' => null,
            'route' => '',
            'params' => array(),
            'fragment' => '',
            'script' => null,
            'separator' => '&amp;',
        );
        if (!isset($options['script_url'])) {
            $route = '/' . trim($options['route'], '/');
            if (isset($options['script']) && isset($this->_scriptUrls[$options['script']])) {
                $options['script_url'] = $this->_scriptUrls[$options['script']];
            } else {
                $options['script_url'] = $this->_scriptUrls[$this->_currentScriptName];
            }            
            // Append route data to request parameters if not the root route
            if ($route !== '/') $options['params'][$this->getRouteParam()] = $route;
        }

        return new SabaiFramework_Application_Url($options['script_url'], $options['params'], $options['fragment'], $options['separator']);
    }
}