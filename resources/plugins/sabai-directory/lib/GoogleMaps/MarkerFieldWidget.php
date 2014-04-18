<?php
class Sabai_Addon_GoogleMaps_MarkerFieldWidget implements Sabai_Addon_Field_IWidget
{
    private $_addon;

    public function __construct(Sabai_Addon_GoogleMaps $addon)
    {
        $this->_addon = $addon;
    }

    public function fieldWidgetGetInfo($key = null)
    {
        $info = array(
            'label' => __('Google Map', 'sabai-directory'),
            'field_types' => array('googlemaps_marker'),
            'default_settings' => array(
                'map_height' => 400,
                'center_latitude' => 40.69847,
                'center_longitude' => -73.95144,
                'zoom' => 10,
                'icon' => '',
                'style' => '',
            ),
            'enable_edit_disabled' => true,
        );

        return isset($key) ? @$info[$key] : $info;
    }

    public function fieldWidgetGetSettingsForm($fieldType, array $fieldSettings, array $settings, array $parents = array())
    {
        return array(
            'map_height' => array(
                '#type' => 'textfield',
                '#size' => 4,
                '#maxlength' => 3,
                '#field_suffix' => 'px',
                '#title' => __('Map height', 'sabai-directory'),
                '#description' => __('Enter the height of map in pixels.', 'sabai-directory'),
                '#default_value' => $settings['map_height'],
                '#numeric' => true,
            ),
            'center_latitude' => array(
                '#type' => 'textfield',
                '#size' => 15,
                '#maxlength' => 9,
                '#title' => __('Default latitude', 'sabai-directory'),
                '#description' => __('Enter the latitude of the default map location in decimals.', 'sabai-directory'),
                '#default_value' => $settings['center_latitude'],
                '#regex' => '/^-?([1-8]?[1-9]|[1-9]0)\.{1}\d{1,5}/',
                '#numeric' => true,
            ),
            'center_longitude' => array(
                '#type' => 'textfield',
                '#size' => 15,
                '#maxlength' => 10,
                '#title' => __('Default longitude', 'sabai-directory'),
                '#description' => __('Enter the longitude of the default map location in decimals.', 'sabai-directory'),
                '#default_value' => $settings['center_longitude'],
                '#regex' => '/^-?((([1]?[0-7][0-9]|[1-9]?[0-9])\.{1}\d{1,6}$)|[1]?[1-8][0]\.{1}0{1,6}$)/',
                '#numeric' => true,
            ),
            'zoom' => array(
                '#type' => 'textfield',
                '#size' => 3,
                '#maxlength' => 2,
                '#title' => __('Default zoom level', 'sabai-directory'),
                '#default_value' => $settings['zoom'],
                '#integer' => true,
                '#min_value' => 0,
            ),
            'icon' => array(
                '#type' => 'textfield',
                '#url' => true,
                '#title' => __('Custom marker icon URL', 'sabai-directory'),
                '#default_value' => $settings['icon'],
                '#size' => 60,
            ),
            'style' => array(
                '#type' => 'select',
                '#options' => array('' => __('Default style', 'sabai-directory')) + $this->_addon->getApplication()->GoogleMaps_Style(),
                '#title' => __('Map style', 'sabai-directory'),
                '#default_value' => $settings['style'],
            ),
        );
    }

    public function fieldWidgetGetForm(Sabai_Addon_Field_IField $field, array $settings, $value = null, array $parents = array())
    {
        return array(
            '#type' => 'googlemaps_marker',
            '#map_height' => $settings['map_height'],
            '#center_latitude' => $settings['center_latitude'],
            '#center_longitude' => $settings['center_longitude'],
            '#zoom' => $settings['zoom'],
            '#marker_icon' => $settings['icon'],
            '#map_style' => $settings['style'],
            '#default_value' => $value,
        );
    }
    
    public function fieldWidgetGetPreview(Sabai_Addon_Field_IField $field, array $settings)
    {
        $marker = $styles = array();
        if (!empty($settings['icon'])) {
            $marker[] = 'icon:' . $settings['icon'];
        }
        $marker[] = $settings['center_latitude'] . ',' . $settings['center_longitude'];
        if (!empty($settings['style'])
            && ($style_settings = $this->_addon->getApplication()->GoogleMaps_Style($settings['style']))
        ) {
            foreach ($style_settings as $style_setting) {
                $style = array();
                $style[] = 'feature:' . $style_setting['featureType'];
                if (isset($style_setting['elementType'])) {
                    $style[] = 'element:' . $style_setting['elementType'];
                }
                foreach ($style_setting['stylers'] as $styler) {
                    foreach ($styler as $styler_k => $styler_v) {
                        if ($styler_k === 'hue') {
                            $styler_v = str_replace('#', '0x', $styler_v);
                        } elseif ($styler_k === 'inverse_lightness') {
                            $styler_v = $styler_v ? 'true' : 'false';
                        }
                        $style[] = $styler_k . ':' . $styler_v;
                    }
                }
                $styles[] = 'style=' . rawurlencode(implode('|', $style));
            }
        }
        return sprintf(
            '<div><input type="text" disabled="disabled" style="width:100%%;" /></div><div><img src="http://maps.googleapis.com/maps/api/staticmap?center=%1$f,%2$f&zoom=%3$d&size=600x%4$d&sensor=false&markers=%5$s&%6$s" style="width:100%%;" /></div>',
            $settings['center_latitude'],
            $settings['center_longitude'],
            $settings['zoom'],
            $settings['map_height'],
            rawurlencode(implode('|', $marker)),
            implode('&', $styles)
        );
    }

    public function fieldWidgetGetEditDefaultValueForm($fieldType, array $fieldSettings, array $settings, array $parents = array())
    {

    }
}