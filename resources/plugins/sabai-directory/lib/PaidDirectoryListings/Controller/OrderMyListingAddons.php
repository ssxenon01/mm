<?php
class Sabai_Addon_PaidDirectoryListings_Controller_OrderMyListingAddons extends Sabai_Addon_Form_MultiStepController
{    
    protected function _getSteps(Sabai_Context $context)
    {
        return array('order', 'confirm_order');
    }
    
    protected function _getFormForStepOrder(Sabai_Context $context, array &$formStorage)
    {
        $this->_submitButtons = false;
        try {
            return $this->PaidListings_OrderForm($context->entity, 'directory_listing_addon', $context->getRoute(), $this->getAddon()->getConfig('paypal'), __('Submit', 'sabai-directory'));
        } catch (Sabai_RuntimeException $e) {
            $context->setError($e->getMessage());
            return false;
        }
    }
        
    protected function _getFormForStepConfirmOrder(Sabai_Context $context, array &$formStorage)
    {
        $this->_submitButtons = false;
        return $this->PaidListings_ConfirmOrderForm($context->entity, $formStorage['values']['order']['plan'], $this->getAddon()->getConfig('paypal'));
    }
    
    protected function _complete(Sabai_Context $context, array $formStorage)
    {
        $order = $this->getModel('Order', 'PaidListings')->fetchById($formStorage['order_id']);
        if (!$order) {
            return; // this should not happen
        }

        $messages = array();        
        switch ($order->status) {
            case Sabai_Addon_PaidListings::ORDER_STATUS_COMPLETE:
                $messages['success'] = __('Your order has been processed successfully and is complete.', 'sabai-directory');
                break;
            case Sabai_Addon_PaidListings::ORDER_STATUS_AWAITING_FULLFILLMENT:
            case Sabai_Addon_PaidListings::ORDER_STATUS_PROCESSING:
                $messages['info'] = __('Your order is currently being processed. We will notify you once it is complete.', 'sabai-directory'); 
                break;
            case Sabai_Addon_PaidListings::ORDER_STATUS_PENDING:
                $messages['info'] = __('Your order is currently awaiting payment. We will process your order once we receive your payment.', 'sabai-directory');      
                break;
            default:
                $messages['error'] = __('An error occurred while processing your order. Please contact the administrator for details.', 'sabai-directory');
                break;
        }
        
        $context->addTemplate('form_results')->setAttributes($messages);
    }
}