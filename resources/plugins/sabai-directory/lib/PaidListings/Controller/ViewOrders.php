<?php
abstract class Sabai_Addon_PaidListings_Controller_ViewOrders extends Sabai_Addon_Form_Controller
{
    protected function _doGetFormSettings(Sabai_Context $context, array &$formStorage)
    {
        if (($order_id = $context->getRequest()->asInt('order_id'))
            && ($order = $this->getModel('Order', 'PaidListings')->fetchById($order_id))
            && in_array($order->plan_type, array_keys($this->_getPlanTypes($context)))
            && $this->_isValidOrder($context, $order)
        ) {
            $order->with('Entity');
            $order_title = sprintf(__('Order %s', 'sabai-directory'), $order->getLabel());
            $context->setInfo($order_title, $this->Url($context->getRoute(), array('order_id' => $order->id)));
            return $this->_viewSingleOrder($context, $order);
        }
        return $this->_viewOrders($context);
    }
    
    protected function _viewSingleOrder(Sabai_Context $context, $order)
    {
        $this->_submitable = false;
        $this->_cancelUrl = $context->getRoute();
        
        // Init form
        $form = array(
            'order_id' => array(
                '#type' => 'hidden',
                '#value' => $order->id,
            ),
        );
        
        $tab = $context->getRequest()->asStr('tab', 'items', array('items', 'logs'));
        if ($tab === 'logs') {
            $this->_addSingleOrderLogsForm($context, $order, $form);
        } else {
            $this->_addSingleOrderItemsForm($context, $order, $form);
        }
        
        // Set template for viewing this order
        $context->addTemplate('paidlistings_order_single')
            ->setAttributes(array(
                'order' => $order,
                'plan_types' => $this->_getPlanTypes($context),
                'tab' => $tab,
            ));
        
        return $form;
    }
    
    protected function _addSingleOrderLogsForm(Sabai_Context $context, $order, &$form)
    {
        // Create order logs table
        $form['logs'] = array(
            '#type' => 'tableselect',
            '#header' => array(
                'number' => '#',
                'date' => __('Log Date', 'sabai-directory'),
                'message' => __('Log Message', 'sabai-directory'),
                'item' => __('Order Item', 'sabai-directory'),
                'status' => __('Order Status', 'sabai-directory'),
            ),
            '#options' => array(),
            '#disabled' => true,
        );
        
        $number = 0;
        $order_logs = $order->OrderLogs->with('OrderItem')->getArray();
        ksort($order_logs);
        foreach ($order_logs as $order_log) {
            if ($order_log->OrderItem) {
                $ifeature = $this->PaidListings_FeatureImpl($order_log->OrderItem->feature_name);
                $item = sprintf(__('%s (%s)', 'sabai-directory'), $ifeature->paidListingsFeatureGetInfo('label'), Sabai::h($order_log->OrderItem->getLabel()));
            } else {
                $item = '';
            }
            $form['logs']['#options'][$order_log->id] = array(
                'number' => ++$number,
                'item' => $item,
                'message' => $order_log->message,
                'date' => $this->DateTime($order_log->created),
                'status' => $order_log->status ? sprintf('<span class="sabai-label %s">%s</span>', $order->getStatusLabelClass($order_log->status), $order->getStatusLabel($order_log->status)) : '',
            );
            if ($order_log->is_error) {
                $form['logs']['#row_attributes'][$order_log->id]['@row']['class'] = 'sabai-error';
            }
        }
    }
    
