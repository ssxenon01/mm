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
abstract class SabaiFramework_Criteria_Field extends SabaiFramework_Criteria
{
    private $_field;
    private $_field2;

    public function __construct($field1, $field2)
    {
        $this->_field = $field;
        $this->_field2 = $field2;
    }

    public function getField()
    {
        return $this->_field;
    }

    public function getField2()
    {
        return $this->_field2;
    }
}
