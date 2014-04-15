<?php
abstract class Sabai_Addon_Entity_GuestAuthorFieldType implements Sabai_Addon_Field_IType
{
    protected $_addon, $_entityType, $_defaultWidget, $_info;

    public function __construct(Sabai_Addon $addon, $entityType, $defaultWidget)
    {
        $this->_addon = $addon;
        $this->_entityType = $entityType;
        $this->_defaultWidget = $defaultWidget;
    }

    public function fieldTypeGetInfo($key = null)
    {
        if (!isset($this->_info)) {
            $this->_info = array(
                'label' => __('Guest Author', 'sabai'),
                'entity_types' => array($this->_entityType),
                'default_widget' => $this->_defaultWidget,
                'creatable' => false,
            );
        }
        return isset($key) ? @$this->_info[$key] : $this->_info;
    }

    public function fieldTypeGetSettingsForm(array $settings, array $parents = array())
    {

    }

    public function fieldTypeGetSchema(array $settings)
    {
        return array(
            'columns' => array(
                'email' => array(
                    'type' => Sabai_Addon_Field::COLUMN_TYPE_VARCHAR,
                    'notnull' => true,
                    'length' => 100,
                    'was' => 'email',
                    'default' => '',
                ),
                'name' => array(
                    'type' => Sabai_Addon_Field::COLUMN_TYPE_VARCHAR,
                    'notnull' => true,
                    'length' => 255,
                    'was' => 'name',
                    'default' => '',
                ),
                'url' => array(
                    'type' => Sabai_Addon_Field::COLUMN_TYPE_VARCHAR,
                    'notnull' => true,
                    'length' => 255,
                    'was' => 'url',
                    'default' => '',
                ),
                'ip' => array(
                    'type' => Sabai_Addon_Field::COLUMN_TYPE_VARCHAR,
                    'notnull' => true,
                    'length' => 100,
                    'was' => 'ip',
                    'default' => '',
                ),
                'user_agent' => array(
                    'type' => Sabai_Addon_Field::COLUMN_TYPE_VARCHAR,
                    'notnull' => true,
                    'length' => 255,
                    'was' => 'user_agent',
                    'default' => '',
                ),
                'guid' => array(
                    'type' => Sabai_Addon_Field::COLUMN_TYPE_VARCHAR,
                    'notnull' => true,
                    'length' => 23,
                    'was' => 'guid',
                    'default' => '',
                ),
            )
        );
    }

    public function fieldTypeOnSave(Sabai_Addon_Field_IField $field, array $values, array $currentValues = null)
    {
        if (is_null($currentValues)
            && !$this->_addon->getApplication()->getUser()->isAnonymous()
        ) {
            return false;
        }
        $ret = array();
        foreach ($values as $key => $value) {
            if (is_array($value)) {
                $value['guid'] = empty($currentValues[$key]['guid']) ? uniqid('', true) : $currentValues[$key]['guid'];
                $value['ip'] = empty($currentValues[$key]['ip']) ? (empty($_SERVER['HTTP_CLIENT_IP']) ? (string)@$_SERVER['REMOTE_ADDR'] : $_SERVER['HTTP_CLIENT_IP']) : $currentValues[$key]['ip'];
                $value['user_agent'] = empty($currentValues[$key]['user_agent']) ? $_SERVER['HTTP_USER_AGENT'] : $currentValues[$key]['user_agent'];
                if (!empty($currentValues[$key])) {
                    $value += $currentValues[$key];
                }
                $ret[] = $value;
            } elseif ($value === false) { // deleting explicitly?
                $ret[] = false; 
            }
        }
        return empty($ret) ? false : $ret;
    }

    public function fieldTypeOnLoad(Sabai_Addon_Field_IField $field, array &$values)
    {

    }
    
    public function fieldTypeIsModified($field, $valueToSave, $currentLoadedValue)
    {
        return $valueToSave !== $currentLoadedValue;
    }
}