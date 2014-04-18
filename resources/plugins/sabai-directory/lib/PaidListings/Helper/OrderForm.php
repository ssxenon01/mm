<?php
class Sabai_Addon_PaidListings_Helper_OrderForm extends Sabai_Helper
{
    /**
     * Creates an order listing form
     * @param Sabai $application
     * @param Sabai_Addon_Content_Entity $entity
     * @param string $planType
     * @param string $currentRoute
     * @param array $paypalConf
     * @param string $submitLabel
     * @param int $weight
     * @throws Sabai_RuntimeException
     */
    public function help(Sabai $application, Sabai_Addon_Content_Entity $entity, $planType, $currentRoute, array $paypalConf, $submitLabel, $weight = 50)
    {
        $has_pending_order = $application->getModel('Order', 'PaidListings')
            ->entityId_is($entity->getId())
            ->planType_is($planType)
            ->userId_is($application->getUser()->id)
            ->status_in(array(
                Sabai_Addon_PaidListings::ORDER_STATUS_PENDING,
                Sabai_Addon_PaidListings::ORDER_STATUS_PROCESSING,
                Sabai_Addon_PaidListings::ORDER_STATUS_AWAITING_FULLFILLMENT
            ))
            ->count();
        
        if ($has_pending_order) {
            throw new Sabai_RuntimeException(__('There is already a pending order placed for this content.', 'sabai-directory'));
        }
        
        $form = array(
            '#disable_back_btn' => true,
            'plan' => array(
                '#type' => 'radios',
                '#title' => __('Select Plan', 'sabai-directory'),
                '#options' => array(),
                '#required' => true,
                '#weight' => $weight,
            ),
            Sabai_Addon_Form::FORM_SUBMIT_BUTTON_NAME => array(
                array(
                    '#type' => 'submit',
                    '#value' => $submitLabel,
                    '#submit' => array(
                        array(array($this, 'submitForm'), array($application, $currentRoute, $paypalConf))
                    ),
                    '#btn_type' => 'primary',
                ),
            ),
        ); 
        $plans = $application->getModel('Plan', 'PaidListings')->type_is($planType)->active_is(1)->fetch(0, 0, 'weight', 'ASC');
        $paid_plan_ids = array();
        foreach ($plans as $plan) {
            $form['plan']['#options'][$plan->id] = $plan->name . ' - ' . $application->PaidListings_MoneyFormat($plan->price, $paypalConf['currency']);
            $form['plan']['#options_description'][$plan->id] = $plan->description;
            if ($plan->price) {
                $paid_plan_ids[] = $plan->id;
            }
        }
        if (!empty($form['plan']['#options'])) {
            $form['plan']['#default_value'] = array_shift(array_keys($form['plan']['#options']));
            if (!empty($paid_plan_ids)) {
                $form['gateway'] = array(
                    '#type' => 'radios',
                    '#title' => __('Payment Method', 'sabai-directory'),
                    '#options' => array('paypal' => '<img style="vertical-align:middle;margin:0 3px !important;" src="' . $application->getPlatform()->getAssetsUrl($application->getAddonPackage('PayPal')) . '/images/paypal.gif" alt="PayPal" />'),
                    '#title_no_escape' => true,
                    '#default_value' => 'paypal',
                    '#states' => array(
                        'visible' => array('input[name="plan[0]"]' => array('type' => 'value', 'value' => $paid_plan_ids)),
                    ),
                    '#weight' => $weight + 1,
                    '#required' => array($this, 'isPaymentMethodRequired'),
                );
                $form['#paid_plan_ids'] = $paid_plan_ids;
            }
        }
        
        return $form;
    }
    
    public function isPaymentMethodRequired($form)
    {
        return !empty($form->settings['#paid_plan_ids']) && in_array($form->values['plan'], $form->settings['#paid_plan_ids']);
    }
    
    public function submitForm(Sabai_Addon_Form_Form $form, Sabai $application, $currentRoute, $paypalConf)
    {       
        if (!$plan = $application->getModel('Plan', 'PaidListings')->fetchById($form->values['plan'])) {
            return false;
        }
        if (!$plan->price) {
            return;
        }
 
        $request = array(
            'RETURNURL' => (string)$application->Url($currentRoute, array(Sabai_Addon_Form::FORM_BUILD_ID_NAME => $form->settings['#build_id'])),
            'CANCELURL' => (string)$application->Url($currentRoute),
            'NOSHIPPING' => 1,
            'ALLOWNOTE' => 0,
            'SOLUTIONTYPE' => 'Sole',
            'LOCALECODE' => $application->getPlatform()->getLocale(),
            'EMAIL' => $application->getUser()->email,
            'BRANDNAME' => $application->getPlatform()->getSiteName(),
            'PAYMENTREQUEST_0_AMT' => $plan->price,
            'PAYMENTREQUEST_0_ITEMAMT' => $plan->price,
            'PAYMENTREQUEST_0_CURRENCYCODE' => $paypalConf['currency'],
            'PAYMENTREQUEST_0_PAYMENTACTION' => 'Sale',
            'L_PAYMENTREQUEST_0_NAME0' => $plan->name,
            'L_PAYMENTREQUEST_0_DESC0' => $plan->description,
            'L_PAYMENTREQUEST_0_AMT0' => $plan->price,
            'L_PAYMENTREQUEST_0_QTY0' => 1,
            'L_PAYMENTREQUEST_0_ITEMCATEGORY0' => 'Digital',
        );
        $response = $application->PayPal_Request('SetExpressCheckout', $request, $paypalConf);
        $form->redirect = $application->Url(array('script_url' => $application->PayPal_ExpressCheckoutUrl($response, !empty($paypalConf['sb']))));
    }
}