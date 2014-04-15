<table class="sabai-table sabai-paidlistings-order">
    <thead>
        <tr>
            <th><?php echo __('Field', 'sabai-directory');?></th>
            <th><?php echo __('Value', 'sabai-directory');?></th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td><strong><?php echo __('Order ID', 'sabai-directory');?></strong></td>
            <td><?php Sabai::_h($order->getLabel());?></td>
        </tr>
        <tr>
            <td><strong><?php echo __('Order Date', 'sabai-directory');?></strong></td>
            <td><?php echo $this->Date($order->created);?></td>
        </tr>
        <tr>
            <td><strong><?php echo __('Plan', 'sabai-directory');?></strong></td>
            <td><?php printf(__('%s (%s)', 'sabai-directory'), $order->Plan ? Sabai::h($order->Plan->name) : __('Unknown', 'sabai-directory'), $plan_types[$order->plan_type]['label']);?></td>
        </tr>
        <tr>
            <td><strong><?php echo $this->Entity_BundleLabel($this->Entity_Bundle($order->Entity), true);?></strong></td>
            <td><?php if ($order->Entity):?><?php echo $order->Entity->isPublished() ? $this->LinkTo($order->Entity->getTitle(), $this->Entity_Bundle($order->Entity)->getPath() . '/' . $order->Entity->getId()) : Sabai::h($order->Entity->getTitle());?><?php endif;?>
        <tr>
            <td><strong><?php echo __('User', 'sabai-directory');?></strong></td>
            <td><?php echo $this->UserIdentityLinkWithThumbnailSmall($order->User);?></td>
        </tr>
        <tr>
            <td><strong><?php echo __('Price', 'sabai-directory');?></strong></td>
            <td><?php echo $this->PaidListings_MoneyFormat($order->price, $order->currency);?></td>
        </tr>
        <tr>
            <td><strong><?php echo __('Payment Method', 'sabai-directory');?></strong></td>
            <td><?php echo $order->gateway;?></td>
        </tr>
        <tr>
            <td><strong><?php echo __('Transaction ID', 'sabai-directory');?></strong></td>
            <td><?php echo $order->transaction_id;?></td>
        </tr>
        <tr>
            <td><strong><?php echo __('Status', 'sabai-directory');?></strong></td>
            <td><span class="sabai-label <?php echo $order->getStatusLabelClass();?>"><?php echo $order->getStatusLabel();?></span></td>
        </tr>
    </tbody>
</table>
<br />
<ul class="sabai-nav sabai-nav-tabs" id="sabai-paidlistings-order-tabs">
    <li<?php if ($tab !== 'logs'):?> class="sabai-active"<?php endif;?>><?php echo $this->LinkToRemote(__('Order Items', 'sabai-directory'), $CURRENT_CONTAINER, $this->Url($CURRENT_ROUTE, array('order_id' => $order->id), 'sabai-paidlistings-order-tabs'), array('scroll' => false));?></li>
    <li<?php if ($tab === 'logs'):?> class="sabai-active"<?php endif;?>><?php echo $this->LinkToRemote(__('Order Logs', 'sabai-directory'), $CURRENT_CONTAINER, $this->Url($CURRENT_ROUTE, array('order_id' => $order->id, 'tab' => 'logs'), 'sabai-paidlistings-order-tabs'), array('scroll' => false));?></li>
</ul>
<?php echo $this->Form_Render($form, $form_js);?>