<?php
class Sabai_Addon_PaidListings_Helper_ConfirmOrderForm extends Sabai_Helper
{    
    /**
     * Creates an order listing form
     * @param Sabai $application
     * @param Sabai_Addon_Content_Entity $entity
     * @param int $planId
     * @param string $orderIdStorageKey
     */
    public function help(Sabai $application, Sabai_Addon_Content_Entity $entity, $planId, array $paypalConf, array $orderData = array(), $orderIdStorageKey = 'order_id')
    {
        if (!$plan = $application->getModel('Plan', 'PaidListings')->fetchById($planId)) {
            return false;
        }

        $markup = sprintf(
            '<table class="sabai-table sabai-paidlistings-order-summary">
    <thead>
        <tr>
            <th class="sabai-paidlistings-order-item-name">%s</th>
            <th class="sabai-paidlistings-order-item-price">%s</th>
            <th class="sabai-paidlistings-order-item-quantity">%s</th>
            <th class="sabai-paidlistings-order-item-amount">%s</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td class="sabai-paidlistings-order-item-name">%s</td>
            <td class="sabai-paidlistings-order-item-price">%s</td>
            <td class="sabai-paidlistings-order-item-quantity">%s</td>
            <td class="sabai-paidlistings-order-item-amount">%s</td>
        </tr>
    </tbody>
    <tfoot>
        <tr>
            <td colspan="3" class="sabai-paidlistings-order-total">%s</td>
            <td class="sabai-paidlistings-order-total-price">%s</td>
        </tr>
    </tfoot>
</table>',
            __('Plan Name', 'sabai-directory'),
            __('Unit Price', 'sabai-directory'),
            __('Quantity', 'sabai-directory'),
            __('Amount', 'sabai-directory'),
            Sabai::h($plan->name),
            $formatted_price = $application->PaidListings_MoneyFormat($plan->price, $paypalConf['currency']),
            1,
            $formatted_price,
            __('Total:', 'sabai-directory'),
            $formatted_price
        );
        
        $form = array(
            '#disable_back_btn' => true,
            'markup' => array(
                '#type' => 'markup',
                '#markup' => $markup,
            ),
            Sabai_Addon_Form::FORM_SUBMIT_BUTTON_NAME => array(
                array(
                    '#type' => 'submit',
                    '#value' => __('Confirm Payment', 'sabai-directory'),
                    '#submit' => array(
                        array(array($this, 'submitForm'), array($application, $entity, $plan, $paypalConf, $orderData, $orderIdStorageKey))
                    ),
                    '#btn_type' => 'primary',
                ),
            ),
        );
        
        if ($plan->price) {
            if (empty($_REQUEST['token'])) {
                return false;
            }
            $response = $application->PayPal_Request('GetExpressCheckoutDetails', array('TOKEN' => $_REQUEST['token']), $paypalConf);
            $form += array(
                'token' => array(
                    '#type' => 'hidden',
                    '#value' => $response['TOKEN'],
                ),
                'payerid' => array(
                    '#type' => 'hidden',
                    '#value' => $response['PAYERID'],
                ),
            );
        }
        
        return $form;
    }
    
