<?php
class Sabai_Addon_Form_Field_SectionBreak extends Sabai_Addon_Form_Field_AbstractField
{
    public function formFieldGetFormElement($name, array &$data, Sabai_Addon_Form_Form $form)
    {
        $data['#template'] = '<div<!-- BEGIN id --> id="{id}"<!-- END id --> class="{class_prefix}form-field<!-- BEGIN class --> {class}<!-- END class -->">
  <!-- BEGIN label --><h2 class="{class_prefix}form-field-label">{label}</h2><!-- END label -->
  <!-- BEGIN label_2 --><div class="{class_prefix}form-field-description {class_prefix}form-field-description-top">{label_2}</div><!-- END label_2 -->
</div>';

        return $form->createHTMLQuickformElement('static', $name, $data['#label'], '');
    }

    public function formFieldOnSubmitForm($name, &$value, array &$data, Sabai_Addon_Form_Form $form)
    {

    }

    public function formFieldOnCleanupForm($name, array $data, Sabai_Addon_Form_Form $form)
    {

    }

    public function formFieldOnRenderForm($name, array $data, Sabai_Addon_Form_Form $form)
    {
        $form->renderElement($data);
    }
}