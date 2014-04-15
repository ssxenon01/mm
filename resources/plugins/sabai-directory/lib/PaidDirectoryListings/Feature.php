<?php
require_once dirname(dirname(__FILE__)) . '/PaidListings/IFeature.php';

class Sabai_Addon_PaidDirectoryListings_Feature implements Sabai_Addon_PaidListings_IFeature
{
    protected $_addon, $_name;
    
    public function __construct(Sabai_Addon_PaidDirectoryListings $addon, $name)
    {
        $this->_addon = $addon;
        $this->_name = $name;
    }
    
    public function paidListingsFeatureGetInfo($key = null)
    {
        switch ($this->_name) {
            case 'paiddirectorylistings_claim':
                $info = array(
                    'label' => __('Claim Listing', 'sabai-directory'),
                    'default_settings' => array(
                        'duration' => 30,
                    ),
                    'bundles' => array('directory_listing'),
                );
                break;
            case 'paiddirectorylistings_renew':
                $info = array(
                    'label' => __('Renew Claim', 'sabai-directory'),
                    'default_settings' => array(
                        'duration' => 30,
                    ),
                    'bundles' => array('directory_listing'),
                );
                break;
            case 'paiddirectorylistings_featured':
                $info = array(     
                    'label' => __('Featured Listing', 'sabai-directory'),
                    'default_settings' => array(
                        'enable' => true,
                        'duration' => 7,
                    ),
                    'bundles' => array('directory_listing'),
                );
                break;
        }
        
        return isset($key) ? $info[$key] : $info;
    }
    
    public function paidListingsFeatureGetSettingsForm(array $settings, array $parents)
    {
        switch ($this->_name) {
            case 'paiddirectorylistings_claim':
            case 'paiddirectorylistings_renew':
                return array(
                    '#title' => __('Claim Lisitng Settings', 'sabai-directory'),
                    'duration' => array(
                        '#type' => 'textfield',
                        '#title' => __('Duration', 'sabai-directory'),
                        '#description' => __('Enter the number of days listings will be claimed.', 'sabai-directory'),
                        '#default_value' => $settings['duration'],
                        '#size' => 7,
                        '#integer' => true,
                        '#field_suffix' => __('day(s)', 'sabai-directory'),
                        '#required' => true,
                    ),
                );
            case 'paiddirectorylistings_featured':
                return array(
                    '#title' => __('Featured Lisitng Settings', 'sabai-directory'),
                    'enable' => array(
                        '#title' => __('Feature on homepage', 'sabai-directory'),
                    ),
                    'duration' => array(
                        '#type' => 'textfield',
                        '#description' => __('Enter the number of days listings will be featured on homepage.', 'sabai-directory'),
                        '#default_value' => $settings['duration'],
                        '#size' => 7,
                        '#integer' => true,
                        '#title' => __('Duration', 'sabai-directory'),
                        '#field_suffix' => __('day(s)', 'sabai-directory'),
                        '#states' => array(
                            'visible' => array(
                                sprintf('input[name="%s[%s][enable][]"]', $this->_addon->getApplication()->Form_FieldName($parents), $this->_name) => array('type' => 'checked', 'value' => true),
                            ),
                        ),
                        '#required' => array(array($this, 'isDurationRequired'), array($parents)),
                    ),
                );
        }
    }
    
    public function isDurationRequired($form, $parents)
    {
        $values = $form->getValue($parents);
        return !empty($values[$this->_name]['enable']);
    }
    
    public function paidListingsFeatureGetOrderDescription(Sabai_Addon_Content_Entity $content, array $order, array $settings)
    {
        switch ($this->_name) {
            case 'paiddirectorylistings_claim':
                return empty($order['duration'])
                    ? __('Claim ownership of listing for unlimited number of days', 'sabai-directory')
                    : sprintf(__('Claim ownership of listing for %d days', 'sabai-directory'), $order['duration']);
            case 'paiddirectorylistings_renew':
                return empty($order['duration'])
                    ? __('Extend ownership of listing for unlimited number of days', 'sabai-directory')
                    : sprintf(__('Extend ownership of listing for %d days', 'sabai-directory'), $order['duration']);
            case 'paiddirectorylistings_featured':
                return empty($order['duration'])
                    ? __('Feature listing on home page for unlimited number of days', 'sabai-directory')
                    : sprintf(__('Feature listing on homepage for %d days', 'sabai-directory'), $order['duration']);
        }
    }
    
