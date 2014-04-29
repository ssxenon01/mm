<?php
class Sabai_Addon_Directory_Controller_AddListing extends Sabai_Addon_Form_MultiStepController
{
    protected $_claimStatus = 'pending';    
    
    protected function _doGetFormSettings(Sabai_Context $context, array &$formStorage)
    {
        $context->clearTabs();
        $settings = parent::_doGetFormSettings($context, $formStorage);
        if (!isset($this->_ajaxSubmit)) {
            $this->_ajaxSubmit = true;
        }
        $this->_ajaxCancelType = 'none';
        return $settings;
    }
    
    protected function _getSteps(Sabai_Context $context)
    {
        if ($this->getUser()->isAnonymous()) {
            return array('select_directory', 'add');
        }
        return array('select_directory', 'add', 'claim');
    }
    
    protected function _getSubmittableDiretctories(Sabai_Context $context)
    {
        // Fetch directory options
        $options = $this->Directory_DirectoryList();
        
        foreach (array_keys($options) as $directory_listing_bundle) {
            if (!$this->HasPermission($directory_listing_bundle . '_add')) {
                unset($options[$directory_listing_bundle]);
            }
        }
        
        return $options;
    }
    
    protected function _getFormForStepSelectDirectory(Sabai_Context $context, array &$formStorage)
    {
        // Fetch submittable directory options
        $options = $this->_getSubmittableDiretctories($context);
        if (empty($options)) {
            // The user is not allowed to submit listings to any directory
            if ($this->getUser()->isAnonymous()) {
                $context->setUnauthorizedError();
            }
            return false;
        }
        if (!$context->getRequest()->isPostMethod()) {
            // Skip selection step if only 1 directory to which the user can submit listings
            if (count($options) === 1) {
                $bundle = current(array_keys($options));
            } elseif (isset($context->bundle)) {
                $bundle = $context->bundle->name;
            } elseif (isset($context->addon)) {
                try {
                    $addon = $this->getAddon($context->addon);
                    if ($addon instanceof Sabai_Addon_Directory) {
                        $bundle = $addon->getListingBundleName();
                        if (!isset($options[$bundle])) {
                            unset($bundle);
                        }
                    }
                } catch (Sabai_IException $e) {
                    $this->LogError($e);
                }
            } else { // or directory is specified in the request parameter
                $bundle = $context->getRequest()->asStr('bundle', null, array_keys($options));
            }
            // Skip this step if directory has been selected
            if (isset($bundle)) {
                $formStorage['values']['select_directory']['bundle'] = $bundle;
                $next_step = $this->_skipStep($formStorage);
                return $this->_getForm($next_step, $context, $formStorage);
            }
        }
        // Create directory selection form       
        return array(
            'bundle' => array(
                '#title' => __('Directory', 'sabai-directory'),
                '#description' => __('Select the directory where you want to submit the new listing.', 'sabai-directory'),
                '#type' => 'radios',
                '#options' => $options,
                '#required' => true,
                '#default_value' => $this->getAddon('Directory')->getListingBundleName(),
            ),
        );
    }
        
    protected function _getFormForStepAdd(Sabai_Context $context, array &$formStorage)
    {
        // Pass form values if form has been submitted. Usually, this is not needed to initialize form settings
        // but the entity form needs to check values to see if any form fields have been added dynamically (via JS) by the user.
        $values = null;
        if ($context->getRequest()->isPostMethod()
            && $context->getRequest()->has(Sabai_Addon_Form::FORM_BUILD_ID_NAME)
        ) {
            $values = $context->getRequest()->getParams();
        }

        $this->_submitButtons[] = array(
            '#value' => __('Submit Listing', 'sabai-directory'),
        );
        
        $bundle = $this->Entity_Bundle($formStorage['values']['select_directory']['bundle']);
        $form = $this->Entity_Form($bundle->name, $values);
        unset($form['directory_claim']);
        // Add photo upload field if the user has a valid permission
        if ($this->HasPermission($this->getAddon($bundle->addon)->getPhotoBundleName() . '_add')) {
            $photo_config = $this->getAddon($bundle->addon)->getConfig('photo');
            if ($photo_config['max_num'] > 0) {
                $form['photos'] = array(
                    '#type' => 'file_upload',
                    '#title' => __('Photos', 'sabai-directory'),
                    '#description' => sprintf(
                        __('Maximum number of files %d, maximum file size %s.', 'sabai-directory'),
                        $photo_config['max_num'],
                        $photo_config['max_file_size'] >= 1024 ? round($photo_config['max_file_size'] / 1024, 1) . 'MB' : $photo_config['max_file_size'] . 'KB'
                    ),
                    '#max_file_size' => $photo_config['max_file_size'],
                    '#multiple' => true,
                    '#allow_only_images' => true,
                    '#default_value' => null,
                    '#max_num_files' => $photo_config['max_num'],
                    '#weight' => 99,
                );
            }
        }
        
        // Do not add the back button if inside a specific directory
        if (isset($context->bundle)) {
            $form['#disable_back_btn'] = true;
        }
        
        return $form;
    }

