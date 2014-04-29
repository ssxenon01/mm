<?php
class Sabai_Addon_Field extends Sabai_Addon
    implements Sabai_Addon_Field_ITypes,
               Sabai_Addon_Field_IWidgets
{
    const VERSION = '1.2.30', PACKAGE = 'sabai';
    const COLUMN_TYPE_INTEGER = 'integer', COLUMN_TYPE_BOOLEAN = 'boolean', COLUMN_TYPE_VARCHAR = 'text',
        COLUMN_TYPE_TEXT = 'clob', COLUMN_TYPE_FLOAT = 'float', COLUMN_TYPE_DECIMAL = 'decimal';
                
    public function isUninstallable($currentVersion)
    {
        return false;
    }

    public function fieldGetTypeNames()
    {
        return array('boolean', 'number', 'string', 'text', 'user', 'html', 'choice', 'captcha', 'sectionbreak', 'link');
    }

    public function fieldGetType($name)
    {
        switch ($name) {
            case 'boolean':
                return new Sabai_Addon_Field_Type_Boolean($this, $name);
            case 'number':
                return new Sabai_Addon_Field_Type_Number($this, $name);
            case 'string':
                return new Sabai_Addon_Field_Type_String($this, $name);
            case 'text':
                return new Sabai_Addon_Field_Type_Text($this, $name);
            case 'user':
                return new Sabai_Addon_Field_Type_User($this, $name);
            case 'html':
                return new Sabai_Addon_Field_Type_HTML($this, $name);
            case 'choice':
                return new Sabai_Addon_Field_Type_Choice($this, $name);
            case 'captcha':
                return new Sabai_Addon_Field_Type_CAPTCHA($this, $name);
            case 'sectionbreak':
                return new Sabai_Addon_Field_Type_SectionBreak($this, $name);
            case 'link':
                return new Sabai_Addon_Field_Type_Link($this, $name);
        }
    }

    public function fieldGetWidgetNames()
    {
        return array('textfield', 'textarea', 'select', 'radiobuttons', 'checkboxes', 'checkbox', 'user_select', 'html', 'sectionbreak', 'link');
    }

    public function fieldGetWidget($name)
    {
        switch ($name) {
            case 'textfield':
                return new Sabai_Addon_Field_Widget_Textfield($this, $name);
            case 'textarea':
                return new Sabai_Addon_Field_Widget_Textarea($this, $name);
            case 'select':
                return new Sabai_Addon_Field_Widget_Select($this, $name);
            case 'radiobuttons':
                return new Sabai_Addon_Field_Widget_RadioButtons($this, $name);
            case 'checkboxes':
                return new Sabai_Addon_Field_Widget_Checkboxes($this, $name);
            case 'checkbox':
                return new Sabai_Addon_Field_Widget_Checkbox($this, $name);
            case 'user_select':
                return new Sabai_Addon_Field_Widget_User($this, $name);
            case 'html':
                return new Sabai_Addon_Field_Widget_HTML($this, $name);
            case 'sectionbreak':
                return new Sabai_Addon_Field_Widget_SectionBreak($this, $name);
            case 'link':
                return new Sabai_Addon_Field_Widget_Link($this, $name);
        }
    }

    public function onFieldITypesInstalled(Sabai_Addon $addon, ArrayObject $log)
    {
        $this->_onFieldFeatureInstalled('Type', $addon, $log);
    }

    public function onFieldITypesUninstalled(Sabai_Addon $addon, ArrayObject $log)
    {
        $this->_onFieldFeatureUninstalled('Type', $addon, $log);
    }

    public function onFieldITypesUpgraded(Sabai_Addon $addon, ArrayObject $log)
    {
        $this->_onFieldFeatureUpgraded('Type', $addon, $log);
    }

    public function onFieldIWidgetsInstalled(Sabai_Addon $addon, ArrayObject $log)
    {
        $this->_onFieldFeatureInstalled('Widget', $addon, $log);
    }

    public function onFieldIWidgetsUninstalled(Sabai_Addon $addon, ArrayObject $log)
    {
        $this->_onFieldFeatureUninstalled('Widget', $addon, $log);
    }

    public function onFieldIWidgetsUpgraded(Sabai_Addon $addon, ArrayObject $log)
    {
        $this->_onFieldFeatureUpgraded('Widget', $addon, $log);
    }

    private function _onFieldFeatureInstalled($feature, Sabai_Addon $addon, ArrayObject $log)
    {
        $method = 'fieldGet' . $feature . 'Names';
        if (!$names = $addon->$method()) return;

        $this->_createFieldFeature($addon, $feature, $names);
    }

    private function _onFieldFeatureUninstalled($feature, Sabai_Addon $addon, ArrayObject $log)
    {
        $this->_deleteFieldFeature($addon, $feature);
    }

    private function _onFieldFeatureUpgraded($feature, Sabai_Addon $addon, ArrayObject $log)
    {
        $method = 'fieldGet' . $feature . 'Names';
        if (!$names = $addon->$method()) {
            $this->_deleteFieldFeature($addon, $feature);
        } else {
            $already_installed = $removed = array();
            foreach ($this->getModel($feature)->addon_is($addon->getName())->fetch() as $current) {
                if (!in_array($current->name, $names)) {
                    $removed[] = $current->name;
                } else {
                    $already_installed[] = $current->name;
                }
            }
            $cache_cleared = false;
            if (!empty($removed)) {
                $this->_deleteFieldFeature($addon, $feature, $removed);
                $cache_cleared = true;
            }
            if ($new = array_diff($names, $already_installed)) {
                $this->_createFieldFeature($addon, $feature, $new);
                $cache_cleared = true;
            }
            if (!$cache_cleared) {
                $this->_application->getPlatform()->deleteCache('field_types')->deleteCache('field_schema');
            }
        }
    }

    private function _createFieldFeature(Sabai_Addon $addon, $featureName, array $names)
    {
        $features = array();
        $parent_addon_name = $addon->hasParent();
        foreach ($names as $name) {
            if ($addon->getName() !== $this->_name) {
                if (!preg_match(sprintf('/^%s_[a-z]+[a-z0-9_]*[a-z0-9]+$/', strtolower($addon->getName())), $name)) {
                    continue;
                }
                if ($parent_addon_name && stripos($name, $parent_addon_name . '_') === 0) {
                    // should be handled when dealing with the parent addon
                    continue;
                }
            }
            $feature = $this->getModel()->create($featureName)->markNew();
            $feature->name = $name;
            $feature->addon = $addon->getName();
            $features[] = $feature;
        }
        $this->getModel()->commit();

        // Clear old cache to reflect changes
        $this->_application->getPlatform()->deleteCache('field_types')->deleteCache('field_schema');
        if ($featureName !== 'Type') {
            $this->_application->getPlatform()->deleteCache('field_' . strtolower($featureName) . '_plugins', $this->_name);
        }

        // Notify of creation
        foreach ($features as $feature) {
            $this->_application->doEvent('Field' . $featureName . 'Created', array($feature));
        }
    }

    private function _deleteFieldFeature(Sabai_Addon $addon, $featureName, array $names = null)
    {
        if (!empty($names)) {
            // Is this a child addon?
            if ($parent_addon_name = $addon->hasParent()) {
                foreach ($names as $k => $name) {
                    if (stripos($name, $parent_addon_name . '_') === 0) {
                        // should be handled when dealing with the parent addon
                        unset($names[$k]);
                    }
                }
                if (empty($names)) {
                    return;
                }
            }
            $features = $this->getModel($featureName)->addon_is($addon->getName())->name_in($names)->fetch();
        } else {
            $features = $this->getModel($featureName)->addon_is($addon->getName())->fetch();
        }
        $features->delete(true);

        // Clear old cache to reflect changes
        // We do not want to clear shema cache here otherwise the Entity plugin will not know which table to delete
        $this->_application->getPlatform()->deleteCache('field_types');
        if ($featureName !== 'Type') {
            $this->_application->getPlatform()->deleteCache('field_' . strtolower($featureName) . '_plugins', $this->_name);
        }

        // Notify of deletion
        foreach ($features as $feature) {
            $this->_application->doEvent('Field' . $featureName . 'Deleted', array($feature));
        }
    }
}