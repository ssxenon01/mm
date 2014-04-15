<?php
class Sabai_Addon_Directory_Controller_Listings extends Sabai_Addon_Content_Controller_ListPosts
{
    protected $_template = 'directory_listings', $_sortContainer = '#sabai-directory-listings', $_scrollTo = '#sabai-directory-listings',
        $_center, $_swne, $_settings, $_isGeolocate = false, $_viewport;
    
    protected function _doExecute(Sabai_Context $context)
    {
        $this->_settings = $this->_getDefaultSettings($context);
        $this->_settings['map'] += array('span' => 5);
        $this->_perPage = $this->_settings['perpage'];
        $this->_defaultSort = $this->_settings['sort'];
        // Init distances
        $this->_settings['distances'] = array(0, 1, 2, 5, 10, 20, 50, 100);
        // Init views
        if (empty($this->_settings['map']['disable'])) {
            $this->_settings['views'] = array('list', 'map');
        } else {
            $this->_settings['views'] = array('list');
            $this->_settings['map']['list_show'] = false;
        }
        
        if ($keywords = $context->getRequest()->asStr('keywords', $this->_settings['keywords'])) {
            $this->_settings['keywords'] = $this->Keywords($keywords, $this->getAddon()->getConfig('search', 'min_keyword_len'));
        }
        $this->_settings['category'] = $context->getRequest()->asInt('category', $this->_settings['parent_category']);
        // Allow switching view mode only if views nav is enabled
        if (empty($this->_settings['hide_nav']) && empty($this->_settings['hide_nav_views'])) {
            if ((null === $view = $this->getPlatform()->getCookie('sabai_directory_map_view', null))
                || !in_array($view, $this->_settings['views'])
            ) {
                $this->_settings['view'] = $context->getRequest()->asStr('view', $this->_settings['view'], $this->_settings['views']);
                $this->getPlatform()->setCookie('sabai_directory_map_view', $this->_settings['view']);
            } else {
                $this->_settings['view'] = $context->getRequest()->asStr('view', $view, $this->_settings['views']);
                if ($this->_settings['view'] !== $view) {
                    $this->getPlatform()->setCookie('sabai_directory_map_view', $this->_settings['view']);
                }
            }
            if (count($this->_settings['views']) <= 1) {
                $this->_settings['hide_nav_views'] = true;
            }
        }
        if ($address = $context->getRequest()->asStr('address', $this->_settings['address'])) {
            $this->_settings['address'] = trim($address);
        }
        if ($zoom = $context->getRequest()->asInt('zoom')) {
            $this->_settings['map']['listing_default_zoom'] = $zoom;
        }
        // the value of scrollwheel must be boolean so that json_encode will convert it correctly
        $this->_settings['map']['options']['scrollwheel'] = !empty($this->_settings['map']['options']['scrollwheel']);
        $this->_settings['distance'] = $context->getRequest()->asInt('distance', $this->_settings['distance'], $this->_settings['distances']);
        $this->_settings['is_mile'] = $context->getRequest()->asBool('is_mile', $this->_settings['is_mile']);
        
        // Lat/Lng specified?
        if ($center = $context->getRequest()->asStr('center')) {
            if (($center_latlng = explode(',', $center))
                && count($center_latlng) === 2
            ) {
                $this->_center = array((float)$center_latlng[0], (float)$center_latlng[1]);
            }
            if ($sw = $context->getRequest()->asStr('sw')) {
                if (($sw_latlng = explode(',', $sw))
                    && count($sw_latlng) === 2
                ) {
                    if ($ne = $context->getRequest()->asStr('ne')) {
                        if (($ne_latlng = explode(',', $ne))
                            && count($ne_latlng) === 2
                        ) {
                            $this->_swne = $this->_viewport = array(
                                array((float)$sw_latlng[0], (float)$sw_latlng[1]),
                                array((float)$ne_latlng[0], (float)$ne_latlng[1])
                            );
                            $this->_settings['address'] = '';
                        }
                    }
                }
            }
        }
        
        // Geolocation?
        if (($is_geolocate = $context->getRequest()->asBool('is_geolocate'))
            && isset($this->_center)
            && ($geocode = $this->GoogleMaps_Geocode(implode(',', $this->_center), true))
        ) {
            // Set address to fill the location input text field and display the distance selection dropdown
            $this->_settings['address'] = $geocode->address;
            $this->_isGeolocate = true;
        } else {
            // Fetch center lat/lng from address
            if (strlen($this->_settings['address'])
                && ($geocode = $this->GoogleMaps_Geocode($this->_settings['address']))
            ) {
                $this->_center = array($geocode->lat, $geocode->lng);
                // Fetch viewport if no distance speficied
                if (empty($this->_settings['distance']) && $geocode->viewport && ($viewport = explode(',', $geocode->viewport))) {
                    $this->_viewport = array(array($viewport[0], $viewport[1]), array($viewport[2], $viewport[3]));
                }
            }
        }
        
        parent::_doExecute($context);
        $distances = array();
        if (strlen($this->_settings['address'])) {
            foreach ($this->_settings['distances'] as $distance) {
                $distances[$distance] = $this->LinkToRemote(
                    empty($distance) ? __('None', 'sabai-directory') : sprintf($this->_settings['is_mile'] ? _n('%d mile', '%d miles', $distance, 'sabai-directory') : _n('%d kilometer', '%d kilometers', $distance, 'sabai-directory'), $distance),
                    '#sabai-directory-listings',
                    $this->Url($context->getRoute(), array('distance' => $distance) + $context->url_params),
                    array('active' => $this->_settings['distance'] == $distance, 'scroll' => '#sabai-directory-listings')
                );
            }
        }
        $context->setAttributes(array(
            'settings' => $this->_settings,
            'views' => $this->_getListingViews($context),
            'distances' => $distances,
            'center' => $this->_center,
            'is_drag' => $context->getRequest()->asBool('is_drag'),
            'is_geolocate' => $is_geolocate,
        ));
        // Load partial content if request is ajax
        if ($context->getRequest()->isAjax() === '#sabai-directory-listings') {
            $context->addTemplate('directory_listings_' . $this->_settings['view']);
        }
    }
    
