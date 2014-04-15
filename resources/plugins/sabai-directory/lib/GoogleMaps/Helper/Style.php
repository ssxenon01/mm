<?php
class Sabai_Addon_GoogleMaps_Helper_Style extends Sabai_Helper
{
    /**
     * @param Sabai $application
     * @param string $style
     */
    public function help(Sabai $application, $style = false)
    {
        if ($style === false) {
            $styles = array('Red', 'Blue', 'Greyscale', 'Night', 'Sepia', 'Chilled', 'Mixed');
            return array_combine($styles, $styles);
        }
        
        switch ($style) {
            case 'Red':
                return array(
                    array(
                        'featureType' => 'all',
                        'stylers' => array(array('hue' => '#ff0000')),
                    ),
                );
            case 'Blue':
                return array(
                    array(
                        'featureType' => 'all',
                        'stylers' => array(array('invert_lightness' => 'true'), array('hue' => '#0000b0'), array('saturation' => -30)),
                    ),
                );
            case 'Greyscale':
                return array(
                    array(
                        'featureType' => 'all',
                        'stylers' => array(array('gamma' => 0.50), array('saturation' => -100)),
                    ),
                );
            case 'Night':
                return array(
                    array(
                        'featureType' => 'all',
                        'stylers' => array(array('invert_lightness' => 'true')),
                    ),
                );
            case 'Sepia':
                return array(
                    array(
                        'featureType' => 'water',
                        'elementType' => 'all',
                        'stylers' => array(array('hue' => '#ff9100'), array('lightness' => 52)),
                    ),
                    array(
                        'featureType' => 'water',
                        'elementType' => 'labels',
                        'stylers' => array(array('visibility' => 'off')),
                    ),
                    array(
                        'featureType' => 'road',
                        'elementType' => 'labels',
                        'stylers' => array(array('visibility' => 'off')),
                    ),
                    array(
                        'featureType' => 'road.highway',
                        'elementType' => 'geometry',
                        'stylers' => array(array('saturation' => -100)),
                    ),
                    array(
                        'featureType' => 'road.arterial',
                        'elementType' => 'geometry',
                        'stylers' => array(array('saturation' => -100)),
                    ),
                    array(
                        'featureType' => 'road.local',
                        'elementType' => 'geometry',
                        'stylers' => array(array('lightness' => -27)),
                    ),
                    array(
                        'featureType' => 'landscape',
                        'elementType' => 'all',
                        'stylers' => array(array('hue' => '#ffa200'), array('lightness' => -20), array('visibility' => 'off')),
                    ),
                    array(
                        'featureType' => 'administrative',
                        'elementType' => 'labels',
                        'stylers' => array(array('hue' => '#1100ff'), array('saturation' => -100), array('lightness' => -18)),
                    ),
                    array(
                        'featureType' => 'administrative',
                        'elementType' => 'geometry',
                        'stylers' => array(array('visibility' => 'simplified')),
                    ),
                    array(
                        'featureType' => 'poi',
                        'elementType' => 'all',
                        'stylers' => array(array('visibility' => 'off'), array('lightness' => -18)),
                    ),
                    array(
                        'featureType' => 'transit',
                        'elementType' => 'all',
                        'stylers' => array(array('visibility' => 'off')),
                    ),
                );
            case 'Chilled':
                return array(
                    array(
                        'featureType' => 'road',
                        'elementType' => 'geometry',
                        'stylers' => array(array('visibility' => 'simplified')),
                    ),
                    array(
                        'featureType' => 'road.arterial',
                        'stylers' => array(array('lightness' => 0), array('hue' => 149), array('saturation' => -78)),
                    ),
                    array(
                        'featureType' => 'road.highway',
                        'stylers' => array(array('lightness' => 2.8), array('hue' => -31), array('saturation' => -40)),
                    ),
                    array(
                        'featureType' => 'poi',
                        'elementType' => 'label',
                        'stylers' => array(array('visibility' => 'off')),
                    ),
                    array(
                        'featureType' => 'landscape',
                        'stylers' => array(array('lightness' => -1.1), array('hue' => 163), array('saturation' => -26)),
                    ),
                    array(
                        'featureType' => 'transit',
                        'stylers' => array(array('visibility' => 'off')),
                    ),
                    array(
                        'featureType' => 'water',
                        'stylers' => array(array('lightness' => -38.57), array('hue' => 3), array('saturation' => -24.24)),
                    ),
                );
            case 'Mixed':
                return array(
                    array(
                        'featureType' => 'landscape',
                        'stylers' => array(array('hue' => '#00dd00')),
                    ),
                    array(
                        'featureType' => 'road',
                        'stylers' => array(array('hue' => '#dd0000')),
                    ),
                    array(
                        'featureType' => 'water',
                        'stylers' => array(array('hue' => '#000040')),
                    ),
                    array(
                        'featureType' => 'poi.park',
                        'stylers' => array(array('visibility' => 'off')),
                    ),
                    array(
                        'featureType' => 'road.arterial',
                        'stylers' => array(array('hue' => '#ffff00')),
                    ),
                    array(
                        'featureType' => 'road.local',
                        'stylers' => array(array('visibility' => 'off')),
                    ),
                );
                
            default:
                return array();
        }
    }
}