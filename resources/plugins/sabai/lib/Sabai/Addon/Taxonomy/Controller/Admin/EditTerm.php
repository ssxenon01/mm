<?php
class Sabai_Addon_Taxonomy_Controller_Admin_EditTerm extends Sabai_Addon_Form_Controller
{
    protected function _doGetFormSettings(Sabai_Context $context, array &$formStorage)
    {        
        $this->_cancelUrl = $context->taxonomy_bundle->getPath();
        $this->_submitButtons[] = array('#value' => __('Save Changes', 'sabai'), '#btn_type' => 'primary');
        
        // Pass form values if form has been submitted. Usually, this is not needed to initialize form settings,
        // but the entity form needs to check values to see if any form fields have been added by the user.
        $values = null;
        if ($context->getRequest()->isPostMethod()
            && $context->getRequest()->has(Sabai_Addon_Form::FORM_BUILD_ID_NAME)
        ) {
            $values = $context->getRequest()->getParams();
        }

        $context->clearTabs();

        return $this->Entity_Form($context->entity, $values, true);
    }

    public function submitForm(Sabai_Addon_Form_Form $form, Sabai_Context $context)
    {
        $entity = $this->getAddon('Entity')->updateEntity($context->entity, $form->values);
        $context->setSuccess($context->taxonomy_bundle->getPath() . '/' . $entity->getId());
    }
}