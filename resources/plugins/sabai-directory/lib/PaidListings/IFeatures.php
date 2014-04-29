<?php
interface Sabai_Addon_PaidListings_IFeatures
{
    public function paidListingsGetFeatureNames();
    public function paidListingsGetFeature($featureName);
}