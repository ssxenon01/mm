<?php
require_once dirname(dirname(dirname(dirname(__FILE__)))) . '/Directory/Controller/Admin/ViewListingClaim.php';

class Sabai_Addon_PaidDirectoryListings_Controller_Admin_ViewListingClaim extends Sabai_Addon_Directory_Controller_Admin_ViewListingClaim
{    
    public function approveClaim(Sabai_Addon_Form_Form $form, Sabai_Context $context)
    {
        if (!$context->claim->Entity->isPublished()) return;

        // Allow approving this claim only if the listing does not already have a valid claim
        if ($this->Directory_ListingOwner($context->claim->Entity)) return;

        // Deliver order item associated with the claim
        $order_item = $this->getModel('OrderItem', 'PaidListings')
            ->getByMeta('claim_id', $context->claim->id)
            ->with('Order')
            ->with('OrderItemMetas')
            ->getFirst();
        if ($order_item && !$order_item->isComplete()) {
            $order_item_data = $order_item->OrderItemMetas->getArray('value', 'key');
            $this->Directory_ClaimListing($context->claim->Entity, $context->claim->User, $order_item_data['duration']);
            $order_item->status = Sabai_Addon_PaidListings::ORDER_ITEM_STATUS_DELIVERED;
            $order_item->createOrderLog(__('Item delivered.', 'sabai-directory'));   
            $this->getModel(null, 'PaidListings')->commit();
            // Notify that the status of an order item has changed
            $this->doEvent('PaidListingsOrderItemsStatusChange', array(array($order_item)));
        }
        $this->_updateClaim($context->claim, 'approved', $form->values['admin_note']);
        $context->setSuccess($context->bundle->getPath() . '/claims');
    }
    
    public function rejectClaim(Sabai_Addon_Form_Form $form, Sabai_Context $context)
    {
        if (!$context->claim->Entity->isPublished()) return;
        
        // Cancel order item associated with the claim
        $order_item = $this->getModel('OrderItem', 'PaidListings')
            ->getByMeta('claim_id', $context->claim->id)
            ->with('Order')
            ->getFirst();
        if ($order_item && !$order_item->isComplete()) {
            $order_item->status = Sabai_Addon_PaidListings::ORDER_ITEM_STATUS_CANCELLED;
            $order_item->createOrderLog(__('Claim rejected.', 'sabai-directory'), true);      
            $this->getModel(null, 'PaidListings')->commit();
            // Notify that the status of an order item has changed
            $this->doEvent('PaidListingsOrderItemsStatusChange', array(array($order_item)));
        }
        $this->_updateClaim($context->claim, 'rejected', $form->values['admin_note']);
        $context->setSuccess($context->bundle->getPath() . '/claims');
    }
}