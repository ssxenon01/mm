<?php
require_once dirname(__FILE__) . '/PaidListings/IFeatures.php';
require_once dirname(__FILE__) . '/PaidListings/IPlanTypes.php';

class Sabai_Addon_PaidDirectoryListings extends Sabai_Addon
    implements Sabai_Addon_System_IMainRouter,
               Sabai_Addon_System_IAdminRouter,
               Sabai_Addon_PaidListings_IFeatures,
               Sabai_Addon_System_IAdminMenus,
               Sabai_Addon_PaidListings_IPlanTypes
{
    const VERSION = '1.2.31', PACKAGE = 'sabai-directory';
    
    /* Start implementation of Sabai_Addon_System_IMainRouter */
    
    public function systemGetMainRoutes()
    {
        $addon = $this->_application->getAddon('Directory');
        $routes = array(
            '/' . $addon->getDashboardSlug() . '/add' => array(
                'controller' => 'AddListing',
                'controller_addon' => 'PaidDirectoryListings',
                'priority' => 6,
                'title_callback' => true,
                'callback_path' => 'add_listing',
                'callback_addon' => 'Directory',
            ),
            '/' . $addon->getDashboardSlug() . '/:listing_id/renew' => array(
                'controller' => 'RenewMyListing',
                'title_callback' => true,
                'access_callback' => true,
                'callback_path' => 'renew_my_listing',
                'controller_addon' => 'PaidDirectoryListings',
                'priority' => 6,
            ),
            '/' . $addon->getDashboardSlug() . '/:listing_id/addons' => array(
                'controller' => 'OrderMyListingAddons',
                'title_callback' => true,
                'callback_path' => 'order_my_listing_addons',
                'controller_addon' => 'PaidDirectoryListings',
                'priority' => 6,
            ),
            '/' . $addon->getDashboardSlug() . '/orders' => array(
                'controller' => 'MyOrders',
                'title_callback' => true,
                'callback_path' => 'my_orders',
                'controller_addon' => 'PaidDirectoryListings',
                'priority' => 6,
                'type' => Sabai::ROUTE_TAB,
            ),
            '/sabai/directory/add' => array(
                'controller' => 'AddListing',
                'type' => Sabai::ROUTE_CALLBACK,
                'controller_addon' => 'PaidDirectoryListings',
                'priority' => 6,
            ),
        );
        $routes += $this->_getMainRoutes($addon);
        foreach ($this->_application->getModel('Addon', 'System')->parentAddon_is('Directory')->fetch() as $addon) {
            $routes += $this->_getMainRoutes($this->_application->getAddon($addon->name));
        }
        return $routes;
    }
    
    private function _getMainRoutes(Sabai_Addon $addon)
    {
        return array(
            '/' . $addon->getDirectorySlug() . '/add' => array(
                'controller' => 'AddListing',
                'access_callback' => true,
                'title_callback' => true,
                'callback_path' => 'add_listing',
                'callback_addon' => 'Directory',
                'controller_addon' => 'PaidDirectoryListings',
                'priority' => 6,
            ),
            
            '/' . $addon->getDirectorySlug() . '/' . $addon->getConfig('pages', 'listing_slug') . '/:slug/' . $addon->getSlug('claim') => array(
                'controller' => 'ClaimListing',
                'access_callback' => true,
                'title_callback' => true,
                'callback_addon' => 'Directory',
                'callback_path' => 'claim',
                'controller_addon' => 'PaidDirectoryListings',
                'priority' => 6,
            ),
        );
    }

    public function systemOnAccessMainRoute(Sabai_Context $context, $path, $accessType, array &$route)
    {
        switch ($path) {
            case 'renew_my_listing':
                $expires_at = $context->entity->directory_claim[$this->_application->getUser()->id]['expires_at'];
                // Check if the claim can expire and if expired the expiration date is within the grace period
                return !empty($expires_at) // never expires if empty
                    && $expires_at > time() - ($this->_application->Entity_Addon($context->entity)->getConfig('claims', 'grace_period') * 86400);
        }
    }

    public function systemGetMainRouteTitle(Sabai_Context $context, $path, $title, $titleType, array $route)
    {
        switch ($path) {
            case 'my_orders':
                return __('Orders', 'sabai-directory');
            case 'renew_my_listing':
                return sprintf(__('Renew Claim - %s', 'sabai-directory'), $context->entity->getTitle());
            case 'order_my_listing_addons':
                return sprintf(__('Order Add-ons - %s', 'sabai-directory'), $context->entity->getTitle());
        }
    }

    /* End implementation of Sabai_Addon_System_IMainRouter */
    
    /* Start implementation of Sabai_Addon_System_IAdminRouter */
    
    public function systemGetAdminRoutes()
    {
        $routes = array(
            '/paiddirectorylistings' => array(
                'controller' => 'Orders',
                'title_callback' => true,
                'controller_addon' => 'PaidDirectoryListings',
                'access_callback' => true,
                'callback_path' => 'payments',
                'data' => array('clear_tabs' => true),
            ),
            '/paiddirectorylistings/plans' => array(
                'controller' => 'Plans',
                'title_callback' => true,
                'controller_addon' => 'PaidDirectoryListings',
                'callback_path' => 'payments_plans',
            ),
            '/paiddirectorylistings/plans/add' => array(
                'controller' => 'AddPlan',
                'title_callback' => true,
                'controller_addon' => 'PaidDirectoryListings',
                'callback_path' => 'payments_plans_add',
            ),
            '/paiddirectorylistings/plans/:plan_id' => array(
                'controller' => 'EditPlan',
                'title_callback' => true,
                'controller_addon' => 'PaidDirectoryListings',
                'format' => array(':plan_id' => '\d+'),
                'callback_path' => 'payments_plans_edit',
            ),
            '/paiddirectorylistings/plans/:plan_id/delete' => array(
                'controller' => 'DeletePlan',
                'title_callback' => true,
                'controller_addon' => 'PaidDirectoryListings',
                'callback_path' => 'payments_plans_delete',
            ),
            '/settings/paiddirectorylistings' => array(
                'controller' => 'Settings',
                'access_callback' => true,
                'title_callback' => true,
                'controller_addon' => 'PaidDirectoryListings',
                'callback_path' => 'settings',
            ),
        );
        $routes += $this->_getAdminRoutes($this->_application->getAddon('Directory'));
        foreach ($this->_application->getModel('Addon', 'System')->parentAddon_is('Directory')->fetch() as $addon) {
            $routes += $this->_getAdminRoutes($this->_application->getAddon($addon->name));
        }
        return $routes;
    }

    private function _getAdminRoutes(Sabai_Addon $addon)
    {
        return array(
            '/' . $addon->getDirectorySlug() . '/claims/:claim_id' => array(
                'controller' => 'ViewListingClaim',
                'format' => array(':claim_id' => '\d+'),
                'title_callback' => true,
                'access_callback' => true,
                'controller_addon' => 'PaidDirectoryListings',
                'callback_path' => 'claim',
                'callback_addon' => 'Directory',
                'priority' => 6,
            ),
        );
    }

    public function systemOnAccessAdminRoute(Sabai_Context $context, $path, $accessType, array &$route)
    {
        switch ($path) {
            case 'payments':
                $context->addTemplateDir($this->_application->getPlatform()->getAssetsDir('sabai-directory') . '/templates');
                return true;
            case 'settings':
                return true;
        }
    }

    public function systemGetAdminRouteTitle(Sabai_Context $context, $path, $title, $titleType, array $route)
    {
        switch ($path) {
            case 'payments':
                return $titleType === Sabai::ROUTE_TITLE_TAB_DEFAULT ? __('Orders', 'sabai-directory') : __('Payment Orders', 'sabai-directory');
            case 'payments_plans':
                return __('Payment Plans', 'sabai-directory');
            case 'payments_plans_add':
                return __('Add Plan', 'sabai-directory');
            case 'payments_plans_edit':
                return __('Edit Plan', 'sabai-directory');
            case 'payments_plans_delete':
                return __('Delete Plan', 'sabai-directory');
            case 'settings':
                return __('Payment Settings', 'sabai-directory');
        }
    }

    /* End implementation of Sabai_Addon_System_IAdminRouter */
    
    /* Start implementation of Sabai_Addon_PaidListings_IFeatures */
    
    public function paidListingsGetFeatureNames()
    {
        return array('paiddirectorylistings_claim', 'paiddirectorylistings_renew', 'paiddirectorylistings_featured');
    }
    
    public function paidListingsGetFeature($featureName)
    {
        require_once dirname(__FILE__) . '/PaidDirectoryListings/Feature.php';
        return new Sabai_Addon_PaidDirectoryListings_Feature($this, $featureName);
    }
    
    /* End implementation of Sabai_Addon_PaidListings_IFeatures */
   
    
    /* Start implmentation of Sabai_Addon_System_IAdminMenus */
    
    public function systemGetAdminMenus()
    {
        $icon_path = str_replace($this->_application->getPlatform()->getSiteUrl() . '/', '', $this->_application->getPlatform()->getAssetsUrl('sabai-directory'));
        return array(
            '/paiddirectorylistings' => array(
                'label' => __('Payments', 'sabai-directory'),
                'title' => __('Orders', 'sabai-directory'),
                'icon' => $icon_path . '/images/icon.png',
                'icon_dark' => $icon_path . '/images/icon_dark.png',
            ),
            '/paiddirectorylistings/plans' => array(
                'title' => __('Plans', 'sabai-directory'),
                'parent' => '/paiddirectorylistings',
            ),
        );
    }
    
    /* End implmentation of Sabai_Addon_System_IAdminMenus */
    
    
    /* Start implementation of Sabai_Addon_PaidListings_IPlanTypes */
    
    public function paidListingsGetPlanTypes()
    {
        return array('directory_listing', 'directory_listing_renewal', 'directory_listing_addon');
    }
    
    /* End implementation of Sabai_Addon_PaidListings_IPlanTypes */
    
    public function getPlanTypes()
    {
        return array(
            'directory_listing' => array(
                'label' => __('Base', 'sabai-directory'),
                'title' => __('Base Plan', 'sabai-directory'),
                'features' => array('paiddirectorylistings_claim', 'paiddirectorylistings_featured'),
                'default_feature' => 'paiddirectorylistings_claim',
            ),
            'directory_listing_renewal' => array(
                'label' => __('Renewal', 'sabai-directory'),
                'title' => __('Renewal Plan', 'sabai-directory'),
                'features' => array('paiddirectorylistings_renew', 'paiddirectorylistings_featured'),
                'default_feature' => 'paiddirectorylistings_renew',
            ),
            'directory_listing_addon' => array(
                'label' => __('Add-on', 'sabai-directory'),
                'title' => __('Add-on Plan', 'sabai-directory'),
                'features' => array('paiddirectorylistings_featured'),
            ),
        );
    }
    
    public function onDirectoryInstallSuccess($addon)
    {        
        $this->_application->getAddon('System')->reloadRoutes($this)->reloadRoutes($this, true);
    }
  
    public function onDirectoryUninstallSuccess($addon)
    {
        $this->_application->getAddon('System')->reloadRoutes($this)->reloadRoutes($this, true);
    }
    
    public function onDirectoryUpgradeSuccess(Sabai_Addon $addon, $log, $previousVersion)
    {
        $this->_application->getAddon('System')->reloadRoutes($this)->reloadRoutes($this, true);
    }
    
    public function onPaidDirectoryListingsInstallSuccess($addon)
    {
        $this->_createDefaultPlans();
    }
    
    private function _createDefaultPlans()
    {
        // Create default plans
        $model = $this->_application->getModel(null, 'PaidListings');
        $plan = $model->create('Plan')->markNew();
        $plan->name = __('7-day Free Trial', 'sabai-directory');
        $plan->description = __('Try and see if listing on this site works for your business.', 'sabai-directory');
        $plan->type = 'directory_listing';
        $plan->price = 0.00;
        $plan->features = array(
            'paiddirectorylistings_claim' => array('enable' => true, 'duration' => 7),
        );
        $plan = $model->create('Plan')->markNew();
        $plan->name = __('30-day Basic Listing', 'sabai-directory');
        $plan->description = __('Claim your listing for 30 days.', 'sabai-directory');
        $plan->type = 'directory_listing';
        $plan->price = 15.00;
        $plan->weight = 1;
        $plan->features = array(
            'paiddirectorylistings_claim' => array('enable' => true, 'duration' => 30),
        );
        $plan->active = true;
        $plan = $model->create('Plan')->markNew();
        $plan->name = __('30-day Renewal Plan', 'sabai-directory');
        $plan->description = __('Extend the duration of your claim for additional 30 days at a discounted price.', 'sabai-directory');
        $plan->type = 'directory_listing_renewal';
        $plan->price = 10.00;
        $plan->weight = 2;
        $plan->features = array(
            'paiddirectorylistings_renew' => array('enable' => true, 'duration' => 30),
        );
        $plan->active = true;
        $plan = $model->create('Plan')->markNew();
        $plan->name = __('30-day Featured Listing Add-on', 'sabai-directory');
        $plan->description = __('Get your listing featured on homepage for 30 days.', 'sabai-directory');
        $plan->type = 'directory_listing_addon';
        $plan->price = 15.00;
        $plan->weight = 3;
        $plan->features = array(
            'paiddirectorylistings_featured' => array('enable' => true, 'duration' => 30),
        );
        $plan->active = true;
        $model->commit();
    }
        
    public function onPaidListingsOrderReceived($order)
    {        
        $this->_application->PaidDirectoryListings_SendOrderNotification(array('received', 'received_admin'), $order);
    }
    
    public function onPaidListingsOrderComplete($order)
    {        
        $this->_application->PaidDirectoryListings_SendOrderNotification('complete', $order); 
    }

    public function onPaidListingsOrderAwaitingFullfillment($order)
    {        
        $this->_application->PaidDirectoryListings_SendOrderNotification('awaiting_fullfillment_admin', $order); 
    }
    
    public function onSystemEmailSettingsFilter(&$settings, $addonType)
    {
        if ($addonType !== 'Directory') return;
        
        $settings += array(
            'order_received' => array(
                'type' => 'user',
                'title' => __('Order Received Notification Email', 'sabai-directory'),
                'description' => __('If enabled, a notification email is sent to the user when a user places an order.', 'sabai-directory'),
                'tags' => $this->_getTemplateTags(),
                'enable' => true,
                'email' => array(
                    'subject' => __('[{site_name}] We have received your order (Order ID: {order_id})', 'sabai-directory'),
                    'body' => __('Hi {recipient_name},
                
We have received an order from you on {order_date}.

Please review your order below:

------------------------------------
{order_plan} - {order_price} {order_currency}
------------------------------------

You can view the details of the order at {order_url}.

Regards,
{site_name}
{site_url}', 'sabai-directory'),
                ),
            ),
            'order_complete' => array(
                'type' => 'user',
                'title' => __('Order Complete Notification Email', 'sabai-directory'),
                'description' => __('If enabled, a notification email is sent to the user when an order is complete.', 'sabai-directory'),
                'tags' => $this->_getTemplateTags(),
                'enable' => true,
                'email' => array(
                    'subject' => __('[{site_name}] Your order (ID: {order_id}) is complete', 'sabai-directory'),
                    'body' => __('Hi {recipient_name},
                
We have processed your order placed on {order_date} and its now complete.

------------------------------------
{order_plan} - {order_price} {order_currency}
------------------------------------

You can view the details of the order at {order_url}.

Regards,
{site_name}
{site_url}', 'sabai-directory'),
                ),
            ),  
            'order_received_admin' => array(
                'type' => 'admin',
                'title' => __('Order Received Admin Notification Email', 'sabai-directory'),
                'description' => __('If enabled, a notification email is sent to administrators when a user place an order and payment received.', 'sabai-directory'),
                'tags' => $this->_getOrderTemplateTags(),
                'enable' => true,
                'email' => array(
                    'subject' => __('[{site_name}] A new order (Order ID: {order_id}) has been placed', 'sabai-directory'),
                    'body' => __('Hi {recipient_name},
                
A new order from {order_user_name} ({order_user_email}) has been placed on {order_date}.

------------------------------------
{order_plan} - {order_price} {order_currency}
------------------------------------

You can view the details of the order at {order_admin_url}.

Regards,
{site_name}
{site_url}', 'sabai-directory'),
                ),
            ),
            'order_awaiting_fullfillment_admin' => array(
                'type' => 'admin',
                'title' => __('Order Awaiting Fullfillment Admin Notification Email', 'sabai-directory'),
                'description' => __('If enabled, a notification email is sent to administrators when ordered items for an order are ready for delivery.', 'sabai-directory'),
                'tags' => $this->_getOrderTemplateTags(),
                'enable' => true,
                'email' => array(
                    'subject' => __('[{site_name}] There is an order (Order ID: {order_id}) awaiting fullfillment', 'sabai-directory'),
                    'body' => __('Hi {recipient_name},

The following order placed on {order_date} is awaiting fullfillment.

------------------------------------
{order_plan} - {order_price} {order_currency}
------------------------------------

You can view the details of the order at {order_admin_url}.

Regards,
{site_name}
{site_url}', 'sabai-directory'),
                ),
            ),
        );
    }
    
    private function _getTemplateTags()
    {
        return array_merge($this->_getOrderTemplateTags(), $this->_getListingTemplateTags());
    }
        
    private function _getOrderTemplateTags()
    {
        return array('{order_id}', '{order_plan}', '{order_price}', '{order_currency}', '{order_user_name}', '{order_user_email}', '{order_url}', '{order_admin_url}', '{order_date}', '{order_status}');
    }
    
    private function _getListingTemplateTags()
    {
        return array('{listing_id}', '{listing_title}', '{listing_summary}', '{listing_author_name}', '{listing_author_email}', '{listing_url}', '{listing_date}');
    }
    
    public function onDirectoryMyListingActionsFilter(&$actions, $bundle, $listing, $identity, $expired)
    {
        $expires_at = $listing->directory_claim[$identity->id]['expires_at'];
        $actions[] = array(
            'label' => __('Renew', 'sabai-directory'),
            'path' => '/renew',
            'icon' => 'refresh',
            'disabled' => empty($expires_at) // never expires
                || $expires_at < time() - ($this->_application->Entity_Addon($listing)->getConfig('claims', 'grace_period') * 86400), // expiration date is not within the grace period
            'title' => __('Renew claim', 'sabai-directory')
        );
        $actions[] = array(
            'label' => __('Add-ons', 'sabai-directory'),
            'path' => '/addons',
            'icon' => 'plus-sign-alt',
            'disabled' => !empty($expires_at) // this listing expires
                && $expires_at < time() - ($this->_application->Entity_Addon($listing)->getConfig('claims', 'grace_period') * 86400), // expiration date is not within the grace period
            'title' => __('Order add-ons', 'sabai-directory')
        );
        $actions[] = array(
            'label' => __('View Orders', 'sabai-directory'),
            'path' => $this->_application->Url('/' . $this->_application->getAddon('Directory')->getDashboardSlug() . '/orders', array('entity_id' => $listing->getId())),
            'icon' => 'list-alt',
            'title' => __('View all orders', 'sabai-directory')
        );
    }
 
    public function onEntityUpdateContentEntitySuccess($bundle, $entity, $oldEntity, $values, $extraArgs)
    {
        if ($bundle->type !== 'directory_listing'
            || !isset($values['content_post_status']) // content status changed?
            || !($oldEntity->isPending() && $entity->isPublished()) // chanegd from pending to published?
        ) {
            return;
        }
        
        // Apply features if any pending
        $this->_application->PaidListings_ApplyFeatures($entity);
    }
    
    public function onFormBuildDirectoryAdminSettings(&$form, &$storage)
    {
        $form['claims']['duration']['#type'] = 'hidden';
    }
    
    public function onFormBuildDirectoryAdminListingclaims(&$form, &$storage)
    {
        if (empty($form['claims']['#options'])) return;
        
        $claim_ids = array_keys($form['claims']['#options']);
        $order_ids = $this->_application->getModel(null, 'PaidListings')
            ->getGateway('OrderItem')
            ->getOrderIdsByMeta('claim_id', $claim_ids);
        $form['claims']['#header']['order'] = __('Order', 'sabai-directory');
        foreach ($claim_ids as $claim_id) {
            if (!isset($order_ids[$claim_id])) {
                continue;
            }
            $order_id = $order_ids[$claim_id];
            $form['claims']['#options'][$claim_id]['order'] = $this->_application->LinkTo(
                '#' . str_pad($order_id, 5, 0, STR_PAD_LEFT),
                $this->_application->Url('/paiddirectorylistings', array('order_id' => $order_id))
            );
        }
    }
    
    public function onContentAdminPostsDirectoryListingLinksFilter(&$links, $entity, $status)
    {
        $links[] = $this->_application->LinkTo(__('View Orders', 'sabai-directory'), $this->_application->Url('/paiddirectorylistings', array('entity_id' => $entity->getId())));
    }
    
    public function getDefaultConfig()
    {
        return array(
            'paypal' => array(
                'version' => '63.0',
                'user' => '',
                'pwd' => '',
                'sig' => '',
                'sb' => true,
                'sb_user' => '',
                'sb_pwd' => '',
                'sb_sig' => '',
                'sb_processing' => false,
                'currency' => 'USD',
            ),
        );
    }
    
    public function isInstallable($version)
    {
        if (!parent::isInstallable($version)) return false;
        
        $required_addons = array(
            'Directory' => '1.2.0',
            'PaidListings' => '1.2.0',
            'PayPal' => '1.2.0',
        );
        return $this->_application->CheckAddonVersion($required_addons);
    }
    
    public function hasSettingsPage($currentVersion)
    {
        return array('url' => '/settings/paiddirectorylistings', 'modal' => true, 'modal_width' => 720);
    }
}
