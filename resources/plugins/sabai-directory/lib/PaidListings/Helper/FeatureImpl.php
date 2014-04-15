<?php
class Sabai_Addon_PaidListings_Helper_FeatureImpl extends Sabai_Helper
{
    private $_handlers, $_impls = array();

    /**
     * Gets an implementation of Sabai_Addon_PaidListings_IFeature interface for a given feature type
     * @param Sabai $application
     * @param string $feature
     * @param bool $useCache
     */
    public function help(Sabai $application, $feature, $useCache = true)
    {
        if (!isset($this->_impls[$feature])) {
            // Feature handlers initialized?
            if (!isset($this->_handlers) || !$useCache) {
                $this->_loadHandlers($application, $useCache);
            }
            // Valid feature type?
            if (!isset($this->_handlers[$feature])
                || (!$feature_plugin = $application->getAddon($this->_handlers[$feature]))
            ) {
                throw new Sabai_UnexpectedValueException(sprintf(__('Invalid feature type: %s', 'sabai-directory'), $feature));
            }
            $this->_impls[$feature] = $feature_plugin->paidListingsGetFeature($feature);
        }

        return $this->_impls[$feature];
    }

    private function _loadHandlers(Sabai $application, $useCache)
    {
        $this->_handlers = array();
        foreach ($application->getModel('Feature', 'PaidListings')->fetch() as $feature) {
            $this->_handlers[$feature->name] = $feature->addon;
        }
    }
}