    protected function _addSingleOrderItemsForm(Sabai_Context $context, $order, &$form)
    {
        // Create order items table
        $form['items'] = array(
            '#type' => 'tableselect',
            '#header' => array(
                'id' => __('Item ID', 'sabai-directory'),
                'name' => __('Item Name', 'sabai-directory'),
                'description' => __('Description', 'sabai-directory'),
                'status' => __('Status', 'sabai-directory'),
            ),
            '#options' => array(),
            '#options_disabled' => array(),
            '#multiple' => true,
            '#disabled' => true,
        );
        $this->_submitButtons['submit'] = array(
            '#value' => __('Deliver Items', 'sabai-directory'),
            '#btn_type' => 'primary',
        );
        $this->_ajaxOnSuccess = 'function (result, target, trigger) {target.hide(); return true;}';

        foreach ($order->OrderItems->with('Feature')->with('OrderItemMetas') as $order_item) {
            $ifeature = $this->PaidListings_FeatureImpl($order_item->feature_name);
            $order_item_data = $order_item->OrderItemMetas->getArray('value', 'key');
            switch ($order_item->status) {
                case Sabai_Addon_PaidListings::ORDER_ITEM_STATUS_PENDING:
                    if (($order->isAwaitingFullfillment() || $order->isComplete())
                        && $ifeature->paidListingsFeatureIsAppliable($order->Entity, $order_item_data, $order->User, true)
                    ) {
                        $class = 'sabai-label-info';
                        $status = __('Pending Delivery', 'sabai-directory');
                        $this->_submitable = $this->getUser()->isAdministrator();
                        $form['items']['#disabled'] = !$this->_submitable;
                    } else {
                        $class = 'sabai-label-warning';
                        $status = __('Pending', 'sabai-directory');
                        $form['items']['#options_disabled'][] = $order_item->id;
                    }
                    break;
                case Sabai_Addon_PaidListings::ORDER_ITEM_STATUS_DELIVERED:
                    $class = 'sabai-label-success';
                    $status = __('Delivered', 'sabai-directory');
                    $form['items']['#options_disabled'][] = $order_item->id;
                    break;
                case Sabai_Addon_PaidListings::ORDER_ITEM_STATUS_CANCELLED:
                    $class = 'sabai-label-important';
                    $status = __('Cancelled', 'sabai-directory');
                    $form['items']['#options_disabled'][] = $order_item->id;
                    break;
            }
            $form['items']['#options'][$order_item->id] = array(
                'id' => Sabai::h($order_item->getLabel()),
                'name' => $ifeature->paidListingsFeatureGetInfo('label'),
                'description' => $ifeature->paidListingsFeatureGetOrderDescription($order->Entity, $order_item_data, $order_item->Feature->settings),
                'status' => sprintf('<span class="sabai-label %s">%s</span>', $class, $status),
            );
        }
    }
    
    public function submitForm(Sabai_Addon_Form_Form $form, Sabai_Context $context)
    {
        if (!empty($form->values['orders'])) {
            switch ($form->values['action']) {
                case 'delete':
                    foreach ($form->values['orders'] as $order_id) {
                        if (isset($context->orders_deletable[$order_id]) && isset($context->orders[$order_id])) {
                            $context->orders[$order_id]->markRemoved();
                        }
                    }
                    $this->getModel(null, 'PaidListings')->commit();
                    break;
                case 'update_status':
                    if (empty($form->values['new_status'])) break;
                    $orders_updated = array();
                    foreach ($form->values['orders'] as $order_id) {
                        if (!isset($context->orders[$order_id])) continue;
                        $order = $context->orders[$order_id];
                        if ($order->status == $form->values['new_status']) continue;

                        $order->status = $form->values['new_status'];
                        $orders_updated[] = $order;
                    }
                    $this->getModel(null, 'PaidListings')->commit();
                    foreach ($orders_updated as $order) {
                        $order->reload();
                        $this->doEvent('PaidListingsOrderStatusChange', array($order));
                    }
                    break;
            }
            $context->setSuccess();
        } elseif (!empty($form->values['items'])) {
            // Order items submitted for process
            $order = $context->order->with('Entity');
            $this->Entity_LoadFields($order->Entity);
            // Apply features that have not yet been applied
            $order_items_updated = array();
            foreach ($order->OrderItems->with('Feature')->with('OrderItemMetas') as $order_item) {
                if ($order_item->isComplete()) {
                    continue;
                }
                // Was this item selected?
                if (!in_array($order_item->id, $form->values['items'])) {
                    continue;
                }
                $ifeature = $this->PaidListings_FeatureImpl($order_item->Feature->name);
                $order_item_data = $order_item->OrderItemMetas->getArray('value', 'key');
                if ($ifeature->paidListingsFeatureIsAppliable($order->Entity, $order_item_data, $order->User, true)) {
                    if ($ifeature->paidListingsFeatureApply($order->Entity, $order_item_data, $order->User)) {
                        $order_item->status = Sabai_Addon_PaidListings::ORDER_ITEM_STATUS_DELIVERED;
                        $order_item->createOrderLog(__('Item delivered.', 'sabai-directory'));
                        $order_items_updated[] = $order_item;
                    } else {
                        $order_item->createOrderLog(__('Item delivery failed.', 'sabai-directory'), true);
                    }
                }
            }
            $this->getModel(null, 'PaidListings')->commit();
            if (!empty($order_items_updated)) {
                // Notify that the status of one or more order items have changed
                $this->doEvent('PaidListingsOrderItemsStatusChange', array($order_items_updated));
            }
            
            $context->setSuccess($this->Url($context->getRoute(), array('order_id' => $order->id)));
        }
    }
    
