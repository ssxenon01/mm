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
        $query = $isLatLng ? 'latlng=' . urlencode($query) : 'address=' . urlencode($query);
        $ch = curl_init('http://maps.googleapis.com/maps/api/geocode/json?' . $query . '&sensor=false&language=' . $application->GoogleMaps_Language());
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec($ch);
        $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($result !== false && $http_status == 200) {
            if ($geocode = json_decode($result)) {
                if ($geocode->status === 'OK') {
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
                } else {
                    throw new Sabai_RuntimeException('Google geocoding service did not respond with OK status. Query: '. $query  .'; Returned status: ' . $geocode->status);
                }
            } else {
                throw new Sabai_RuntimeException('Failed parsing result returned from Google gecoding service. Query: '. $query);
            }
        } else {
            throw new Sabai_RuntimeException('Failed requesting Google geocoding service. Query: '. $query  .'; Returned HTTP status: ' . $http_status);
        }
    }
}