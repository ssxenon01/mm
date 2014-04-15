<?php
class Sabai_Addon_PaidListings_Model_OrderItem extends Sabai_Addon_PaidListings_Model_Base_OrderItem
{
    public function getLabel()
    {
        return '#' . str_pad($this->order_id, 5, 0, STR_PAD_LEFT) . '-' . str_pad($this->id, 5, 0, STR_PAD_LEFT);
    }
    
    public function isComplete()
    {
        return in_array($this->status, array(Sabai_Addon_PaidListings::ORDER_ITEM_STATUS_DELIVERED, Sabai_Addon_PaidListings::ORDER_ITEM_STATUS_CANCELLED));
    }
    
    public function isDelivered()
    {
        return $this->status === Sabai_Addon_PaidListings::ORDER_ITEM_STATUS_DELIVERED;
    }
        
    public function isCancelled()
    {
        return $this->status === Sabai_Addon_PaidListings::ORDER_ITEM_STATUS_CANCELLED;
    }
    
    public function createOrderLog($message, $isError = false)
    {
        $order_log = parent::createOrderLog()->markNew();
        $order_log->order_id = $this->order_id;
        $order_log->message = $message;
        $order_log->is_error = $isError;
        return $order_log;
    }
}

class Sabai_Addon_PaidListings_Model_OrderItemRepository extends Sabai_Addon_PaidListings_Model_Base_OrderItemRepository
{
    public function getByMeta($key, $value)
    {
        return $this->_getCollection($this->_model->getGateway($this->getName())->getByMeta($key, $value));
    }
}