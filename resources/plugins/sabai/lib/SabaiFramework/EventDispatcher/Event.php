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
class SabaiFramework_EventDispatcher_Event
{
    private $_type;
    private $_vars;

    public function __construct($type, array $vars = array())
    {
        $this->_type = $type;
        $this->_vars = $vars;
    }

    public function getType()
    {
        return $this->_type;
    }

    public function getVars()
    {
        return $this->_vars;
    }
}