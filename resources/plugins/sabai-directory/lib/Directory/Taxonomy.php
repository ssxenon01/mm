<?php
class Sabai_Addon_Directory_Taxonomy implements Sabai_Addon_Taxonomy_ITaxonomy
{
    private $_addon, $_name;

    public function __construct(Sabai_Addon_Directory $addon, $name)
    {
        $this->_addon = $addon;
        $this->_name = $name;
    }

    public function taxonomyGetInfo()
    {
        switch ($this->_name) {
            case $this->_addon->getCategoryBundleName():
                return array(
                    'type' => 'directory_category',
                    'path' => '/' . $this->_addon->getDirectorySlug() . '/' . $this->_addon->getSlug('categories'),
                    'label' => $this->_addon->getApplication()->_t(_n_noop('Categories', 'Categories', 'sabai-directory')),
                    'label_singular' => $this->_addon->getApplication()->_t(_n_noop('Category', 'Category', 'sabai-directory')),
                    'taxonomy_hierarchical' => true,
                    'taxonomy_body' => array(
                        'required' => false,
                        'title' => __('Description', 'sabai-directory'),
                        'widget_settings' => array('rows' => 15),
                        'weight' => 6,
                    ),
                    'taxonomy_permissions' => array(
                        'add' => false,
                        'edit' => false,
                        'delete' => false,
                    ),
                    'properties' => array(
                        'term_parent' => array(
                            'title' => __('Parent Category', 'sabai-directory'),
                            'weight' => 4,
                        ),
                        'term_title' => array(
                            'weight' => 2,
                        ),
                    ),
                    'fields' => array(
                        'directory_map_marker' => array(
                            'type' => 'file_image',
                            'widget' => 'file_upload',
                            'widget_settings' => array('medium_image' => false, 'large_image' => false),
                            'title' => __('Custom Map Marker Icon', 'sabai-directory'),
                            'max_num_items' => 1,
                            'weight' => 7,
                        ),
                    ),
                );
        }
    }
}
