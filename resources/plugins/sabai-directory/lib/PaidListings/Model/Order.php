<?php
class Sabai_Addon_PaidListings_Model_Order extends Sabai_Addon_PaidListings_Model_Base_Order
{
    public function getLabel()
    {
        return '#' . str_pad($this->id, 5, 0, STR_PAD_LEFT);
    }
    
    public function isComplete()
    {
        return $this->status === Sabai_Addon_PaidListings::ORDER_STATUS_COMPLETE;
    }
    
    public function isAwaitingFullfillment() 
    {
        return $this->status === Sabai_Addon_PaidListings::ORDER_STATUS_AWAITING_FULLFILLMENT;
    }
    
    public function getStatusLabel($status = null)
    {
        if (!isset($status)) {
            $status = $this->status;
        }
        switch ($status) {
            case Sabai_Addon_PaidListings::ORDER_STATUS_PENDING:
                return __('Pending Payment', 'sabai-directory');
            case Sabai_Addon_PaidListings::ORDER_STATUS_PROCESSING:
                return __('Processing', 'sabai-directory');
            case Sabai_Addon_PaidListings::ORDER_STATUS_AWAITING_FULLFILLMENT:
                return __('Awaiting Fullfillment', 'sabai-directory');
            case Sabai_Addon_PaidListings::ORDER_STATUS_COMPLETE:
                return __('Complete', 'sabai-directory');
            case Sabai_Addon_PaidListings::ORDER_STATUS_EXPIRED:
                return __('Expired', 'sabai-directory');
            case Sabai_Addon_PaidListings::ORDER_STATUS_FAILED:
                return __('Failed', 'sabai-directory');
            case Sabai_Addon_PaidListings::ORDER_STATUS_REFUNDED:
                return __('Refunded', 'sabai-directory');
            default:
                return '';
        }
    }
    
    public function getStatusLabelClass($status = null)
    {
        if (!isset($status)) {
            $status = $this->status;
        }
        switch ($status) {
            case Sabai_Addon_PaidListings::ORDER_STATUS_PENDING:
                return'sabai-label-warning';
            case Sabai_Addon_PaidListings::ORDER_STATUS_PROCESSING:
            case Sabai_Addon_PaidListings::ORDER_STATUS_AWAITING_FULLFILLMENT:
                return 'sabai-label-info';                    
            case Sabai_Addon_PaidListings::ORDER_STATUS_COMPLETE:
            case Sabai_Addon_PaidListings::ORDER_STATUS_REFUNDED:
                return 'sabai-label-success';
            case Sabai_Addon_PaidListings::ORDER_STATUS_EXPIRED:
            case Sabai_Addon_PaidListings::ORDER_STATUS_FAILED:
                return 'sabai-label-important';
            default:
                return '';
        }
    }
        
    public function createOrderLog($message, $status = 0, $isError = false)
    {
        $order_log = parent::createOrderLog()->markNew();
        $order_log->order_id = $this->id;
        $order_log->message = $message;
        $order_log->status = $status;
        $order_log->is_error = $isError;
        return $order_log;
    }
    
    public function getGatewayData($key, $default = null)
    {
        if (isset($this->gateway_data[$key])) return $this->gateway_data[$key];
        return isset($this->gateway_data[0][$key]) ? $this->gateway_data[0][$key] : $default; // for compat with < 1.2.8
    }
}

class Sabai_Addon_PaidListings_Model_OrderRepository extends Sabai_Addon_PaidListings_Model_Base_OrderRepository
{
}