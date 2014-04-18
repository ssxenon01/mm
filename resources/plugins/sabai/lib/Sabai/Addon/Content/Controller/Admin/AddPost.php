<?php
class Sabai_Addon_Content_Controller_Admin_AddPost extends Sabai_Addon_Form_Controller
{
    protected function _doGetFormSettings(Sabai_Context $context, array &$formStorage)
    {
        $bundle = $this->_getBundle($context);
        $this->_cancelUrl = $bundle->getPath();
        $this->_submitButtons[] = array('#value' => sprintf(__('Add %s', 'sabai'), $this->Entity_BundleLabel($bundle, true)));
        
        // Pass form values if form has been submitted. Usually, this is not needed to initialize form settings
        // but the entity form needs to check values to see if any form fields have been added dynamically (via JS) by the user.
        $values = null;
        if ($context->getRequest()->isPostMethod()
            && $context->getRequest()->has(Sabai_Addon_Form::FORM_BUILD_ID_NAME)
        ) {
            $values = $context->getRequest()->getParams();
        }
        
        $context->clearTabs();

        $form = $this->Entity_Form($bundle, $values, true);
        $form['content_post_status'] = array(
            '#type' => 'select',
            '#title' => __('Status', 'sabai'),
            '#options' => array(
                Sabai_Addon_Content::POST_STATUS_PUBLISHED => __('Published', 'sabai'),
                Sabai_Addon_Content::POST_STATUS_DRAFT => __('Draft', 'sabai'),
            ),
            '#default_value' => 'published',
        );
        $form['content_post_published'] = array(
            '#type' => 'date_datepicker',
            '#title' => __('Published on', 'sabai'),
            '#current_date_selected' => true,
            '#max_date' => time(),
        );
        $form['content_post_user_id'] = array(
            '#type' => 'autocomplete_user',
            '#title' => __('Author', 'sabai'),
            '#default_value' => $this->getUser()->id,
        );
        if (!empty($bundle->info['content_featurable'])) {
            $form['content_featured'] = array(
                '#title' => __('Featured', 'sabai'),
                '#tree' => true,
                'value' => array(
                    '#type' => 'checkbox',
                    '#title' => sprintf(__('This is a featured %s', 'sabai'), strtolower($this->Entity_BundleLabel($bundle, true))),
                ),
                'expires_at' => array(
                    '#type' => 'date_datepicker',
                    '#field_prefix' => sprintf('<strong><i class="sabai-icon-calendar"></i> %s</strong>', __('Expires on:', 'sabai')),
                    '#min_date' => time(),
                    '#states' => array(
                        'visible' => array('input[name="content_featured[value][]"]' => array('type' => 'checked', 'value' => true)),
                    ),
                    '#empty_value' => 0,
                ),
            );
        }
        
        return $form;
    }

    public function submitForm(Sabai_Addon_Form_Form $form, Sabai_Context $context)
    {
        $bundle = $this->_getBundle($context);
        $entity = $this->getAddon('Entity')->createEntity($bundle->name, $form->values);
        $context->setSuccess($bundle->getPath() . '/' . $entity->getId())
            ->addFlash(sprintf(__('%s posted successfully.', 'sabai'), $this->Entity_BundleLabel($bundle, true)));
        return $entity;
    }
    
    /**
     * @return Sabai_Addon_Model_Bundle 
     */
    protected function _getBundle(Sabai_Context $context)
    {
        return $context->bundle;
    }
}