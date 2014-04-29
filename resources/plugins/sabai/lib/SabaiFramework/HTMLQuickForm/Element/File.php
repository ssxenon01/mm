<?php
require_once 'HTML/QuickForm/file.php';

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
class SabaiFramework_HTMLQuickForm_Element_File extends HTML_QuickForm_file
{
    protected $_multiple = false;
    
    public function __construct($elementName = null, $elementLabel = null, $attributes = null)
    {
        parent::HTML_QuickForm_file($elementName, $elementLabel, $attributes);
    }
    
    public function setMultiple($flag = true)
    {
        $this->_multiple = (bool)$flag;
        if ($flag) {
            $this->setAttribute('multiple', 'multiple');
        } else {
            $this->removeAttribute('multiple');
        }
    }

    /**
     * Overrides the parent class so that file values will be included
     * in exported values
     * 
     * @param $submitValues
     * @param $assoc
     */
    public function exportValue(&$submitValues, $assoc = false)
    {
        $value = $this->_findValue($submitValues);
        if (null === $value) {
            $value = $this->getValue();
        }
        
        return $this->_prepareValue($value, $assoc);
    }
    
    public function toHtml()
    {
        if ($this->_flagFrozen) return $this->getFrozenHtml();
        
        if (!$this->_multiple) {
            $attr = $this->_getAttrString($this->_attributes);
        } else {
            $name = $this->getName();
            $this->setName($name . '[]');
            $attr = $this->_getAttrString($this->_attributes);
            $this->setName($name);
        }
        
        return $this->_getTabs() . '<input' . $attr . ' />';
    }
}
