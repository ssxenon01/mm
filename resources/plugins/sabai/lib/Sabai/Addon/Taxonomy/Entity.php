<?php
class Sabai_Addon_Taxonomy_Entity extends Sabai_Addon_Entity_Entity
{    
    public function __construct($bundleName, $bundleType, $userId, $timestamp, $id, $title, $name, $parent)
    {
        parent::__construct(array(
            'bundle_name' => $bundleName,
            'bundle_type' => $bundleType,
            'user_id' => $userId,
            'timestamp' => $timestamp,
            'id' => $id,
            'title' => $title,
            'name' => $name,
            'parent_id' => $parent,
        ));
    }
    
    public function getType()
    {
        return 'taxonomy';
    }
    
    public function getBundleName()
    {
        return $this->_properties['bundle_name'];
    }
    
    public function getBundleType()
    {
        return $this->_properties['bundle_type'];
    }
    
    public function getAuthorId()
    {
        return $this->_properties['user_id'];
    }
        
    public function getTimestamp()
    {
        return $this->_properties['timestamp'];
    }
    
    public function getId()
    {
        return $this->_properties['id'];
    }

    public function getTitle()
    {
        return $this->_properties['title'];
    }
    
    public function getSlug()
    {
        return $this->_properties['name'];
    }
    
    public function getUrlPath(Sabai_Addon_Entity_Model_Bundle $bundle, $path)
    {
        return $bundle->getPath() . '/' . rawurlencode($this->_properties['name']) . $path;
    }
    
    public function getProperty($name)
    {
        if ($name === 'taxonomy_term_title') return $this->getTitle();
        if ($name === 'taxonomy_term_parent') return $this->_properties['parent_id'];
    }
    
    public function isPropertyModified($name, $value)
    {
        switch ($name) {
            case 'taxonomy_term_title':
                return $value != $this->_properties['title'];
            case 'taxonomy_term_parent':
                return $value != $this->_properties['parent_id'];
            case 'taxonomy_term_name':
                return $value != $this->_properties['name'];
        }
    }
        
    public function getParentId()
    {
        return $this->_properties['parent_id'];
    }
    
    public function getSummary($length = 0, $trimmarker = '...')
    {
        $body = (string)$this->getSingleFieldValue('taxonomy_body', 'filtered_value');
        if (!strlen($body)) {
            return '';
        }
        $body = strip_tags(strtr($body, array("\r" => '', "\n" => ' ')));
        
        return empty($length) ? $body : mb_strimwidth($body, 0, $length, $trimmarker);
    }
}