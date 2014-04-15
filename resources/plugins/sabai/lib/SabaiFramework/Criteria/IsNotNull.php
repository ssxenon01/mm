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
class SabaiFramework_Criteria_IsNotNull extends SabaiFramework_Criteria
{
    private $_field;

    public function __construct($field)
    {
        $this->_field = $field;
    }

    public function getField()
    {
        return $this->_field;
    }

    /**
     * Accepts a Visitor object
     *
     * @param SabaiFramework_Criteria_Visitor $visitor
     * @param mixed $valuePassed
     */
    public function acceptVisitor(SabaiFramework_Criteria_Visitor $visitor, &$valuePassed)
    {
        $visitor->visitCriteriaIsNotNull($this, $valuePassed);
    }
}
