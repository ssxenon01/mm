<?php
class Sabai_Addon_GoogleMaps_Helper_Geocode extends Sabai_Helper
{
    /**
     * @param Sabai $application
     * @param string $query
     * @param bool $latlng
     * @throw Sabai_RuntimeException
     */
    public function help(Sabai $application, $query, $latlng = false)
    {
        $query = strtolower(trim($query));
        $hash = md5($query);
        $model = $application->getModel('Geocode', 'GoogleMaps');
        if (!$geocode = $model->hash_is($hash)->fetchOne()) {
            // No cache, so query Google geocoding service
            $result = $application->GoogleMaps_GoogleGeocode($query, $latlng);
            // Create new geocode cache entry
            $geocode = $model->create('Geocode')
                ->markNew()
                ->set('query', $query)
                ->set('hash', $hash)
                ->set('lat', $result['lat'])
                ->set('lng', $result['lng'])
                ->set('address', $result['address'])
                ->set('hits', 1)
                ->set('viewport', implode(',', $result['viewport']))
                ->commit();
        } else {
            // Increment hit count with 30% probability to reduce load
            if (mt_rand(1, 10) <= 3) {
                $geocode->set('hits', $geocode->hits + 1)->commit();
            }
        }
        return $geocode;
    }
}
