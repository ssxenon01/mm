<?php
/**
 * Short description for class
 *
 * @package    SabaiFramework
 * @subpackage SabaiFramework_Criteria
 * @copyright  Copyright (c) 2006-2011 Kazumi Ono
 * @author     Kazumi Ono <onokazu@gmail.com>
 * @license    http://opensource.org/licenses/lgpl-license.php GNU LGPL
 * @version    CVS: $Id:$
 */
class SabaiFramework_Criteria
{
    const CRITERIA_AND = 'AND', CRITERIA_OR = 'OR';

    /**
     * Checks if the criteria is empty
     *
     * @return bool
     */
    public function isEmpty()
    {
        return false;
    }
}