    protected function _viewOrders(Sabai_Context $context)
    {
        // Init variables
        $criteria = $this->_getCriteria($context);
        $sortable_headers = array('date' => 'created');
        $sort = $context->getRequest()->asStr('sort', 'date', array_keys($sortable_headers));
        $order = $context->getRequest()->asStr('order', 'DESC', array('ASC', 'DESC'));
        $url_params = array('sort' => $sort, 'order' => $order);
        // Init entity ID
        if (($entity_id = $context->getRequest()->asInt('entity_id'))
            && ($entity = $this->Entity_Entity('content', $entity_id, false))
        ) {
            $url_params['entity_id'] = $entity_id;
            $criteria->entityId_is($entity_id);
            $context->setInfo(sprintf(__('Orders for %s', 'sabai-directory'), $entity->getTitle()));
        } elseif (($user_id = $context->getRequest()->asInt('user_id'))
            && ($identity = $this->UserIdentity($user_id))
            && !$identity->isAnonymous()
        ) {
            $url_params['user_id'] = $user_id;
            $criteria->userId_is($user_id);
            $context->setInfo(sprintf(__('Orders by %s', 'sabai-directory'), $identity->name));
        }
        // Init status filters and current filter
        $filters = array(0 => $this->LinkToRemote(__('All', 'sabai-directory'), $context->getContainer(), $this->Url($context->getRoute(), $url_params), array(), array('class' => 'sabai-btn sabai-btn-mini')));
        $status_labels = $this->PaidListings_OrderStatusLabels();
        foreach ($status_labels as $status => $status_label) {
            $filters[$status] = $this->LinkToRemote($status_label, $context->getContainer(), $this->Url($context->getRoute(), array('status' => $status) + $url_params), array(), array('class' => 'sabai-btn sabai-btn-mini'));            
        }
        $current_status = $context->getRequest()->asInt('status', 0, array_keys($filters));
        $filters[$current_status]->setAttribute('class', $filters[$current_status]->getAttribute('class') . ' sabai-active');
        if ($current_status) {
            $url_params['status'] = $current_status;
            $criteria->status_is($current_status);
        }

        // Paginate orders
        $pager = $this->getModel('Order', 'PaidListings')
            ->paginateByCriteria($criteria, 20, $sortable_headers[$sort], $order)
            ->setCurrentPage($context->getRequest()->asInt(Sabai::$p, 1));
        
        // Init form
        $form = array(
            'orders' => array(
                '#type' => 'tableselect',
                '#header' => array(
                    'id' => __('Order ID', 'sabai-directory'),
                    'date' => __('Order Date', 'sabai-directory'),
                    'plan' => __('Plan', 'sabai-directory'),
                    'content' => __('Content', 'sabai-directory'),
                    'user' => __('User', 'sabai-directory'),
                    'items' => __('Items', 'sabai-directory'),
                    'price' => __('Price', 'sabai-directory'),
                    'status' => __('Status', 'sabai-directory'),
                ),
                '#options' => array(),
                '#options_disabled' => array(),
                '#disabled' => true,
                '#multiple' => true,
            ),
        );
        
        // Set sortable headers
        $this->_makeTableSortable($context, $form['orders'], array_keys($sortable_headers), array(), $sort, $order, $url_params);
        
        $plan_types = $this->_getPlanTypes($context);
        $orders_deletable = $orders = array();
        $commit = false;

        foreach ($pager->getElements()->with('User')->with('Plan')->with('Entity')->with('OrderItems') as $order) {
            $order_item_count = array(
                Sabai_Addon_PaidListings::ORDER_ITEM_STATUS_PENDING => 0,
                Sabai_Addon_PaidListings::ORDER_ITEM_STATUS_DELIVERED => 0,
                Sabai_Addon_PaidListings::ORDER_ITEM_STATUS_CANCELLED => 0,
            );
            foreach ($order->OrderItems as $order_item) {
                $order_item_count[$order_item->status]++;
            }
            $order_item_labels = array();
            if (!empty($order_item_count[Sabai_Addon_PaidListings::ORDER_ITEM_STATUS_PENDING])) {
                $order_item_labels[] = '<span class="sabai-label sabai-label-warning">' . sprintf(__('%d Pending', 'sabai-directory'), $order_item_count[Sabai_Addon_PaidListings::ORDER_ITEM_STATUS_PENDING]) . '</span>';
            }
            if (!empty($order_item_count[Sabai_Addon_PaidListings::ORDER_ITEM_STATUS_DELIVERED])) {
                $order_item_labels[] = '<span class="sabai-label sabai-label-success">' . sprintf(__('%d Delivered', 'sabai-directory'), $order_item_count[Sabai_Addon_PaidListings::ORDER_ITEM_STATUS_DELIVERED]) . '</span>';
            }
            if (!empty($order_item_count[Sabai_Addon_PaidListings::ORDER_ITEM_STATUS_CANCELLED])) {
                $order_item_labels[] = '<span class="sabai-label sabai-label-important">' . sprintf(__('%d Cancelled', 'sabai-directory'), $order_item_count[Sabai_Addon_PaidListings::ORDER_ITEM_STATUS_CANCELLED]) . '</span>';
            }
            if ($order->Entity) {
                if ($order->Entity->isPublished()) {
                    $listing_link = $this->Entity_Link($order->Entity);
                } else {
                    $listing_link = Sabai::h($order->Entity->getTitle());
                }
            } else {
                $listing_link = '';
            }
            $status_desc = '';
            if ($order->status === Sabai_Addon_PaidListings::ORDER_STATUS_PROCESSING) {
                $status_desc = $order->getGatewayData('payment_status');
            } elseif ($order->status === Sabai_Addon_PaidListings::ORDER_STATUS_PENDING) {
                $status_desc = $order->getGatewayData('pending_reason');
            }
            $form['orders']['#options'][$order->id] = array(
                'plan' => sprintf(__('%s (%s)', 'sabai-directory'), $order->Plan ? Sabai::h($order->Plan->name) : __('Unknown', 'sabai-directory'), $plan_types[$order->plan_type]['label']),
                'date' => $this->Date($order->created),
                'id' => $this->LinkTo('<strong class="sabai-row-title">' . Sabai::h($order->getLabel()) . '</strong>', $this->Url($context->getRoute(), array('order_id' => $order->id)), array('no_escape' => true), array('title' => sprintf(__('Order %s', 'sabai-directory'), $order->getLabel()))),
                'user' => $this->UserIdentityLink($order->User),
                'items' => implode(PHP_EOL, $order_item_labels),
                'price' => $this->PaidListings_MoneyFormat($order->price, $order->currency),
                'status' => sprintf(
                    '<span class="sabai-label %s" title="%s">%s</span>',
                    $order->getStatusLabelClass(),
                    $status_desc !== '' ? Sabai::h($status_desc) : '',
                    $order->getStatusLabel()
                ),
                'content' => $listing_link,
            );
            // Update order status to completed if no pending order items. This could happen, for example status could not be updated because of database error.
            if (empty($order_item_count[Sabai_Addon_PaidListings::ORDER_ITEM_STATUS_PENDING])
                && !in_array($order->status, array(Sabai_Addon_PaidListings::ORDER_STATUS_COMPLETE, Sabai_Addon_PaidListings::ORDER_STATUS_EXPIRED, Sabai_Addon_PaidListings::ORDER_STATUS_FAILED, Sabai_Addon_PaidListings::ORDER_STATUS_REFUNDED))
            ) {
                $order->status = Sabai_Addon_PaidListings::ORDER_STATUS_COMPLETE;
                $commit = true;
            }
            // Only allow deletion of completed/pending orders or orders without listings
            if ($order->isComplete()
                || $listing_link === ''
                || $order->status !== Sabai_Addon_PaidListings::ORDER_STATUS_AWAITING_FULLFILLMENT
            ) {
                $orders_deletable[$order->id] = $order->id;
            } else {
                $form['orders']['#options_disabled'][] = $order->id;
            }
            $orders[$order->id] = $order;
        }
        
        if ($commit) {
            $this->getModel(null, 'PaidListings')->commit();
        }
        
        $context->addTemplate('paidlistings_orders')
            ->setAttributes(array(
                'links' => array(),
                'filters' => $filters,
                'paginator' => $pager,
                'url_params' => $url_params,
                'orders' => $orders,
                'orders_deletable' => $orders_deletable,
            ));
        
        if ($this->_submitable = $this->getUser()->isAdministrator()) {
            // Do not allow manually changing the status to Complete
            unset($status_labels[Sabai_Addon_PaidListings::ORDER_STATUS_COMPLETE]);
            $this->_submitButtons = array(
                'action' => array(
                    '#type' => 'select',
                    '#options' => array(
                        '' => __('Bulk Actions', 'sabai-directory'),
                        'delete' => __('Delete', 'sabai-directory'),
                        'update_status' => __('Update Status', 'sabai-directory'),
                    ),
                    '#weight' => 1,
                ),
                'new_status' => array(
                    '#type' => 'select',
                    '#default_value' => null,
                    '#multiple' => false,
                    '#options' => $status_labels,
                    '#weight' => 2,
                    '#states' => array(
                        'visible' => array('select[name="action"]' => array('type' => 'value', 'value' => 'update_status')),
                    ),
                ),
                'apply' => array(
                    '#value' => __('Apply', 'sabai-directory'),
                    '#btn_size' => 'small',
                    '#btn_type' => false,
                    '#weight' => 10,
                ),
            );
            $form['orders']['#disabled'] = false;
        }
        
        $form[Sabai::$p] = array('#type' => 'hidden', '#value' => $pager->getCurrentPage());
        
        return $form;
    }
    
    protected function _getOrderDisplayId(Sabai_Addon_PaidListings_Model_Order $order, Sabai_Addon_PaidListings_Model_OrderItem $orderItem = null)
    {
        $order_id = '#' . str_pad($order->id, 5, 0, STR_PAD_LEFT);
        if (!isset($orderItem)) {
            return $order_id;
        }
        return !isset($orderItem) ? $order_id : $order_id . '-' . str_pad($orderItem->id, 5, 0, STR_PAD_LEFT);
    }
    
    protected function _getCriteria(Sabai_Context $context)
    {
        return $this->getModel(null, 'PaidListings')->createCriteria('Order')
            ->planType_in(array_keys($this->_getPlanTypes($context)));
    }
    
    protected function _isValidOrder(Sabai_Context $context, Sabai_Addon_PaidListings_Model_Order $order)
    {
        return true;
    }
    
    abstract protected function _getPlanTypes(Sabai_Context $context);
}
