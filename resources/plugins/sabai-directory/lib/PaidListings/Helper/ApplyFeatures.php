<?php
class Sabai_Addon_PaidListings_Helper_ApplyFeatures extends Sabai_Helper
{    
    /**
     * @param Sabai $application
     * @param Sabai_Addon_Content_Entity $entity Content to which ordered features should be applied
     */
    public function help(Sabai $application, Sabai_Addon_Content_Entity $entity, $isManual = false)
    {
        $orders = $application->getModel('Order', 'PaidListings')
            ->entityId_is($entity->getId())
            ->status_in(array(Sabai_Addon_PaidListings::ORDER_STATUS_COMPLETE, Sabai_Addon_PaidListings::ORDER_STATUS_AWAITING_FULLFILLMENT))
            ->fetch()
            ->with('User')
            ->with('OrderItems', array('Feature', 'OrderItemMetas'));
        $order_items_updated = array();
        foreach ($orders as $order) {
            foreach ($order->OrderItems as $order_item) {
                if ($order_item->isComplete()) {
                    continue;
                }
                $ifeature = $application->PaidListings_FeatureImpl($order_item->Feature->name); 
                $order_item_data = $order_item->OrderItemMetas->getArray('value', 'key');
                if ($ifeature->paidListingsFeatureIsAppliable($entity, $order_item_data, $order->User, $isManual)) {
                    if ($ifeature->paidListingsFeatureApply($entity, $order_item_data, $order->User)) {
                        $order_item->status = Sabai_Addon_PaidListings::ORDER_ITEM_STATUS_DELIVERED;
                        $order_item->createOrderLog(__('Item delivered.', 'sabai-directory'));
                        $order_items_updated[] = $order_item;
                    } else {
                        $order_item->createOrderLog(__('Item delivery failed.', 'sabai-directory'), true);
                    }
                }
            }
        }
        $application->getModel(null, 'PaidListings')->commit();
        
        if (!empty($order_items_updated)) {
            // Notify that the status of one or more order items have changed
            $application->doEvent('PaidListingsOrderItemsStatusChange', array($order_items_updated));
        }
    }
}