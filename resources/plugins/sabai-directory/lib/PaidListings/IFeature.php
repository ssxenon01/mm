<?php
interface Sabai_Addon_PaidListings_IFeature
{
    public function paidListingsFeatureGetInfo($key = null);
    public function paidListingsFeatureGetSettingsForm(array $settings, array $parents);
    public function paidListingsFeatureGetOrderDescription(Sabai_Addon_Content_Entity $content, array $order, array $settings);
    public function paidListingsFeatureIsAppliable(Sabai_Addon_Content_Entity $content, array $order, SabaiFramework_User_Identity $user, $isManual = false);
    public function paidListingsFeatureApply(Sabai_Addon_Content_Entity $content, array $orderData, SabaiFramework_User_Identity $user);
    public function paidListingsFeatureUnapply(Sabai_Addon_Content_Entity $content, array $orderData, SabaiFramework_User_Identity $user);
}