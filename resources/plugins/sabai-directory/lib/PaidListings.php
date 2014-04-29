<?php
require_once dirname(__FILE__) . '/PaidListings/IFeatures.php';

class Sabai_Addon_PaidListings extends Sabai_Addon
{
    const VERSION = '1.2.30', PACKAGE = 'sabai-directory';
    const ORDER_STATUS_PENDING = 1, ORDER_STATUS_PROCESSING = 2, ORDER_STATUS_AWAITING_FULLFILLMENT = 3, ORDER_STATUS_COMPLETE = 4,
        ORDER_STATUS_REFUNDED = 5, ORDER_STATUS_EXPIRED = 6, ORDER_STATUS_FAILED = 7,
        ORDER_ITEM_STATUS_PENDING = 1, ORDER_ITEM_STATUS_DELIVERED = 2, ORDER_ITEM_STATUS_CANCELLED = 3;
    
    protected $_path;
        
    protected function _init()
    {
        $this->_path = $this->_application->Path(dirname(__FILE__) . '/PaidListings');
    }
    
    public function getPath()
    {
        return $this->_path;
    }
    
    public function onPaidListingsIFeaturesInstalled(Sabai_Addon $addon, ArrayObject $log)
    {
        if ($features = $addon->paidListingsGetFeatureNames()) {
            $this->_createFeatures($addon, $features);
        }
    }

    public function onPaidListingsIFeaturesUninstalled(Sabai_Addon $addon, ArrayObject $log)
    {
        $this->_deleteFeatures($addon);
    }

    public function onPaidListingsIFeaturesUpgraded(Sabai_Addon $addon, ArrayObject $log)
    {
        if (!$features = $addon->paidListingsGetFeatureNames()) {
            $this->_deleteFeatures($addon);
        } else {            
            $already_installed = $removed = array();
            foreach ($this->getModel('Feature')->addon_is($addon->getName())->fetch() as $current) {
                if (!in_array($current->name, $features)) {
                    $removed[] = $current->name;
                } else {
                    $already_installed[] = $current->name;
                }
            }
            if (!empty($removed)) {
                $this->_deleteFeatures($addon, $removed);
            }
            if ($new = array_diff($features, $already_installed)) {
                $this->_createFeatures($addon, $new);
            }
        }
    }

    private function _createFeatures(Sabai_Addon $addon, array $featureNames)
    {
        $parent_addon_name = $addon->hasParent();
        foreach ($featureNames as $feature_name) {
            if ($addon->getName() !== $this->_name) {
                if (!preg_match(sprintf('/^%s_[a-z]+[a-z0-9_]*[a-z0-9]+$/', strtolower($addon->getName())), $feature_name)) {
                    continue;
                }
                if ($parent_addon_name && stripos($feature_name, $parent_addon_name . '_') === 0) {
                    // should be handled when dealing with the parent addon
                    continue;
                }
            }
            $feature = $this->getModel()->create('Feature')->markNew();
            $feature->name = $feature_name;
            $feature->addon = $addon->getName();
            $feature->settings = array();
        }
        $this->getModel()->commit();
    }

    private function _deleteFeatures(Sabai_Addon $addon, array $featureNames = null)
    {
        if (!empty($featureNames)) {
            // Is this a child addon?
            if ($parent_addon_name = $addon->hasParent()) {
                foreach ($featureNames as $k => $feature_name) {
                    if (stripos($feature_name, $parent_addon_name . '_') === 0) {
                        // should be handled when dealing with the parent addon
                        unset($featureNames[$k]);
                    }
                }
                if (empty($featureNames)) {
                    return;
                }
            }
            $features = $this->getModel('Feature')->addon_is($addon->getName())->name_in($featureNames)->fetch();
        } else {
            $features = $this->getModel('Feature')->addon_is($addon->getName())->fetch();
        }
        $features->delete(true);
    }
    
