<?php
class Sabai_Addon_Directory_FieldType implements Sabai_Addon_Field_IType
{
    private $_addon, $_name, $_info;

    public function __construct(Sabai_Addon_Directory $addon, $name)
    {
        $this->_addon = $addon;
        $this->_name = $name;
    }

    public function fieldTypeGetInfo($key = null)
    {
        if (!isset($this->_info)) {
            $this->_info = array(
                'default_settings' => array(),
                'creatable' => false,
            );
            switch ($this->_name) {
                case 'directory_rating':
                    $this->_info += array(
                        'label' => 'Listing Rating',
                    );
                    break;
                case 'directory_contact':
                    $this->_info += array(
                        'label' => 'Contact Info',
                    );
                    break;
                case 'directory_social':
                    $this->_info += array(
                        'label' => 'Social Accounts',
                    );
                    break;
                case 'directory_claim':
                    $this->_info += array(
                        'label' => 'Listing Claim',
                        'editable' => false,
                    );
                    break;
                case 'directory_photo':
                    $this->_info += array(
                        'label' => 'Listing Photo',
                    );
                    break;
                default:
                    return;
            }
        }

        return isset($key) ? @$this->_info[$key] : $this->_info;
    }

    public function fieldTypeGetSettingsForm(array $settings, array $parents = array())
    {
        return array();
    }

    public function fieldTypeGetSchema(array $settings)
    {
        switch ($this->_name) {
            case 'directory_rating':
                return array(
                    'columns' => array(
                        'value' => array(
                            'type' => Sabai_Addon_Field::COLUMN_TYPE_DECIMAL,
                            'notnull' => true,
                            'length' => 5,
                            'scale' => 2,
                            'unsigned' => true,
                            'was' => 'value',
                            'default' => 0,
                        ),
                    ),
                    'indexes' => array(
                        'value' => array(
                            'fields' => array('value' => array('sorting' => 'ascending')),
                            'was' => 'value',
                        ),
                    ),
                );
            case 'directory_contact':
                return array(
                    'columns' => array(
                        'phone' => array(
                            'type' => Sabai_Addon_Field::COLUMN_TYPE_VARCHAR,
                            'notnull' => true,
                            'length' => 50,
                            'was' => 'phone',
                            'default' => '',
                        ),
                        'mobile' => array(
                            'type' => Sabai_Addon_Field::COLUMN_TYPE_VARCHAR,
                            'notnull' => true,
                            'length' => 50,
                            'was' => 'mobile',
                            'default' => '',
                        ),
                        'fax' => array(
                            'type' => Sabai_Addon_Field::COLUMN_TYPE_VARCHAR,
                            'notnull' => true,
                            'length' => 50,
                            'was' => 'fax',
                            'default' => '',
                        ),
                        'email' => array(
                            'type' => Sabai_Addon_Field::COLUMN_TYPE_VARCHAR,
                            'notnull' => true,
                            'length' => 100,
                            'was' => 'email',
                            'default' => '',
                        ),
                        'website' => array(
                            'type' => Sabai_Addon_Field::COLUMN_TYPE_VARCHAR,
                            'notnull' => true,
                            'length' => 255,
                            'was' => 'website',
                            'default' => '',
                        ),
                    ),
                );
            case 'directory_social':
                return array(
                    'columns' => array(
                        'twitter' => array(
                            'type' => Sabai_Addon_Field::COLUMN_TYPE_VARCHAR,
                            'notnull' => true,
                            'length' => 20,
                            'was' => 'twitter',
                            'default' => '',
                        ),
                        'facebook' => array(
                            'type' => Sabai_Addon_Field::COLUMN_TYPE_VARCHAR,
                            'notnull' => true,
                            'length' => 255,
                            'was' => 'facebook',
                            'default' => '',
                        ),
                        'googleplus' => array(
                            'type' => Sabai_Addon_Field::COLUMN_TYPE_VARCHAR,
                            'notnull' => true,
                            'length' => 255,
                            'was' => 'googleplus',
                            'default' => '',
                        ),
                    ),
                );
            case 'directory_claim':
                return array(
                    'columns' => array(
                        'claimed_by' => array(
                            'type' => Sabai_Addon_Field::COLUMN_TYPE_INTEGER,
                            'notnull' => true,
                            'unsigned' => true,
                            'was' => 'claimed_by',
                            'default' => 0,
                        ),
                        'claimed_at' => array(
                            'type' => Sabai_Addon_Field::COLUMN_TYPE_INTEGER,
                            'notnull' => true,
                            'unsigned' => true,
                            'was' => 'claimed_at',
                            'default' => 0,
                        ),
                        'expires_at' => array(
                            'type' => Sabai_Addon_Field::COLUMN_TYPE_INTEGER,
                            'notnull' => true,
                            'unsigned' => true,
                            'was' => 'expires_at',
                            'default' => 0,
                        ),
                    ),
                    'indexes' => array(
                        'claimed_by' => array(
                            'fields' => array(
                                'claimed_by' => array('sorting' => 'ascending'),
                            ),
                            'was' => 'claimed_by',
                        ),
                        'expires_at' => array(
                            'fields' => array(
                                'expires_at' => array('sorting' => 'ascending'),
                            ),
                            'was' => 'expires_at',
                        ),
                    ),
                );
                
            case 'directory_photo':
                return array(
                    'columns' => array(
                        'official' => array(
                            'type' => Sabai_Addon_Field::COLUMN_TYPE_INTEGER,
                            'notnull' => true,
                            'unsigned' => true,
                            'length' => 1,
                            'was' => 'official',
                            'default' => 0,
                        ),
                        'display_order' => array(
                            'type' => Sabai_Addon_Field::COLUMN_TYPE_INTEGER,
                            'notnull' => true,
                            'unsigned' => true,
                            'length' => 2,
                            'was' => 'display_order',
                            'default' => 0,
                        ),
                    ),
                    'indexes' => array(
                        'official_display_order' => array(
                            'fields' => array(
                                'official' => array('sorting' => 'ascending'),
                                'display_order' => array('sorting' => 'ascending'),
                            ),
                            'was' => 'official_display_order',
                        ),
                    ),
                );
        }
        
    }

