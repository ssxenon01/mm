<?php
require_once dirname(__FILE__) . '/Listings.php';
class Sabai_Addon_Directory_Controller_AllListings extends Sabai_Addon_Directory_Controller_Listings
{
    protected $_allAddons = false, $_addons = array(), $_categoryBundles = array(), $_isCustomTemplate = false;
    
    protected function _doExecute(Sabai_Context $context)
    {
        // Init add-on names
        if ($addons = $context->getRequest()->asStr('addons', isset($context->addons) ? $context->addons : '')) {
            $addons = array_map('trim', explode(',', $addons));
            foreach ($addons as $addon_key => $addon_name) {
                try {
                    $addon = $this->getAddon($addon_name);
                } catch (Sabai_IException $e) {
                    $this->LogError($e);
                    continue;
                }
                if (!$addon instanceof Sabai_Addon_Directory) {
                    continue;
                }
                $this->_addons[$addon->getListingBundleName()] = $addon_name;
                $this->_categoryBundles[$addon->getCategoryBundleName()] = $addon->getDirectoryPageTitle();
            }
        }
        if (empty($this->_categoryBundles)) {
            $this->_categoryBundles = $this->Directory_DirectoryList('category');
            $this->_allAddons = true;
        }
        // Custom template specified?
        if (($template = $context->getRequest()->asStr('template', isset($context->template) ? $context->template : ''))
            && strpos($template, 'directory_listings_') === 0
            && strpos($template, '.') === false // prevent directory traversal
        ) {
            $this->_template = $template;
            $this->_isCustomTemplate = true;
        }
        
        parent::_doExecute($context);        
    }
    
    protected function _createListingsQuery(Sabai_Context $context, $sort, Sabai_Addon_Entity_Model_Bundle $bundle = null)
    {
        $query = $this->Entity_Query('content')
            ->propertyIs('post_status', Sabai_Addon_Content::POST_STATUS_PUBLISHED);
        if ($this->_allAddons) {
            return $query->propertyIs('post_entity_bundle_type', 'directory_listing');
        }
        return $query->propertyIsIn('post_entity_bundle_name', array_keys($this->_addons));
    }
    
    protected function _getDefaultSettings(Sabai_Context $context)
    {
        $parent_category = 0;
        if ($category = $context->getRequest()->asStr('category', isset($context->category) ? $context->category : '')) {
            if (count($this->_categoryBundles) === 1) {
                $category_bundle = array_shift(array_keys($this->_categoryBundles));
            } else {
                $category_bundle = 'directory_listing_category';
            }
            if ($category = $this->getModel('Term', 'Taxonomy')->entityBundleName_is($category_bundle)->name_is($category)->fetchOne()) {
                $parent_category = $category->id;
            } else {
                $category_bundle = $this->_categoryBundles;
            }
        } else {
            $category_bundle = $this->_categoryBundles;
        }
        $settings = array(
            'perpage' => $context->getRequest()->asInt('perpage', isset($context->perpage) ? (int)$context->perpage : 10),
            'sort' => isset($context->sort) ? $context->sort : null,
            'view' => isset($context->view) ? $context->view : 'list',
            'distance' => isset($context->distance) ? (int)$context->distance : 0,
            'is_mile' => isset($context->is_mile) && $context->is_mile == 1 ? true : false,
            'address' => isset($context->address) ? $context->address : '',
            'keywords' => array(),
            'parent_category' => $parent_category,
            'category_bundle' => $category_bundle,
            'scroll_list' => $context->getRequest()->asBool('scroll_list', isset($context->scroll_list) && $context->scroll_list == 0 ? false : true),
            'map' => array(
                'disable' => !empty($context->map_disable),
                'height' => $map_height = (isset($context->map_height) ? (int)$context->map_height : 500),
                'list_height' => isset($context->list_map_height) ? (int)$context->list_map_height : $map_height,
                'span' => isset($context->map_span) && in_array((int)$context->map_span, array(4, 5, 6, 7, 8)) ? $context->map_span : 5,
                'style' => isset($context->map_style) ? $context->map_style : '',
                'list_show' => !isset($context->list_map_show) || !empty($context->list_map_show),
                'listing_default_zoom' => $context->getRequest()->asInt('zoom', isset($context->zoom) ? (int)$context->zoom : 15),
                'options' => array(
                    'scrollwheel' => !empty($context->map_scrollwheel),
                    'marker_clusters' => !isset($context->map_marker_clusters) || !empty($context->map_marker_clusters),
                ),
            ),
            'hide_searchbox' => $context->getRequest()->asBool('hide_searchbox', !empty($context->hide_searchbox)),
            'hide_nav' => $context->getRequest()->asBool('hide_nav', !empty($context->hide_nav)),
            'hide_nav_views' => $context->getRequest()->asBool('hide_nav_views', !empty($context->hide_nav_views)),
            'hide_pager' => $context->getRequest()->asBool('hide_pager', !empty($context->hide_pager)),
            'featured_only' => $context->getRequest()->asBool('featured_only', !empty($context->featured_only)),
            'feature' => $context->getRequest()->asBool('feature', !isset($context->feature) || !empty($context->feature)), // make featured listings sticky?
            'search' => array('no_loc' => isset($context->search_location) && empty($context->search_location)),
            'buttons' => array('search' => 'sabai-btn-primary'),
            'grid_columns' => $context->getRequest()->asInt('grid_columns', isset($context->grid_columns) ? (int)$context->grid_columns : 4, array(2, 3, 4, 6)),
        );
        if ($sorts = $context->getRequest()->asStr('sorts', isset($context->sorts) ? $context->sorts : null)) {
            $settings['sorts'] = explode(',', $sorts);
        }
        if ($map_settings = $context->getRequest()->asArray('map')) {
            $settings['map'] = $map_settings + $settings['map'];
            if (empty($settings['map']['height'])
                || $settings['map']['height'] < 200
                || $settings['map']['height'] > 1000
            ) {
                $settings['map']['height'] = 500;
            }
            if (empty($settings['map']['span'])
                || !in_array($settings['map']['span'], array(4, 5, 6, 7, 8))
            ) {
                $settings['map']['span'] = 5;
            }
            if (empty($settings['map']['list_height'])
                || $settings['map']['list_height'] < 200
                || $settings['map']['list_height'] > 1000
            ) {
                $settings['map']['list_height'] = 500;
            }
        }
        return $settings + $context->getAttributes(); 
    }
    
    protected function _getUrlParams(Sabai_Context $context, Sabai_Addon_Entity_Model_Bundle $bundle = null)
    {
        return parent::_getUrlParams($context, $bundle) + array(
            'perpage' => $this->_settings['perpage'],
            'scroll_list' => $this->_settings['scroll_list'],
            'feature' => $this->_settings['feature'],
            'featured_only' => $this->_settings['featured_only'],
            'hide_searchbox' => $this->_settings['hide_searchbox'],
            'hide_nav' => $this->_settings['hide_nav'],
            'hide_nav_views' => $this->_settings['hide_nav_views'],
            'hide_pager' => $this->_settings['hide_pager'],
            'template' => $this->_isCustomTemplate ? $this->_template : '',
            'addons' => $this->_allAddons ? '' : implode(',', $this->_addons),
            'sorts' => !empty($this->_settings['sorts']) ? implode(',', $this->_settings['sorts']) : null,
            'grid_columns' => $this->_settings['grid_columns'],
        );
    }
}
