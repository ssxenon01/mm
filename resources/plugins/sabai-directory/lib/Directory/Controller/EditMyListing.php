<?php
class Sabai_Addon_Directory_Controller_EditMyListing extends Sabai_Addon_Form_Controller
{    
    protected function _doGetFormSettings(Sabai_Context $context, array &$formStorage)
    {        
        $this->_submitButtons['submit'] = array(
            '#value' => __('Save Changes', 'sabai-directory'),
            '#btn_type' => 'primary',
        );

        // Pass form values if form has been submitted. Usually, this is not needed to initialize form settings
        // but the entity form needs to check values to see if any form fields have been added dynamically (via JS) by the user.
        $values = null;
        if ($context->getRequest()->isPostMethod()
            && $context->getRequest()->has(Sabai_Addon_Form::FORM_BUILD_ID_NAME)
        ) {
            $values = $context->getRequest()->getParams();
        }

        $form = $this->Entity_Form($context->entity, $values);
        unset($form['directory_claim']);
        
        return $form;
    }

    public function submitForm(Sabai_Addon_Form_Form $form, Sabai_Context $context)
    {        
        $this->getAddon('Entity')->updateEntity($context->entity, $form->values);
        $context->setSuccess()->addFlash(__('The listing has been updated successfully.', 'sabai-directory'));
    }
}