    public function onPaidListingsOrderStatusChange($order, $isManual = false)
    {
        switch ($order->status) {
            case self::ORDER_STATUS_AWAITING_FULLFILLMENT:
                // Apply features that have not yet been applied
                $order->with('Entity');
                $this->_application->Entity_LoadFields($order->Entity);
                $order_items_updated = array();
                foreach ($order->OrderItems->with('Feature')->with('OrderItemMetas') as $order_item) {
                    if ($order_item->isComplete()) {
                        continue;
                    }
                    $ifeature = $this->_application->PaidListings_FeatureImpl($order_item->Feature->name); 
                    $order_item_data = $order_item->OrderItemMetas->getArray('value', 'key');
                    if ($ifeature->paidListingsFeatureIsAppliable($order->Entity, $order_item_data, $order->User, $isManual)) {
                        // Create log
                        $order_log = $order->createOrderLog('')->markNew();
                        $order_log->OrderItem = $order_item;
                        if ($ifeature->paidListingsFeatureApply($order->Entity, $order_item_data, $order->User)) {
                            $order_item->status = self::ORDER_ITEM_STATUS_DELIVERED;
                            $order_log->message = __('Item delivered.', 'sabai-directory');
                            $order_items_updated[] = $order_item;
                        } else {
                            $order_log->message = __('Item delivery failed.', 'sabai-directory');
                            $order_log->is_error = true;
                        }
                    }
                }
                $this->getModel()->commit();
                
                if (!empty($order_items_updated)) {
                    // Notify that the status of one or more order items have changed
                    $this->_application->doEvent('PaidListingsOrderItemsStatusChange', array($order_items_updated));
                }
                return;
            case self::ORDER_STATUS_REFUNDED:
            case self::ORDER_STATUS_EXPIRED:
            case self::ORDER_STATUS_FAILED:
                $order->with('Entity');
                $this->_application->Entity_LoadFields($order->Entity);
                // Unapply order item features that have already been applied otherwise cancel
                foreach ($order->OrderItems->with('Feature') as $order_item) {
                    if ($order_item->isCancelled()) {
                        continue;
                    }
                    // Create log
                    $order_log = $order->createOrderLog('')->markNew();
                    $order_log->OrderItem = $order_item;
                    if ($order_item->isDelivered()) {
                        $ifeature = $this->_application->PaidListings_FeatureImpl($order_item->Feature->name);
                        $order_item_data = $order_item->OrderItemMetas->getArray('value', 'key');
                        if ($ifeature->paidListingsFeatureUnapply($order->Entity, $order_item_data, $order->User)) {
                            $order_item->status = self::ORDER_ITEM_STATUS_CANCELLED;
                            $order_log->message = __('Item delivery cancelled.', 'sabai-directory');
                        } else {
                            $order_log->message = __('Item delivery cancel failed.', 'sabai-directory');
                            $order_log->is_error = true;
                        }
                    } else {
                        $order_item->status = self::ORDER_ITEM_STATUS_CANCELLED;
                        $order_log->message = __('Item cancelled.', 'sabai-directory');
                    }
                }
                $this->getModel()->commit();
                // Notify
                if ($order->status === self::ORDER_STATUS_REFUNDED) {
                    // Notify order has been refunded
                    $this->_application->doEvent('PaidListingsOrderRefunded', array($order));
                }
                return;
        }
    }
    
    public function onPaidListingsOrderItemsStatusChange($orderItems)
    {
        $order_ids = array();
        foreach ($orderItems as $order_item) {
            $order_ids[] = $order_item->order_id;
        }
        if (empty($order_ids)) return;

        // Create log for orders that have all features applied
        $orders_completed = array();
        foreach ($this->getModel('Order')->fetchByIds($order_ids)->with('OrderItems') as $order) {
            if ($order->isComplete()) {
                continue; // No more logs to create for order with complete status
            }
            foreach ($order->OrderItems as $order_item) {
                if (!$order_item->isComplete()) {
                    continue 2;
                }
            }
            $order->status = self::ORDER_STATUS_COMPLETE;
            // Create log
            $order_log = $order->createOrderLog('')->markNew();
            $order_log->message = __('Order complete.', 'sabai-directory');
            $order_log->status = $order->status;
                
            $orders_completed[] = $order;
        }
        
        $this->getModel()->commit();
        
        // Reload orders and notify
        foreach ($this->getModel('Order')->fetchByIds($order_ids)->with('OrderItems') as $order) {
            if ($order->isComplete()) {
                $this->_application->doEvent('PaidListingsOrderComplete', array($order));
            } elseif ($order->isAwaitingFullfillment()) {
                $this->_application->doEvent('PaidListingsOrderAwaitingFullfillment', array($order));
            }
        }
    }
        
    public function onEntityDeleteBundlesSuccess($entityType, $bundles)
    {
        if ($entityType !== 'content') return;
        
        $criteria = $this->getModel()->createCriteria('Order')->entityBundleName_in(array_keys($bundles));
        $this->getModel()->getGateway('Order')->deleteByCriteria($criteria);
    }
    
    public function onPayPalIpnReceived($ipn)
    {
        // Get the order with the notified transaction ID
        if (!$order = $this->getModel('Order')->transactionId_is($ipn['txn_id'])->fetchOne()) {
            $this->_application->LogError('Invalid transaction ID: ' . $ipn['txn_id']);
            // Order with the transaction ID does not exist
            return;
        }
        
        $previous_status = $order->status;
        
        switch ($ipn['payment_status']) {
            case 'Completed':
                $order->status = self::ORDER_STATUS_AWAITING_FULLFILLMENT;
                break;
            case 'Expired':
                $order->status = self::ORDER_STATUS_EXPIRED;
                break;
            case 'Failed':
                $order->status = self::ORDER_STATUS_FAILED;
                break;
            case 'Refunded':
                $order->status = self::ORDER_STATUS_REFUNDED;
                break;
        }

        // Create log
        $order_log = $order->createOrderLog('')->markNew();
        $order_log->message = sprintf(__('PayPal IPN received (Status: %s).', 'sabai-directory'), $ipn['payment_status']);
        $order_log->status = $order->status;
        
        $gateway_data = $order->gateway_data;
        $gateway_data[] = $ipn;
        $order->gateway_data = $gateway_data;

        $this->getModel()->commit();
        
        if ($previous_status !== $order->status) {
            $order->reload();
            $this->_application->doEvent('PaidListingsOrderStatusChange', array($order));
        }
    }
    
    public function onPaidListingsIPlanTypesUninstalled(Sabai_Addon $addon, ArrayObject $log)
    {
        $criteria = $this->getModel()->createCriteria('Plan')->type_in($addon->paidListingsGetPlanTypes());
        $this->getModel()->getGateway('Plan')->deleteByCriteria($criteria);
    }
}
