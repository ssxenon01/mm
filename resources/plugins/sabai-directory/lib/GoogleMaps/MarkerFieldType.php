<?php
class Sabai_Addon_GoogleMaps_MarkerFieldType implements Sabai_Addon_Field_IType
{
    private $_addon;

    public function __construct(Sabai_Addon_GoogleMaps $addon)
    {
        $this->_addon = $addon;
    }

    public function fieldTypeGetInfo($key = null)
    {
        $info = array(
            'label' => __('Google Map', 'sabai-directory'),
            'default_widget' => 'googlemaps_marker',
            'creatable' => false,
        );

        return isset($key) ? @$info[$key] : $info;
    }

    public function fieldTypeGetSettingsForm(array $settings, array $parents = array())
    {
        return array();
    }

    public function fieldTypeGetSchema(array $settings)
    {
        return array(
            'columns' => array(
                'address' => array(
                    'type' => Sabai_Addon_Field::COLUMN_TYPE_VARCHAR,
                    'length' => 255,
                    'notnull' => true,
                    'was' => 'address',
                    'default' => '',
                ),
                'zoom' => array(
                    'type' => Sabai_Addon_Field::COLUMN_TYPE_INTEGER,
                    'unsigned' => true,
                    'notnull' => true,
                    'length' => 2,
                    'was' => 'zoom',
                    'default' => 0,
                ),
                'lat' => array(
                    'type' => Sabai_Addon_Field::COLUMN_TYPE_DECIMAL,
                    'length' => 9,
                    'scale' => 6,
                    'notnull' => true,
                    'unsigned' => false,
                    'was' => 'lat',
                    'default' => 0,
                ),
                'lng' => array(
                    'type' => Sabai_Addon_Field::COLUMN_TYPE_DECIMAL,
                    'length' => 9,
                    'scale' => 6,
                    'notnull' => true,
                    'unsigned' => false,
                    'was' => 'lng',
                    'default' => 0,
                ),
            ),
            'indexes' => array(
                'lat_lng' => array(
                    'fields' => array(
                        'lat' => array('sorting' => 'ascending'),
                        'lng' => array('sorting' => 'ascending'),
                    ),
                    'was' => 'lat_lng',
                ),
            ),
        );
    }

    public function fieldTypeOnSave(Sabai_Addon_Field_IField $field, array $values)
    {
        $ret = array();
        foreach ($values as $weight => $value) {
            if (!is_array($value)
                || strlen((string)$value['lat']) === 0
                || strlen((string)$value['lng']) === 0
            ) continue;

            $ret[] = $value;
        }

        return $ret;
    }

    public function fieldTypeOnLoad(Sabai_Addon_Field_IField $field, array &$values)
    {

    }
    
    public function fieldTypeIsModified($field, $valueToSave, $currentLoadedValue)
    {
        return $valueToSave !== $currentLoadedValue;
    }
}