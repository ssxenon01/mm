<?php
class Sabai_Addon_PaidDirectoryListings_Helper_SendOrderNotification extends Sabai_Helper
{
    public function help(Sabai $application, $name, Sabai_Addon_PaidListings_Model_Order $order)
    {
        if (!$listing = @$order->fetchObject('Entity')) {
            $order->with('Entity'); // load listing entity associated with the order
            $listing = $order->Entity;
        }
        $tags = array(
            '{order_id}' => $order->id,
            '{order_plan}' => $order->Plan->name,
            '{order_price}' => $order->price,
            '{order_currency}' => $order->currency,
            '{order_user_name}' => $order->User->name,
            '{order_user_email}' => $order->User->email,
            '{order_url}' => $application->MainUrl('/' . $application->getAddon('Directory')->getDashboardSlug() . '/orders', array('order_id' => $order->id), '', '&'),
            '{order_date}' => $application->Date($order->created),
            '{order_status}' => $order->getStatusLabel(),
            '{order_admin_url}' => $application->AdminUrl('/paiddirectorylistings', array('order_id' => $order->id), '', '&'), 
        );
        if ($order->isComplete()) {
            $tags += $application->Content_TemplateTags($listing, 'listing_');
        }
        foreach ((array)$name as $notification_name) {
            $application->Directory_SendNotification($application->Entity_Bundle($listing)->addon, 'order_' . $notification_name, $tags, $order->User);
        }
    }
}