    protected function _getListingViews(Sabai_Context $context)
    {
        $views = array(
            'list' => array(
                'label' => __('List', 'sabai-directory'),
                'icon' => 'th-list',
                'title' => __('Switch to list view', 'sabai-directory'),
            ),
            'map' => array(
                'label' => __('Map', 'sabai-directory'),
                'icon' => 'map-marker',
                'title' => __('Switch to map view', 'sabai-directory'),
            ),
        );
        $ret = array();
        $params = $context->url_params;
        if (isset($context->paginator)) {
            $params[Sabai::$p] = $context->paginator->getCurrentPage();
        }
        foreach (array_intersect_key($views, array_flip($this->_settings['views'])) as $view => $view_data) {
            $ret[$view] = $this->LinkToRemote(
                $view_data['label'],
                '#sabai-directory-listings',
                $this->Url($context->getRoute(), array('view' => $view) + $params),
                array('active' => $this->_settings['view'] === $view, 'icon' => $view_data['icon'], 'scroll' => '#sabai-directory-listings'),
                array('title' => $view_data['title'], 'data-container' => $this->_sortContainer)
            );
        }

        return $ret;
    }

    protected function _getUrlParams(Sabai_Context $context, Sabai_Addon_Entity_Model_Bundle $bundle = null)
    {
        $params = array();
        if (strlen($this->_settings['address'])) {
            $params['address'] = $this->_settings['address'];
        }
        if (!empty($this->_settings['keywords'][0])) {
            $params['keywords'] = implode(' ', $this->_settings['keywords'][0]);
        }
        if (!empty($this->_settings['category'])) {
            $params['category'] = $this->_settings['category'];
        }
        if ($this->_center) {
            $params['center'] = implode(',', $this->_center);
            if ($this->_isGeolocate) {
                $params['is_geolocate'] = 1;
            }
        }
        if ($this->_swne) {
            $params['sw'] = implode(',', $this->_swne[0]);
            $params['ne'] = implode(',', $this->_swne[1]);
        }
        $params['map'] = $this->_settings['map'];
        $params['distance'] = $this->_settings['distance'];
        $params['is_mile'] = $this->_settings['is_mile'];
        $params['zoom'] = $this->_settings['map']['listing_default_zoom'];
        
        return $params;
    }

    protected function _getSorts(Sabai_Context $context)
    {
        $sorts = array(
            'newest' => __('Newest First', 'sabai-directory'),
            'reviews' => __('Most Reviews', 'sabai-directory'),
            'rating' => __('Highest Rated', 'sabai-directory'),
            'title' => _x('Title', 'sort', 'sabai-directory'),
            'random' => __('Random', 'sabai-directory'),
        );
        if (strlen($this->_settings['address'])) {
            $sorts['distance'] = __('Distance', 'sabai-directory');
        }
        
        return isset($this->_settings['sorts']) ? array_intersect_key($sorts, array_flip($this->_settings['sorts'])) : $sorts;
    }
    
    protected function _createQuery(Sabai_Context $context, $sort, Sabai_Addon_Entity_Model_Bundle $bundle = null)
    {
        $query = $this->_createListingsQuery($context, $sort, $bundle);
        if (!empty($this->_settings['feature'])) {
            $query->sortByField('content_featured', 'DESC');
        }
        return $this->Directory_ListingsQuery(
            $query,
            isset($this->_center) ? $this->_center : $this->_settings['address'],
            $this->_settings['keywords'],
            $this->_settings['category'],
            $sort,
            isset($this->_viewport) ? $this->_viewport : $this->_settings['distance'],
            $this->_settings['is_mile'],
            !empty($this->_settings['featured_only'])
        );
    }
    
    protected function _createListingsQuery(Sabai_Context $context, $sort, Sabai_Addon_Entity_Model_Bundle $bundle = null)
    {
        return parent::_createQuery($context, $sort, $bundle);
    }
    
    protected function _getDefaultSettings(Sabai_Context $context)
    {
        $config = $this->getAddon()->getConfig();
        return array(
            'perpage' => $config['display']['perpage'],
            'sorts' => isset($config['display']['sorts']) ? $config['display']['sorts'] : null,
            'sort' => $config['display']['sort'],
            'view' => $config['display']['view'],
            'distance' => 0,
            'is_mile' => @$config['map']['distance_mode'] === 'mil',
            'address' => '',
            'keywords' => array(),
            'parent_category' => $this->_getDefaultCategoryId($context),
            'category_bundle' => $this->getAddon()->getCategoryBundleName(),
            'map' => $config['map'] + array('listing_default_zoom' => 15, 'options' => array()), // for compat with 1.2.5 or lower
            'scroll_list' => false,
            'feature' => !isset($config['display']['stick_featured']) || !empty($config['display']['stick_featured']),  // for compat with 1.2.17 or lower
            'search' => $config['search'],
        );
    }
    
    protected function _getDefaultCategoryId(Sabai_Context $context)
    {
        return 0;
    }
}