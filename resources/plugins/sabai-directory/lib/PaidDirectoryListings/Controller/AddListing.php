<?php
require_once dirname(dirname(dirname(__FILE__))) . '/Directory/Controller/AddListing.php';

class Sabai_Addon_PaidDirectoryListings_Controller_AddListing extends Sabai_Addon_Directory_Controller_AddListing
{
    protected $_claimStatus = 'pending_payment';
    
    protected function _getSteps(Sabai_Context $context)
    {
        $steps = parent::_getSteps($context);
        return in_array('claim', $steps) ? array_merge($steps, array('confirm_order')) : $steps;
    }
    
    protected function _getFormForStepClaim(Sabai_Context $context, array &$formStorage)
    {
        $listing = $this->Entity_Entity('content', $formStorage['listing_id'], false);
        $bundle = $this->Entity_Bundle($listing);
        $route = '/' . $this->getAddon($bundle->addon)->getDirectorySlug() . '/add';
        try {
            $form = $this->PaidListings_OrderForm($listing, 'directory_listing', $route, $this->getAddon()->getConfig('paypal'), __('Claim Listing', 'sabai-directory'));
        } catch (Sabai_RuntimeException $e) {
            $context->setError($e->getMessage());
            return false;
        }
        $form += parent::_getFormForStepClaim($context, $formStorage);
        $form['#action'] = $this->Url($route);
        $this->_submitButtons = false;
        $this->_ajaxSubmit = false;
        
        return $form;
    }
        
    protected function _getFormForStepConfirmOrder(Sabai_Context $context, array &$formStorage)
    {
        $claim = $this->getModel('Claim', 'Directory')->fetchById($formStorage['claim_id']);
        if (!$claim) {
            return false; // this should never happen
        }
        $this->_submitButtons = false;
        $this->_ajaxSubmit = false;
        $entity = $this->Entity_Entity('content', $formStorage['listing_id'], false);
        return $this->PaidListings_ConfirmOrderForm(
            $entity,
            $formStorage['values']['claim']['plan'], // plan ID
            $this->getAddon()->getConfig('paypal'),
            array('paiddirectorylistings_claim' => array('claim_id' => $claim->id)) // custom order data
        );
    }
}