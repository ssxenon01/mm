<?php
class Sabai_Addon_Field_Widget_SectionBreak extends Sabai_Addon_Field_Widget_AbstractWidget
{
    protected function _fieldWidgetGetInfo()
    {
        return array(
            'label' => __('Section Break', 'sabai'),
            'field_types' => array('sectionbreak'),
            'default_settings' => array(
                
            ),
            'disable_edit_required' => true,
            'enable_edit_disabled' => true,
            'disable_preview_title' => true,
            'disable_preview_description' => true,
        );
    }

    public function fieldWidgetGetSettingsForm($fieldType, array $fieldSettings, array $settings, array $parents = array())
    {
        return array(

        );
    }

    public function fieldWidgetGetForm(Sabai_Addon_Field_IField $field, array $settings, $value = null, array $parents = array())
    {
        return array(
            '#type' => 'sectionbreak',
            '#title' => $field->getFieldTitle(),
            '#description' => $field->getFieldDescription(),
        );
    }
    
    public function fieldWidgetGetPreview(Sabai_Addon_Field_IField $field, array $settings)
    {
        return sprintf(
            '<div class="sabai-form-field sabai-form-type-sectionbreak"><h2 class="sabai-form-field-label">%s</h2>%s</div>',
            Sabai::h($field->getFieldTitle()),
            $field->getFieldDescription() ? '<div class="sabai-form-field-description sabai-form-field-description-top">' . $field->getFieldDescription() . '</div>' : ''
        );
    }

    public function fieldWidgetGetEditDefaultValueForm($fieldType, array $fieldSettings, array $settings, array $parents = array())
    {

    }
}