    public function paidListingsFeatureApply(Sabai_Addon_Content_Entity $content, array $orderData, SabaiFramework_User_Identity $user)
    {
        switch ($this->_name) {
            case 'paiddirectorylistings_claim':
            case 'paiddirectorylistings_renew':
                $this->_addon->getApplication()->Directory_ClaimListing($content, $user, $orderData['duration']);
                return true;
            case 'paiddirectorylistings_featured':
                if (($content_featured = $content->getFieldValue('content_featured'))
                    && $content_featured[0]['expires_at'] > time()
                ) {
                    $featured_at = $content_featured[0]['featured_at'];
                    $expires_at = $content_featured[0]['expires_at'] + $orderData['duration'] * 86400; // extend expiration time
                } else {
                    $featured_at = time();
                    $expires_at = time() + $orderData['duration'] * 86400;
                }
                $this->_addon->getApplication()->getAddon('Entity')
                    ->updateEntity($content, array('content_featured' => array('value' => true, 'featured_at' => $featured_at, 'expires_at' => $expires_at)));
                return true;
        }
    }
    
    public function paidListingsFeatureUnapply(Sabai_Addon_Content_Entity $content, array $order, SabaiFramework_User_Identity $user)
    {
        switch ($this->_name) {
            case 'paiddirectorylistings_claim':
            case 'paiddirectorylistings_renew':
                if ((!$current_claim = $content->getFieldValue('directory_claim'))
                    || !isset($current_claim[$user->id])
                    || $current_claim[$user->id]['expires_at'] < time()
                ) {
                    // No valid claim
                    return;
                }
                $expires_at = $current_claim[$user->id]['expires_at'] - $order['duration'] * 86400; // reset extended expiration time
                if ($expires_at < time()) {
                    $value = false;
                } else {
                    $value = array('claimed_by' => $user->id, 'claimed_at' => $current_claim[$user->id]['claimed_at'], 'expires_at' => $expires_at);
                }
                $this->_addon->getApplication()->getAddon('Entity')->updateEntity($content, array('directory_claim' => $value));
                return true;
            case 'paiddirectorylistings_featured':
                if ((!$content_featured = $content->getFieldValue('content_featured'))
                    || $content_featured[0]['expires_at'] < time()
                ) {
                    return;
                }
                $expires_at = $content_featured[0]['expires_at'] - $order['duration'] * 86400; // extend expiration time
                if ($expires_at < time()) {
                    $value = false;
                } else {
                    $value = array('value' => true, 'featured_at' => $content_featured[0]['featured_at'], 'expires_at' => $expires_at);
                }
                $this->_addon->getApplication()->getAddon('Entity')->updateEntity($content, array('content_featured' => $value));
                return true;
        }
    }
    
    public function paidListingsFeatureIsAppliable(Sabai_Addon_Content_Entity $content, array $orderData, SabaiFramework_User_Identity $user, $isManual = false)
    {
        switch ($this->_name) {
            case 'paiddirectorylistings_claim':
                $application = $this->_addon->getApplication();
                if (empty($orderData['claim_id'])
                    || (!$claim = $application->getModel('Claim', 'Directory')->fetchById($orderData['claim_id']))
                ) {
                    return false;
                }
                if ($claim->status === 'approved') {
                    return $content->isPublished(); // this should always return true
                }
                if ($claim->status === 'pending_payment') {
                    // Since payment should have been completed at this stage, we can safely change the status of claim to pending.
                    $claim->status = 'pending';
                    $claim->commit();
                    $claim->reload();
                    $application->doEvent('DirectoryListingClaimStatusChange', array($claim));
                }
                return false;
            case 'paiddirectorylistings_renew':
            case 'paiddirectorylistings_featured':
                return $content->isPublished();
        }
    }
}