    protected function _submitFormForStepAdd(Sabai_Context $context, Sabai_Addon_Form_Form $form)
    {
        if (empty($form->storage['listing_id'])) {
            $bundle_name = $form->storage['values']['select_directory']['bundle'];
            $status = $this->HasPermission($bundle_name . '_add2') // can post without approval?
                ? Sabai_Addon_Content::POST_STATUS_PUBLISHED
                : Sabai_Addon_Content::POST_STATUS_PENDING;
            // Create listing and save entity id into session for later use
            $listing = $this->getAddon('Entity')->createEntity($bundle_name, array('content_post_status' => $status) + $form->values);
            // Add new photos if any
            if (!empty($form->values['photos'])) {
                $bundle = $this->Entity_Bundle($form->storage['values']['select_directory']['bundle']);
                $display_order = 0;
                foreach ($form->values['photos'] as $file) {
                    $this->_application->getAddon('Entity')->createEntity(
                        $this->getAddon($bundle->addon)->getPhotoBundleName(),
                        array(
                            'content_post_title' => $file['title'],
                            'content_post_status' => $listing->getStatus(),
                            'file_image' => $file,
                            'content_parent' => $listing->getId(),
                            'directory_photo' => array(
                                'official' => 2, // partially official
                                'display_order' => ++$display_order
                            ),
                        ),
                        array('content_skip_update_parent' => true) // we'll update parent listing later
                    );
                }
                // Update parent listing
                $this->getAddon('Content')->updateParentPost($listing, false, true, true);
            }
            $form->storage['listing_id'] = $listing->getId();
        }
    }

    protected function _getSubmitSuccessMessage(Sabai_Context $context, Sabai_Addon_Entity_Entity $entity)
    {
        if ($entity->isPublished()) {
            return '<i class="sabai-icon-ok-sign"></i> ' . sprintf(
                __('Your listing has been submitted successfully and published. You can view the listing <a href="%s" target="_blank">here</a>.', 'sabai-directory'),
                $this->Entity_Url($entity)
            );
        }
        return '<i class="sabai-icon-ok-sign"></i> ' . __('Your listing has been submitted successfully. We will review your submission and post it on this site if it is approved.', 'sabai-directory');
    }