    public function fieldTypeOnSave(Sabai_Addon_Field_IField $field, array $values, array $currentValues = null)
    {
        switch ($this->_name) {
            case 'directory_rating':
                $ret = array();
                foreach ($values as $weight => $value) {
                    $value = (float)$value;
                    if ($value >= 0 && $value <= 5) {
                        $ret[]['value'] = $value;
                    }
                }
                return $ret;
            case 'directory_contact':
                $ret = array();
                foreach ($values as $weight => $value) {
                    if (!is_array($value)) {
                        continue;
                    }
                    if (strlen($value['phone']) || strlen($value['mobile']) || strlen($value['fax']) || strlen($value['email']) || strlen($value['website'])) {
                        $ret[] = $value;
                    }
                }
                return $ret;
            case 'directory_social':
                $ret = array();
                foreach ($values as $weight => $value) {
                    if (!is_array($value)) {
                        continue;
                    }
                    if (strlen($value['twitter']) || strlen($value['facebook']) || strlen($value['googleplus'])) {
                        $ret[] = $value;
                    }
                }
                return $ret;
            case 'directory_claim':
                $ret = array();
                if (!empty($currentValues)) {
                    $current_values = array();
                    foreach ($currentValues as $current_value) {
                        $current_values[$current_value['claimed_by']] = $current_value;
                    }
                }
                foreach ($values as $value) {
                    if (!is_array($value) || empty($value['claimed_by'])) {
                        continue;
                    }
                    if (empty($value['claimed_at'])) { // may be empty on renewal, for example
                        $value['claimed_at'] = !empty($current_values[$value['claimed_by']]['claimed_at']) ? $currentValues[$value['claimed_by']]['claimed_at'] : time();   
                    }
                    $ret[$value['claimed_by']] = $value;
                }
                return empty($ret) ? $ret : array_values($ret); // re-index array
            case 'directory_photo':
                $ret = array();
                foreach ($values as $weight => $value) {
                    if (!is_array($value) || empty($value['official'])) {
                        continue;
                    }
                    $ret[] = $value;
                }
                return $ret;
        }
    }

    public function fieldTypeOnLoad(Sabai_Addon_Field_IField $field, array &$values)
    {
        switch ($this->_name) {
            case 'directory_rating':
                foreach ($values as $key => $value) {
                    $values[$key] = $value['value'];
                }
                break;
            case 'directory_claim':
                $new_values = array();
                foreach ($values as $key => $value) {
                    $new_values[$value['claimed_by']] = $value;
                }
                $values = $new_values;
                break;
        }
    }
    
    public function fieldTypeIsModified($field, $valueToSave, $currentLoadedValue)
    {   
        switch ($this->_name) {
            case 'directory_claim':
                $current = $new = array();
                foreach ($currentLoadedValue as $user_id => $value) {
                    $value['claimed_by'] = $user_id;
                    $current[] = $value;
                }
                return $current !== $valueToSave;
            default:
                return $valueToSave !== $currentLoadedValue;
        }
    }
}