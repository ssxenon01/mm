<?php
/**
 * Short description for class
 *
 * @package    SabaiFramework
 * @subpackage SabaiFramework_Criteria
 * @copyright  Copyright (c) 2006-2010 Kazumi Ono
 * @author     Kazumi Ono <onokazu@gmail.com>
 * @license    http://opensource.org/licenses/lgpl-license.php GNU LGPL
 * @version    CVS: $Id:$
 */
abstract class SabaiFramework_Criteria_String extends SabaiFramework_Criteria
{
    private $_field;
    private $_string;

    public function __construct($field, $string)
    {
        $this->_field = $field;
        $this->_string = strval($string);
    }

    public function getField()
    {
        return $this->_field;
    }

    public function getString()
    {
        return $this->_string;
    }
}
