<?php
require_once 'HTML/QuickForm/checkbox.php';

/**
 * Short description for class
 *
 * @package    SabaiFramework
 * @subpackage SabaiFramework_HTMLQuickForm
 * @copyright  Copyright (c) 2006-2010 Kazumi Ono
 * @author     Kazumi Ono <onokazu@gmail.com>
 * @license    http://opensource.org/licenses/lgpl-license.php GNU LGPL
 * @version    CVS: $Id:$
 */
class SabaiFramework_HTMLQuickForm_Element_Checkbox extends HTML_QuickForm_checkbox
{    
    public function __construct($elementName = null, $elementLabel = null, $text = '', $attributes = null)
    {
        parent::HTML_QuickForm_checkbox($elementName, $elementLabel, $text, $attributes);
    }

    /*
     * Overrides the parent method to cope with the bug below
     * http://pear.php.net/bugs/bug.php?id=15298
     */
    public function getValue()
    {
        return $this->getAttribute('value');
    }
}