<?php
class Sabai_Addon_Field_Helper_WidgetImpl extends Sabai_Helper
{
    private $_handlers, $_impls = array();

    /**
     * Gets an implementation of Sabai_Addon_Field_IWidget interface for a given widget type
     * @param Sabai $application
     * @param string $widget
     */
    public function help(Sabai $application, $widget)
    {
        if (!isset($this->_impls[$widget])) {
            // Widget handlers initialized?
            if (!isset($this->_handlers)) {
                $this->_loadWidgetHandlers($application);
            }
            // Valid widget type?
            if (!isset($this->_handlers[$widget])
                || (!$widget_plugin = $application->getAddon($this->_handlers[$widget]))
            ) {
                throw new Sabai_UnexpectedValueException(sprintf('Invalid widget type: %s', $widget));
            }
            $this->_impls[$widget] = $widget_plugin->fieldGetWidget($widget);
        }

        return $this->_impls[$widget];
    }

    private function _loadWidgetHandlers(Sabai $application)
    {
        $this->_handlers = array();
        foreach ($application->getModel('Widget', 'Field')->fetch() as $widget) {
            $this->_handlers[$widget->name] = $widget->addon;
        }
    }
    
    public function reset(Sabai $application)
    {
        $this->_handlers = null;
    }
}