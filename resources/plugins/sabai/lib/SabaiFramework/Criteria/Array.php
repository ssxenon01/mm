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
abstract class SabaiFramework_Criteria_Array extends SabaiFramework_Criteria
{
    private $_field;
    private $_array;

    public function __construct($field, array $array)
    {
        $this->_field = $field;
        $this->_array = $array;
    }

    public function getField()
    {
        return $this->_field;
    }

    public function getArray()
    {
        return $this->_array;
    }
}
