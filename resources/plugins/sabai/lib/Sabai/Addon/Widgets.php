<?php
class Sabai_Addon_Widgets extends Sabai_Addon
{
    const VERSION = '1.2.18', PACKAGE = 'sabai';
                
    public function isUninstallable($currentVersion)
    {
        return false;
    }

    public function onWidgetsIWidgetsInstalled(Sabai_Addon $addon, ArrayObject $log)
    {
        if ($widgets = $addon->widgetsGetWidgetNames()) {
            $this->_createWidgets($addon, $widgets);
        }
    }

    public function onWidgetsIWidgetsUninstalled(Sabai_Addon $addon, ArrayObject $log)
    {
        $this->_deleteWidgets($addon);
    }

    public function onWidgetsIWidgetsUpgraded(Sabai_Addon $addon, ArrayObject $log)
    {
        if (!$widgets = $addon->widgetsGetWidgetNames()) {
            $this->_deleteWidgets($addon);
        } else {
            $widgets_already_installed = array();
            foreach ($this->getModel('Widget')->addon_is($addon->getName())->fetch() as $current_widget) {
                if (in_array($current_widget->name, $widgets)) {
                    $widgets_already_installed[] = $current_widget->name;
                } else {
                    // This widget does not exist any more
                    $current_widget->markRemoved();
                }
            }
            $this->_createWidgets($addon, array_diff($widgets, $widgets_already_installed));
        }
    }

    private function _createWidgets($addon, $widgets)
    {
        $model = $this->getModel();
        foreach ($widgets as $name) {
            if ($addon->getName() !== $this->_name) {
                if (!preg_match(sprintf('/^%s_[a-z]+[a-z0-9_]*[a-z0-9]+$/', strtolower($addon->getName())), $name)) {
                    continue;
                }
            }
            $widget = $model->create('Widget')->markNew();
            $widget->name = $name;
            $widget->addon = $addon->getName();
        }
        $model->commit();
    }

    private function _deleteWidgets($addon)
    {
        $model = $this->getModel();
        foreach ($model->Widget->addon_is($addon->getName())->fetch() as $widget) {
            $widget->markRemoved();
        }
        $model->commit();
    }
}