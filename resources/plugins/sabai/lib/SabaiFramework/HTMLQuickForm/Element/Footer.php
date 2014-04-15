<?php
require_once 'HTML/QuickForm/static.php';

class SabaiFramework_HTMLQuickForm_Element_Footer extends HTML_QuickForm_static
{
    // {{{ constructor

   /**
    * Class constructor
    * 
    * @param string $elementName    Header name
    * @param string $text           Header text
    * @access public
    * @return void
    */
    public function __construct($elementName = null, $text = null)
    {
        $this->HTML_QuickForm_static($elementName, null, $text);
        $this->_type = 'footer';
    }

    // }}}
    // {{{ accept()

   /**
    * Accepts a renderer
    *
    * @param HTML_QuickForm_Renderer    renderer object
    * @access public
    * @return void 
    */
    public function accept($renderer)
    {
        if (is_callable(array($renderer, 'renderFooter'))) {
            $renderer->renderFooter($this);
        } else {
            $renderer->renderHeader($this); // for BC
        }
    }
}