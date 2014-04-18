<?php
class Sabai_Addon_PaidListings_Helper_OrderStatusLabels extends Sabai_Helper
{    
    /**
     * @param Sabai $application
     */
    public function help(Sabai $application)
    {
        return array(
            Sabai_Addon_PaidListings::ORDER_STATUS_COMPLETE => __('Complete', 'sabai-directory'),
            Sabai_Addon_PaidListings::ORDER_STATUS_AWAITING_FULLFILLMENT => __('Awaiting Fullfillment', 'sabai-directory'),
            Sabai_Addon_PaidListings::ORDER_STATUS_PROCESSING => __('Processing', 'sabai-directory'),
            Sabai_Addon_PaidListings::ORDER_STATUS_PENDING => __('Pending Payment', 'sabai-directory'),
            Sabai_Addon_PaidListings::ORDER_STATUS_EXPIRED => __('Expired', 'sabai-directory'),
            Sabai_Addon_PaidListings::ORDER_STATUS_FAILED => __('Failed', 'sabai-directory'),
            Sabai_Addon_PaidListings::ORDER_STATUS_REFUNDED => __('Refunded', 'sabai-directory'),
        );
    }
}