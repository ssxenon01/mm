<?php
class Sabai_Addon_Taxonomy_Helper_SelectDropdown extends Sabai_Helper
{
    public function help(Sabai $application, $bundleName, array $options = array())
    {
        $options += array(
            'parent' => 0,
            'class' => '',
            'name' => '',
            'current' => null,
            'default_text' => null,
        );
        $html = array();
        if (is_array($bundleName)) {
            $_html = array();
            $query = $application->Entity_Query('taxonomy')
                ->propertyIsIn('term_entity_bundle_name', array_keys($bundleName))
                ->propertyIs('term_parent', $options['parent'])
                ->sortByProperty('term_title');
            foreach ($query->fetch(0, 0, false) as $term) {
                $_html[$term->getBundleName()][] = $this->_renderOption($term, $options['current']);
            }
            foreach ($bundleName as $bundle_name => $label) {
                if (!empty($_html[$bundle_name])) {
                    $html[] = '<optgroup label="'. Sabai::h($label) . '">' . implode(PHP_EOL, $_html[$bundle_name]) . '</optgroup>';
                }
            }            
        } else {
            $query = $application->Entity_Query('taxonomy')
                ->propertyIs('term_entity_bundle_name', $bundleName)
                ->propertyIs('term_parent', $options['parent'])
                ->sortByProperty('term_title');
            foreach ($query->fetch(0, 0, false) as $term) {
                $html[] = $this->_renderOption($term, $options['current']);
            }
        }
        if (empty($html)) {
            return '';
        }
        if (isset($options['default_text'])) {
            array_unshift($html, '<option>' . Sabai::h($options['default_text']) . '</option>');
        }
        if (isset($options['class'])) {
            array_unshift($html, '<select name="'. $options['name'] .'" class="'. $options['class'] .'">');
        } else {
            array_unshift($html, '<select name="'. $options['name'] .'">');
        }
        $html[] = '</select>';
        
        return implode(PHP_EOL, $html);
    }
    
    protected function _renderOption($term, $current)
    {
        if ($term->getId() == $current) {
            return '<option value="'. $term->getId() .'" selected="selected">' . Sabai::h($term->getTitle()) . '</option>';
        }
        return '<option value="'. $term->getId() .'">' . Sabai::h($term->getTitle()) . '</option>';
    }
}