<?php
class Sabai_Addon_Entity_Helper_RenderField extends Sabai_Helper
{
    /**
     * Renders an entity field
     * @param Sabai $application
     * @param Sabai_Addon_Entity_IEntity $entity
     * @param string|Sabai_Addon_Entity_Model_Field $fieldType
     * @param array|null $fieldSettings
     * @param array|null $fieldValues
     * @param array $options
     */
    public function help(Sabai $application, Sabai_Addon_Entity_IEntity $entity, $fieldType, array $fieldSettings = null, array $fieldValues = null, array $options = array())
    {
        if ($fieldType instanceof Sabai_Addon_Entity_Model_Field) {
            if (!$fieldValues = $entity->getFieldValue($fieldType->getFieldName())) {
                return '';
            }
            $fieldSettings = $fieldType->getFieldSettings();
            $fieldType = $fieldType->getFieldType();
        }
        
        return $this->$fieldType($application, $entity, $fieldSettings, $fieldValues, $options);
    }
    
    protected function string(Sabai $application, Sabai_Addon_Entity_IEntity $entity, array $fieldSettings, array $fieldValues, $options)
    {
        $ret = array_map(array('Sabai', 'h'), $fieldValues);
        if (!isset($options['separator'])) {
            $options['separator'] = ', ';
        } elseif ($options['separator'] === false) {
            return $ret;
        }
        return implode($options['separator'], $ret);
    }
    
    protected function number(Sabai $application, Sabai_Addon_Entity_IEntity $entity, array $fieldSettings, array $fieldValues, $options)
    {
        $ret = array_map(array('Sabai', 'h'), $fieldValues);
        if (!isset($options['separator'])) {
            $options['separator'] = ', ';
        } elseif ($options['separator'] === false) {
            return $ret;
        }
        return implode($options['separator'], $ret);
    }
        
    protected function choice(Sabai $application, Sabai_Addon_Entity_IEntity $entity, array $fieldSettings, array $fieldValues, $options)
    {
        $ret = array();
        foreach ($fieldValues as $value) {
            if (isset($fieldSettings['options']['options'][$value])) {
                $ret[] = Sabai::h($fieldSettings['options']['options'][$value]);
            }
        }
        if (!isset($options['separator'])) {
            $options['separator'] = ', ';
        } elseif ($options['separator'] === false) {
            return $ret;
        }
        return implode($options['separator'], $ret);
    }

    protected function text(Sabai $application, Sabai_Addon_Entity_IEntity $entity, array $fieldSettings, array $fieldValues, $options)
    {
        $ret = array();
        foreach ($fieldValues as $value) {
            $ret[] = '<p>' . Sabai::h($value) . '</p>';
        }
        return implode(PHP_EOL, $ret);
    }
            
    protected function boolean(Sabai $application, Sabai_Addon_Entity_IEntity $entity, array $fieldSettings, array $fieldValues, $options)
    {
        return empty($fieldValues[0]) ? __('No', 'sabai') : __('Yes', 'sabai');
    }    

    protected function user(Sabai $application, Sabai_Addon_Entity_IEntity $entity, array $fieldSettings, array $fieldValues, $options)
    {
        $ret = array();
        foreach ($fieldValues as $value) {
            $ret[] = $application->UserIdentityLinkWithThumbnailSmall($value);
        }
        if (!isset($options['separator'])) {
            $options['separator'] = PHP_EOL;
        } elseif ($options['separator'] === false) {
            return $ret;
        }
        return implode($options['separator'], $ret);
    }
           
    protected function link(Sabai $application, Sabai_Addon_Entity_IEntity $entity, array $fieldSettings, array $fieldValues, $options)
    {
        if (count($fieldValues) > 1) {
            $ret = array('<ul>');
            foreach ($fieldValues as $value) {
                $ret[] = sprintf(
                    '<li><a href="%s"%s%s>%s</a></li>',
                    Sabai::h($value['url']),
                    $fieldSettings['target'] === '_blank' ? ' target="_blank"' : '',
                    empty($fieldSettings['nofollow']) ? '' : ' rel="nofollow"',
                    strlen($value['title']) ? Sabai::h($value['title']) : Sabai::h($value['url'])
                );
            }
            $ret[] = '</ul>';
            return implode(PHP_EOL, $ret);
        }
        
        $value = $fieldValues[0];
        return sprintf(
            '<a href="%s"%s%s>%s</a>',
            Sabai::h($value['url']),
            $fieldSettings['target'] === '_blank' ? ' target="_blank"' : '',
            empty($fieldSettings['nofollow']) ? '' : ' rel="nofollow"',
            strlen($value['title']) ? Sabai::h($value['title']) : Sabai::h($value['url'])
        );
    }
    
    public function __call($fieldType, $args)
    {
        $application = $args[0];
        $field_types = $application->Field_Types();
        $method = $field_types[$fieldType]['addon'] . '_RenderEntityField';
        return $application->$method($args[1], $fieldType, $args[2], $args[3], $args[4]);
    }
}