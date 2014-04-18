<?php
interface Sabai_Addon_Field_IWidget
{
    public function fieldWidgetGetInfo($key = null);
    public function fieldWidgetGetSettingsForm($fieldType, array $fieldSettings, array $settings, array $parents = array());
    public function fieldWidgetGetForm(Sabai_Addon_Field_IField $field, array $settings, $value = null, array $parents = array());
    public function fieldWidgetGetEditDefaultValueForm($fieldType, array $fieldSettings, array $settings, array $parents = array());
}