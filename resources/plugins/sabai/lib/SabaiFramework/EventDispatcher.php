<?php
/**
 * Short description for class
 *
 * @package    SabaiFramework
 * @subpackage SabaiFramework_EventDispatcher
 * @copyright  Copyright (c) 2006-2010 Kazumi Ono
 * @author     Kazumi Ono <onokazu@gmail.com>
 * @license    http://opensource.org/licenses/lgpl-license.php GNU LGPL
 * @version    CVS: $Id:$
 */
abstract class SabaiFramework_EventDispatcher
{
    protected $_listeners = array();

    public function addListener($eventType, $listenerName, $priority = 10)
    {
        $this->_listeners[strtolower($eventType)][$priority][] = $listenerName;
    }

    public function dispatch($eventType, array $eventArgs = array())
    {
        $event_type = strtolower($eventType);
        if (empty($this->_listeners[$event_type])) return;

        ksort($this->_listeners[$event_type]);
        $event = new SabaiFramework_EventDispatcher_Event($eventType, $eventArgs);
        foreach ($this->_listeners[$event_type] as $listeners) {
            foreach ($listeners as $listener_name) {
                $this->_dispatchEvent($listener_name, $event);
            }
        }
    }
 
    public function getListeners()
    {
        return $this->_listeners;
    }

    public function clear()
    {
        $this->_listeners = array();
    }

    abstract protected function _dispatchEvent($listenerName, SabaiFramework_EventDispatcher_Event $event);
}