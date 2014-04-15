<?php
class Sabai_Addon_Content_Entity extends Sabai_Addon_Entity_Entity
{    
    public function __construct($bundleName, $bundleType, $author, $timestamp, $id, $title, $status, $views, $slug)
    {
        parent::__construct(array(
            'bundle_name' => $bundleName,
            'bundle_type' => $bundleType,
            'author' => $author,
            'published' => $timestamp,
            'id' => $id,
            'title' => $title,
            'status' => $status,
            'views' => $views,
            'slug' => $slug,
        ));
    }
    
    public function getType()
    {
        return 'content';
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
        return $this->_properties['author']->id;
    }

    public function getAuthor()
    {
        return $this->_properties['author'];
    }
        
    public function getTimestamp()
    {
        return $this->_properties['published'];
    }
    
    public function getId()
    {
        return $this->_properties['id'];
    }

    public function getTitle()
    {
        return $this->_properties['title'];
    }
    
    public function getStatus()
    {
        return $this->_properties['status'];
    }
        
    public function getSlug()
    {
        return $this->_properties['slug'];
    }
    
    public function getViews()
    {
        return $this->_properties['views'];
    }
    
    public function getUrlPath(Sabai_Addon_Entity_Model_Bundle $bundle, $path)
    {
        return isset($bundle->info['permalink_path'])
            ? $bundle->info['permalink_path'] . '/' . rawurlencode($this->_properties['slug']) . $path
            : $bundle->getPath() . '/' . $this->_properties['id'] . $path;
    }

    public function getProperty($name)
    {
        switch ($name) {
            case 'content_post_title': return $this->_properties['title'];
            case 'content_post_published': return $this->_properties['published'];
            case 'content_post_status': return $this->_properties['status'];
            case 'content_post_user_id': return $this->_properties['author'];
        }
    }
        
    public function setProperty($name, $value)
    {
        $this->_properties[$name] = $value;
    }
    
    public function isTrashed()
    {
        return $this->_properties['status'] == Sabai_Addon_Content::POST_STATUS_TRASHED;
    }
    
    public function isDraft()
    {
        return $this->_properties['status'] == Sabai_Addon_Content::POST_STATUS_DRAFT;
    }
    
    public function isPending()
    {
        return $this->_properties['status'] == Sabai_Addon_Content::POST_STATUS_PENDING;
    }
    
    public function isPublished()
    {
        return $this->_properties['status'] == Sabai_Addon_Content::POST_STATUS_PUBLISHED;
    }
    
    public function isFeatured()
    {
        return (bool)$this->getSingleFieldValue('content_featured');
    }
    
    public function isPropertyModified($name, $value)
    {
        switch ($name) {
            case 'content_post_title':
                return $value != $this->_properties['title'];
            case 'content_post_published':
                return $value != $this->_properties['published'];
            case 'content_post_status':
                return $value != $this->_properties['status'];
            case 'content_post_user_id':
                return $value != $this->_properties['author']->id;
            case 'content_post_slug':
                return $value != $this->_properties['slug'];
        }
    }
    
    public function getSummary($length = 0, $trimmarker = '...')
    {
        $body = (string)$this->getSingleFieldValue('content_body', 'filtered_value');
        if (!strlen($body)) {
            return '';
        }
        $body = strip_tags(strtr($body, array("\r" => '', "\n" => ' ')));
        
        return empty($length) ? $body : mb_strimwidth($body, 0, $length, $trimmarker);
    }
}