    protected function _getFormForStepClaim(Sabai_Context $context, array &$formStorage)
    {
        $entity = $this->Entity_Entity('content', $formStorage['listing_id'], false);
        $headers = array('<div class="sabai-success">' . $this->_getSubmitSuccessMessage($context, $entity) . '</div>');
        $headers[] = sprintf(
            '<div class="sabai-info">%s</div>',
            $this->getPlatform()->getOption(
                $this->Entity_Addon($entity)->getName() . '_claim_form_header',
                __('If the listing is for your organisation, please complete the details below. Once we have confirmed your identity, we will give you full control over your listing and its contents.', 'sabai-directory')
            )
        );
        $form = array(
            '#disable_back_btn' => true,
            '#header' => $headers,
            'name' => array(
                '#type' => 'textfield',
                '#required' => true,
                '#title' => __('Contact name', 'sabai-directory'),
                '#default_value' => $this->getUser()->name,
            ),
            'email' => array(
                '#type' => 'textfield',
                '#email' => true,
                '#required' => true,
                '#title' => __('E-mail', 'sabai-directory'),
                '#default_value' => $this->getUser()->email,
            ),
            'comment' => array(
                '#required' => $this->Entity_Addon($entity)->getConfig('claims', 'no_comment') ? false : true,
                '#type' => 'markdown_textarea',
                '#title' => __('Comment', 'sabai-directory'),
                '#description' => __('Please provide additional information that will allow us to verify your claim.', 'sabai-directory'),
                '#rows' => 3,
                '#hide_buttons' => true,
                '#hide_preview' => true,
            ),
        );
        $tac_config = $this->Entity_Addon($entity)->getConfig('claims', 'tac');
        if (isset($tac_config['type']) && $tac_config['type'] !== 'none') {
            $form['tac'] = array(
                '#collapsible' => false,
                '#class' => 'sabai-form-group',
                '#weight' => 9999,
            );
            $form['tac']['agree_tac'] = array(
                '#type' => 'checkbox',
                '#required' => !empty($tac_config['required']),
                '#tree' => false,
                '#weight' => 2,
            );
            if ($tac_config['type'] === 'inline') {
                $form['tac']['agree_tac']['#title'] = __('I agree to the above terms and conditions', 'sabai-directory');
                $form['tac']['inline'] = array(
                    '#title' => __('Terms and Conditions', 'sabai-directory'),
                    '#type' => 'item',
                    '#weight' => 1,
                    '#markup' => '<textarea readonly="readonly" style="width:98%;">'. Sabai::h($this->getPlatform()->getOption($this->Entity_Addon($entity)->getName() . '_claim_tac')) . '</textarea>',
                );
            } else {
                $form['tac']['agree_tac']['#title'] = sprintf(__('I agree to the <a href="%s" target="_blank">terms and conditions</a>', 'sabai-directory'), rtrim($this->getScriptUrl('main'), '/') . '/' . $tac_config['link']);
                $form['tac']['agree_tac']['#title_no_escape'] = true;
            }
        }
        $this->_submitButtons[] = array(
            '#value' => __('Submit Claim', 'sabai-directory'),
        );
        
        return $form;
    }

    protected function _submitFormForStepClaim(Sabai_Context $context, Sabai_Addon_Form_Form $form)
    {
        if (!$entity = $this->Entity_Entity('content', $form->storage['listing_id'], false)) {
            return false;
        }
        $claim = $this->_createClaim($form, $entity);
        $form->storage['claim_id'] = $claim->id;
    }
    
    protected function _createClaim(Sabai_Addon_Form_Form $form, Sabai_Addon_Content_Entity $entity)
    {
        $claim = $this->getModel(null, 'Directory')->create('Claim')->markNew();
        $claim->name = $form->values['name'];
        $claim->email = $form->values['email'];
        $claim->comment = $form->values['comment']['text'];
        $claim->comment_html = $form->values['comment']['filtered_text'];
        $claim->entity_id = $entity->getId();
        $claim->entity_bundle_name = $entity->getBundleName();
        $claim->User = $this->getUser();
        $claim->type = 'new';
        $claim->status = $this->_claimStatus;
        return $claim->commit();
    }
    
    protected function _complete(Sabai_Context $context, array $formStorage)
    {
        $entity = $this->Entity_Entity('content', $formStorage['listing_id'], false);
        if (!$entity) {
            return; // this should never happen
        }
        // Set cookie to track guest user
        if ($this->getUser()->isAnonymous()) {
            $this->Content_SetGuestAuthorCookie($entity);
        }
        // Display message
        $messages = array();
        if (@$formStorage['claim_id']) {
            $claim = $this->getModel('Claim', 'Directory')->fetchById($formStorage['claim_id']);
            if (!$claim) {
                return; // this should never happen
            }
            $messages['success'] = __('Your claim has been submitted successfully. We will review your claim details and notify you if it is approved.', 'sabai-directory');
            if ($entity->isPublished()) {
                $messages['info'] = sprintf(
                    __('Click <a href="%s">here</a> to view your listing.', 'sabai-directory'),
                    $this->Entity_Url($entity)
                );
            }
        } else {
            $messages['success'] = $this->_getSubmitSuccessMessage($context, $entity);
        }
        $context->addTemplate('form_results')->setAttributes($messages);
    }
}