    public function submitForm(Sabai_Addon_Form_Form $form, Sabai $application, Sabai_Addon_Content_Entity $entity, $plan, array $paypalConf, $orderData, $orderIdStorageKey)
    {
        $order = $application->getModel(null, 'PaidListings')->create('Order')->markNew();
        $order->User = $application->getUser();
        $order->status = Sabai_Addon_PaidListings::ORDER_STATUS_PENDING;
        $order->currency = $paypalConf['currency'];
        $order->price = $plan->price;
        $order->gateway_data = array();
        $order->entity_id = $entity->getId();
        $order->entity_bundle_name = $entity->getBundleName();
        $order->Plan = $plan;
        $order->plan_type = $plan->type;
        $order->createOrderLog(__('Order created.', 'sabai-directory'), Sabai_Addon_PaidListings::ORDER_STATUS_PENDING); 
        // Create items
        foreach ($plan->features as $feature_name => $feature_settings) {
            if (empty($feature_settings['enable'])) {
                continue;
            }
            unset($feature_settings['enable']);
            $order_item = $order->createOrderItem()->markNew();
            $order_item->feature_name = $feature_name;
            $order_item->status = Sabai_Addon_PaidListings::ORDER_ITEM_STATUS_PENDING;
            // Append custom order data for this feature if any set
            $order_item_data = isset($orderData[$feature_name]) ? $feature_settings + $orderData[$feature_name] : $feature_settings;
            foreach ($order_item_data as $meta_key => $meta_value) {
                $order_item_meta = $order_item->createOrderItemMeta()->markNew();
                $order_item_meta->key = $meta_key;
                $order_item_meta->value = $meta_value;
            }
        }
        $application->getModel(null, 'PaidListings')->commit();
        
        if ($plan->price) {
            $request = array(
                'TOKEN' => $form->values['token'],
                'PAYERID' => $form->values['payerid'],
                'PAYMENTREQUEST_0_AMT' => $order->price,
                'PAYMENTREQUEST_0_ITEMAMT' => $order->price,
                'PAYMENTREQUEST_0_CURRENCYCODE' => $paypalConf['currency'],
                'PAYMENTREQUEST_0_PAYMENTACTION' => 'Sale',
                'PAYMENTREQUEST_0_INVNUM' => $order->id,
                'PAYMENTREQUEST_0_SOFTDESCRIPTOR' => $application->getPlatform()->getSiteName(),
                'PAYMENTREQUEST_0_NOTIFYURL' => $application->PayPal_IpnUrl(),
                'L_PAYMENTREQUEST_0_NAME0' => $plan->name,
                'L_PAYMENTREQUEST_0_DESC0' => $plan->description,
                'L_PAYMENTREQUEST_0_AMT0' => $plan->price,
                'L_PAYMENTREQUEST_0_QTY0' => 1,
                'L_PAYMENTREQUEST_0_ITEMCATEGORY0' => 'Digital',
            );        
            $response = $application->PayPal_Request('DoExpressCheckoutPayment', $request, $paypalConf);
            $order->transaction_id = $response['PAYMENTINFO_0_TRANSACTIONID'];
            $gateway_data = array();
            $gateway_data['payment_type'] = $response['PAYMENTINFO_0_PAYMENTTYPE'];
            $gateway_data['payment_status'] = $response['PAYMENTINFO_0_PAYMENTSTATUS'];
            switch ($response['PAYMENTINFO_0_PAYMENTSTATUS']) {
                case 'Pending':
                    if (isset($response['PAYMENTINFO_0_PENDINGREASON'])) {
                        $gateway_data['pending_reason'] = $response['PAYMENTINFO_0_PENDINGREASON'];
                    }
                    break;               
                case 'Completed':
                    $order->status = Sabai_Addon_PaidListings::ORDER_STATUS_AWAITING_FULLFILLMENT;
                    $order->createOrderLog(__('Order confirmed and payment complete.', 'sabai-directory'), $order->status);
                    break;               
                default:
                    $order->status = Sabai_Addon_PaidListings::ORDER_STATUS_PROCESSING;
                    $order->createOrderLog(__('Order confirmed.', 'sabai-directory'), $order->status);
            }
            $order->gateway = 'paypal';
            $order->gateway_data = $gateway_data;
        } else {
            $order->status = Sabai_Addon_PaidListings::ORDER_STATUS_AWAITING_FULLFILLMENT;
            $order->createOrderLog(__('Order confirmed and payment complete.', 'sabai-directory'), $order->status);
        }
        $application->getModel(null, 'PaidListings')->commit();

        $form->storage[$orderIdStorageKey] = $order->id;
        
        $order->reload();
        $application->doEvent('PaidListingsOrderReceived', array($order));
        $application->doEvent('PaidListingsOrderStatusChange', array($order));
    }
}