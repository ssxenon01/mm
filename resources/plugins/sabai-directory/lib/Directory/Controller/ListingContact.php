<?php
class Sabai_Addon_Directory_Controller_ListingContact extends Sabai_Addon_Content_Controller_AddChildPost
{
    protected function _doGetFormSettings(Sabai_Context $context, array &$formStorage)
    {
        $form = parent::_doGetFormSettings($context, $formStorage);
        $this->_cancelUrl = null;
        $this->_submitButtons['submit'] = array(
            '#value' => __('Submit', 'sabai-directory'),
            '#btn_type' => 'primary',
        );
        return $form;
    }

    public function submitForm(Sabai_Addon_Form_Form $form, Sabai_Context $context)
    {
        $entity = parent::submitForm($form, $context);
        if ($entity->isPublished()) {
            // redirect to the listing page.
            $context->setSuccess($this->Entity_Url($context->entity))
                ->addFlash(__('Your message has been submitted successfully.', 'sabai-directory'));
        }
        
        return $entity;
    }
}