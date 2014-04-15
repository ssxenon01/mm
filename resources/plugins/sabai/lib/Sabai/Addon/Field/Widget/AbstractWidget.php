<?php
abstract class Sabai_Addon_Field_Widget_AbstractWidget implements Sabai_Addon_Field_IWidget
{
    protected $_addon, $_name, $_info = array();

    public function __construct(Sabai_Addon_Field $addon, $name)
    {
        $this->_addon = $addon;
        $this->_name = $name;
    }

    public function fieldWidgetGetInfo($key = null)
    {
        if (!isset($this->_info[$this->_name])) {
            $this->_info[$this->_name] = $this->_fieldWidgetGetInfo();
        }

        return isset($key) ? @$this->_info[$this->_name][$key] : $this->_info[$this->_name];
    }

    abstract protected function _fieldWidgetGetInfo();
}