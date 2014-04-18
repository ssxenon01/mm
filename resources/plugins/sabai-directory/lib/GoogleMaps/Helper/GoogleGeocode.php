<?php
class Sabai_Addon_GoogleMaps_Helper_GoogleGeocode extends Sabai_Helper
{
    /**
     * @param Sabai $application
     * @param string $query
     * @param bool $latlng
     * @throw Sabai_RuntimeException
     */
    public function help(Sabai $application, $query, $isLatLng = false)
    {
        $_query = $isLatLng ? 'latlng=' . urlencode($query) : 'address=' . urlencode($query);
        $ch = curl_init('http://maps.googleapis.com/maps/api/geocode/json?' . $_query . '&sensor=false&language=' . $application->GoogleMaps_Language());
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec($ch);
        $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($result !== false && $http_status == 200) {
            if ($geocode = json_decode($result)) {
                switch ($geocode->status) {
                    case 'OK':
                        $geometry = $geocode->results[0]->geometry;
                        return array(
                            'lat' => $geometry->location->lat,
                            'lng' => $geometry->location->lng,
                            'address' => $geocode->results[0]->formatted_address,
                            'viewport' => array(
                                $geometry->viewport->southwest->lat,
                                $geometry->viewport->southwest->lng, 
                                $geometry->viewport->northeast->lat,
                                $geometry->viewport->northeast->lng,
                            ),
                        );
                    case 'ZERO_RESULTS':
                        require_once dirname(__FILE__) . '/../GeocodeNoResultsException.php';
                        throw new Sabai_Addon_Google_GeocodeNoResultsException($query, 'Google geocoding service did not respond with OK status. Returned status: ' . $geocode->status);
                    case 'OVER_QUERY_LIMIT':
                        require_once dirname(__FILE__) . '/../GeocodeOverQueryLimitException.php';
                        throw new Sabai_Addon_Google_GeocodeOverQueryLimitException($query, 'Google geocoding service did not respond with OK status. Returned status: ' . $geocode->status);
                    case 'REQUEST_DENIED':
                        require_once dirname(__FILE__) . '/../GeocodeRequestDeniedException.php';
                        throw new Sabai_Addon_Google_GeocodeRequestDeniedException($query, 'Google geocoding service did not respond with OK status. Returned status: ' . $geocode->status);
                    case 'INVALID_REQUEST':
                        require_once dirname(__FILE__) . '/../GeocodeInvalidRequestException.php';
                        throw new Sabai_Addon_Google_GeocodeInvalidRequestException($query, 'Google geocoding service did not respond with OK status. Returned status: ' . $geocode->status);
                }
            } else {
                require_once dirname(__FILE__) . '/../GeocodeException.php';
                throw new Sabai_Addon_Google_GeocodeException($query, 'Failed parsing result returned from Google gecoding service.');
            }
        } else {
            require_once dirname(__FILE__) . '/../GeocodeException.php';
            throw new Sabai_Addon_Google_GeocodeException($query, 'Failed requesting Google geocoding service. Returned HTTP status: ' . $http_status);
        }
    }
}
