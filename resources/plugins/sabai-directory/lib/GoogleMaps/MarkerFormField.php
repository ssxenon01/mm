<?php
class Sabai_Addon_GoogleMaps_MarkerFormField implements Sabai_Addon_Form_IField
{
    private $_addon;
    private static $_elements = array();

    public function __construct(Sabai_Addon_GoogleMaps $addon)
    {
        $this->_addon = $addon;
    }

    public function formFieldGetFormElement($name, array &$data, Sabai_Addon_Form_Form $form)
    {
        if (!isset(self::$_elements[$form->settings['#id']])) {
            self::$_elements[$form->settings['#id']] = array();
        }
        $ele_map_id = $form->getFieldId($name);
        $data = array(
            '#tree' => true,
            '#children' => array(
                0 => array(
                    'address' => array(
                        '#type' => 'textfield',
                        '#description' => '<a class="sabai-btn sabai-btn-small" href="#" id="' . $ele_map_id . '-search"><i class="sabai-icon-search"></i> ' . __('Find location on map', 'sabai-directory') . '</a> <a class="sabai-btn sabai-btn-small" href="#" id="' . $ele_map_id . '-fetch"><i class="sabai-icon-arrow-up"></i> ' . __('Get address from map', 'sabai-directory') . '</a>',
                        '#default_value' => @$data['#default_value']['address'],
                        '#attributes' => array('id' => $ele_map_id . '-addr'),
                        '#required' => @$data['#required'],
                    ) + $form->defaultElementSettings(),
                    'map' => array(
                        '#type' => 'markup',
                        '#markup' => sprintf(
                            '<div id="%1$s" style="height:%2$dpx;" class="sabai-googlemaps-map"></div>',
                            $ele_map_id,
                            isset($data['#map_height']) ? Sabai::h($data['#map_height']) : 300
                        ),
                    ) + $form->defaultElementSettings(),
                    'zoom' => array(
                        '#type' => 'hidden',
                        '#default_value' => @$data['#default_value']['zoom'],
                        '#attributes' => array('id' => $ele_map_id . '-zoom'),
                    ) + $form->defaultElementSettings(),
                    'lat' => array(
                        '#type' => 'hidden',
                        '#default_value' => @$data['#default_value']['lat'],
                        '#attributes' => array('id' => $ele_map_id . '-lat'),
                    ) + $form->defaultElementSettings(),
                    'lng' => array(
                        '#type' => 'hidden',
                        '#default_value' => @$data['#default_value']['lng'],
                        '#attributes' => array('id' => $ele_map_id . '-lng'),
                    ) + $form->defaultElementSettings(),
                )
            ),
        ) + $data + $form->defaultElementSettings();
        $data['#class'] .= ' sabai-form-group';

        $map = array();

        $map['path'] = isset($data['#default_value']['path']) ? $data['#default_value']['path'] : null;
        if (!empty($data['#default_value']['lat']) && !empty($data['#default_value']['lng'])) {
            $map['centerLatitude'] = $map['latitude'] = str_replace(',', '.', floatval($data['#default_value']['lat']));
            $map['centerLongitude'] = $map['longitude'] = str_replace(',', '.', floatval($data['#default_value']['lng']));
        } else {
            if (isset($data['#center_longitude'])) {
                $map['centerLongitude'] = str_replace(',', '.', floatval($data['#center_longitude']));
            }
            if (isset($data['#center_latitude'])) {
                $map['centerLatitude'] = str_replace(',', '.', floatval($data['#center_latitude']));
            }
            $map['latitude'] = $map['longitude'] = 'null';
        }
        if (!empty($data['#default_value']['zoom'])) {
            $map['zoom'] = $data['#default_value']['zoom'];
        } else {
            $map['zoom'] = isset($data['#zoom']) ? intval($data['#zoom']) : 10;
        }
        $map['mapTypeControl'] = !isset($data['#mapTypeControl']) || $data['#mapTypeControl'];
        $map['icon'] = !empty($data['#marker_icon']) ? $data['#marker_icon'] : null;
        $map['style'] = !empty($data['#map_style']) ? $data['#map_style'] : null;

        // Register pre render callback if this is the first map element
        if (empty(self::$_elements[$form->settings['#id']])) {
            $form->settings['#pre_render'][] = array($this, 'preRenderCallback');
        }

        self::$_elements[$form->settings['#id']][$ele_map_id] = $map;

        return $form->createFieldset($name, $data);
    }

    public function formFieldOnSubmitForm($name, &$value, array &$data, Sabai_Addon_Form_Form $form)
    {
        $value['address'] = $this->_addon->getApplication()->Trim($value['address']);
        if ($form->isFieldRequired($data)) {
            if (strlen($value['address']) === 0) {
                $form->setError(isset($data['#required_error_message']) ? $data['#required_error_message'] : __('Please fill out this field.', 'sabai-directory'), $name . '[address]');
            } else {
                if (empty($value['lat']) || empty($value['lng'])) {
                    $form->setError(isset($data['#required_error_message']) ? $data['#required_error_message'] : __('Please select a valid location on map.', 'sabai-directory'), $name . '[address]');
                }
            }
        }
    }

    public function formFieldOnCleanupForm($name, array $data, Sabai_Addon_Form_Form $form)
    {

    }

    public function formFieldOnRenderForm($name, array $data, Sabai_Addon_Form_Form $form)
    {
        $form->renderElement($data);
        $form->renderChildElements($name, $data);
    }

    public function preRenderCallback($form)
    {
        if (empty(self::$_elements[$form->settings['#id']])) return;

        $js = array();
        foreach (self::$_elements[$form->settings['#id']] as $map_id => $map) {
            $js[] = sprintf('(function($) {
    SABAI.GoogleMaps.markerMap("%1$s", %2$s, %3$s, %4$s, %5$s, %6$d, %7$s, %8$s, %9$s);
    SABAI.GoogleMaps.autocomplete("#%1$s-addr");
})(jQuery);',
                $map_id,
                $map['centerLatitude'],
                $map['centerLongitude'],
                $map['latitude'],
                $map['longitude'],
                $map['zoom'],
                !empty($map['icon']) ? "'" . Sabai::h($map['icon']) . "'" : 'null',
                empty($map['style']) ? 'null' : json_encode($this->_addon->getApplication()->GoogleMaps_Style($map['style'])),
                json_encode(array('scrollwheel' => false))
            );
        }

        $public_url = $this->_addon->getApplication()->getPlatform()->getAssetsUrl('sabai-directory');
        $form->addJs(sprintf('google.load("maps", "3", {other_params:"sensor=false&libraries=drawing,places&language=%s", callback:function () {
    $LAB.script("%s")
        .script("%s")
        .script("%s").wait(function(){
            %s
})}});',
            $this->_addon->getApplication()->GoogleMaps_Language(),
            $public_url . '/js/sabai-googlemaps-markermap.js',
            $public_url . '/js/jquery.fitmaps.js',
            $public_url . '/js/sabai-googlemaps-autocomplete.js',
            implode(PHP_EOL, $js)
        ));
    }
}
