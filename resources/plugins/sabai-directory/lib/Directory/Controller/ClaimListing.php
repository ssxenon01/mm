<?php
class Sabai_Addon_Directory_Controller_ClaimListing extends Sabai_Addon_Form_MultiStepController
{
    protected $_claimStatus = 'pending';
    
    protected function _getSteps(Sabai_Context $context)
    {
        return array('claim');
    }
    
    protected function _getFormForStepClaim(Sabai_Context $context, array &$formStorage)
    {
        $header = $this->getPlatform()->getOption($this->Entity_Addon($context->entity)->getName() . '_claim_form_header');
        if (!strlen($header)) {
            $header = __('If the listing is for your organisation, please complete the details below. Once we have confirmed your identity, we will give you full control over your listing and its contents.', 'sabai-directory');
        }
        $headers = array();
        $headers[] = '<div class="sabai-info">' . $header .'</div>';
        $form = array(
            '#disable_back_btn' => true,
            '#header' => $headers,
            'listing' => array(
                '#type' => 'item',
                '#title' => __('Listing', 'sabai-directory'),
                '#markup' => $this->Entity_Permalink($context->entity),
            ),
            'name' => array(
                '#type' => 'textfield',
                '#required' => true,
                '#title' => __('Contact name', 'sabai-directory'),
                '#default_value' => $this->getUser()->name,
                '#size' => 30,
            ),
            'email' => array(
                '#type' => 'textfield',
                '#email' => true,
                '#required' => true,
                '#title' => __('E-mail', 'sabai-directory'),
                '#default_value' => $this->getUser()->email,
                '#size' => 30,
            ),
            'comment' => array(
                '#required' => $this->Entity_Addon($context->entity)->getConfig('claims', 'no_comment') ? false : true,
                '#type' => 'markdown_textarea',
                '#title' => __('Comment', 'sabai-directory'),
                '#description' => __('Please provide additional information that will allow us to verify your claim.', 'sabai-directory'),
                '#rows' => 5,
                '#hide_buttons' => true,
                '#hide_preview' => true,
            ),
        );
        $tac_config = $this->Entity_Addon($context->entity)->getConfig('claims', 'tac');
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
                    '#markup' => '<textarea readonly="readonly" style="width:98%;">'. Sabai::h($this->getPlatform()->getOption($this->Entity_Addon($context->entity)->getName() . '_claim_tac')) . '</textarea>',
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
        $claim = $this->_createClaim($form, $context->entity);
        $form->storage['claim_id'] = $claim->id;
        $claim->reload();
        $this->doEvent('DirectoryListingClaimStatusChange', array($claim));
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
        $claim->type = 'claim';
        $claim->status = $this->_claimStatus;
        return $claim->commit();
    }
    
    protected function _complete(Sabai_Context $context, array $formStorage)
    {       
        $context->addTemplate('form_results')
            ->setAttributes(array(
                'success' => array(
                    __('Your claim has been submitted successfully. We will review your claim details and notify you if it is approved.', 'sabai-directory')
                )
            ));
    }
}
