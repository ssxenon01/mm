<?php
class Sabai_Addon_Directory extends Sabai_Addon
    implements Sabai_Addon_Field_ITypes,
               Sabai_Addon_Field_IWidgets,
               Sabai_Addon_Taxonomy_ITaxonomies,
               Sabai_Addon_System_IMainRouter,
               Sabai_Addon_System_IAdminRouter,
               Sabai_Addon_Content_IContentTypes,
               Sabai_Addon_System_IAdminMenus,
               Sabai_Addon_System_IUserMenus,
               Sabai_Addon_Widgets_IWidgets,
               Sabai_Addon_System_IMainMenus
{
    const VERSION = '1.2.32', PACKAGE = 'sabai-directory';
    
    private static $_gmapLoaded = false;    
    protected $_path, $_listingBundleName, $_reviewBundleName, $_leadBundleName, $_photoBundleName, $_categoryBundleName;
    
    protected function _init()
    {
        $this->_path = $this->_application->Path(dirname(__FILE__) . '/Directory');
        $this->_listingBundleName = $this->_config['listing_name'];
        $this->_reviewBundleName = $this->_config['listing_name'] . '_review';
        $this->_photoBundleName = $this->_config['listing_name'] . '_photo';
        $this->_leadBundleName = $this->_config['listing_name'] . '_lead';
        $this->_categoryBundleName = $this->_config['listing_name'] . '_category';
        
        return $this;
    }
        
    public function isCloneable()
    {
        return !$this->hasParent();
    }
    
    public function getListingBundleName()
    {
        return $this->_listingBundleName;
    }
    
    public function getReviewBundleName()
    {
        return $this->_reviewBundleName;
    }

    public function getPhotoBundleName()
    {
        return $this->_photoBundleName;
    }

    public function getLeadBundleName()
    {
        return $this->_leadBundleName;
    }

    public function getCategoryBundleName()
    {
        return $this->_categoryBundleName;
    }
    
    public function getDirectorySlug()
    {
        return $this->_config['pages']['directory_slug'];
    }
        
    public function getDirectoryPageTitle()
    {
        return $this->_config['pages']['directory_title'];
    }
    
    public function getDashboardSlug()
    {
        if ($parent = $this->hasParent()) {
            return $this->_application->getAddon($parent)->getConfig('pages', 'dashboard_slug');
        }
        return $this->_config['pages']['dashboard_slug'];
    }
    
    public function getDashboardPageTitle()
    {
        if ($parent = $this->hasParent()) {
            return $this->_application->getAddon($parent)->getConfig('pages', 'dashboard_title');
        }
        return $this->_config['pages']['dashboard_title'];
    }
    
    public function getSlug($name)
    {
        return isset($this->_config['pages'][$name . '_slug']) // check for backward compat with 1.0.x versions
            ? $this->_config['pages'][$name . '_slug']
            : $name;
    }
    
    public function getPageTitle($name = 'main')
    {
        return isset($this->_config['page_title'][$name]) // check for backward compat with 1.0.x versions
            ? $this->_config['page_title'][$name]
            : $this->_config['title'];
    }
    
    /* Start implementation of Sabai_Addon_System_IMainRouter */
    
    public function systemGetMainRoutes()
    {        
        $routes = array(
            '/' . $this->getDirectorySlug() => array(
                'controller' => 'Listings',
                'access_callback' => true,
                'callback_addon' => 'Content',
                'callback_path' => 'posts',
                'title_callback' => true,
                'data' => array(
                    'bundle_name' => $this->_config['listing_name'],
                ),
                'controller_addon' => 'Directory',
                'type' => Sabai::ROUTE_TAB,
                'priority' => 5,
            ),
            '/' . $this->getDirectorySlug() . '/add' => array(
                'controller' => 'AddListing',
                'controller_addon' => 'Directory',
                'access_callback' => true,
                'title_callback' => true,
                'callback_path' => 'add_listing',
                'priority' => 5,
            ),
            '/' . $this->getDirectorySlug() . '/feed' => array(
                'controller' => 'Feed',
                'controller_addon' => 'Directory',
                'priority' => 5,
                'type' => Sabai::ROUTE_CALLBACK,
            ),
            '/' . $this->getDirectorySlug() . '/users/:user_name' => array(
                'callback_path' => 'user_reviews',
                'controller' => 'UserReviews',
                'access_callback' => true,
                'title_callback' => true,
                'format' => array(':user_name' => '.+'),
                'controller_addon' => 'Directory',
                'data' => array('clear_tabs' => true),
                'priority' => 5,
                'type' => Sabai::ROUTE_TAB,
            ),
            '/' . $this->getDirectorySlug() . '/users/:user_name/photos' => array(
                'controller' => 'UserPhotos',
                'access_callback' => true,
                'title_callback' => true,
                'callback_path' => 'user_photos',
                'controller_addon' => 'Directory',
                'priority' => 5,
                'type' => Sabai::ROUTE_TAB,
                'weight' => 3,
            ),
            '/' . $this->getDirectorySlug() . '/users/:user_name/bookmarks' => array(
                'controller' => 'UserBookmarks',
                'title_callback' => true,
                'callback_path' => 'user_bookmarks',
                'controller_addon' => 'Directory',
                'priority' => 5,
                'type' => Sabai::ROUTE_TAB,
                'weight' => 9,
            ),
            '/' . $this->getDirectorySlug() . '/' . $this->getConfig('pages', 'listing_slug') . '/:slug/edit' => array(
                'controller' => 'EditListing',
                'access_callback' => true,
                'title_callback' => true,
                'callback_path' => 'edit_listing',
                'controller_addon' => 'Directory',
                'priority' => 5,
            ),
            '/' . $this->getDirectorySlug() . '/' . $this->getConfig('pages', 'listing_slug') . '/:slug/delete' => array(
                'controller' => 'TrashPost',
                'title_callback' => true,
                'callback_path' => 'delete_listing',
                'controller_addon' => 'Content',
                'priority' => 5,
            ),
            '/' . $this->getDirectorySlug() . '/' . $this->getConfig('pages', 'listing_slug') . '/:slug/' . $this->getSlug('reviews') => array(
                'controller' => 'ListingReviews',
                'access_callback' => true,
                'title_callback' => true,
                'type' => Sabai::ROUTE_INLINE_TAB,
                'callback_path' => 'listing_reviews',
                'controller_addon' => 'Directory',
                'priority' => 5,
                'weight' => $this->_config['display']['listing_default_tab'] === 'reviews' ? 5 : 6,
                'data' => array('clear_tabs' => true),
            ),
             '/' . $this->getDirectorySlug() . '/' . $this->getConfig('pages', 'listing_slug') . '/:slug/' . $this->getSlug('photos') => array(
                'controller' => 'ListingPhotos',
                'access_callback' => true,
                'title_callback' => true,
                'type' => Sabai::ROUTE_INLINE_TAB,
                'callback_path' => 'listing_photos',
                'controller_addon' => 'Directory',
                'priority' => 5,
                'weight' => $this->_config['display']['listing_default_tab'] === 'photos' ? 5 : 10,
                'data' => array('clear_tabs' => true),
            ),
            '/' . $this->getDirectorySlug() . '/' . $this->getConfig('pages', 'listing_slug') . '/:slug/' . $this->getSlug('map') => array(
                'controller' => 'ListingMap',
                'title_callback' => true,
                'access_callback' => true,
                'type' => Sabai::ROUTE_INLINE_TAB,
                'callback_path' => 'listing_map',
                'controller_addon' => 'Directory',
                'priority' => 5,
                'weight' => $this->_config['display']['listing_default_tab'] === 'map' ? 5 : 50,
            ),
            '/' . $this->getDirectorySlug() . '/' . $this->getConfig('pages', 'listing_slug') . '/:slug/' . $this->getSlug('contact') => array(
                'controller' => 'ListingContact',
                'title_callback' => true,
                'access_callback' => true,
                'type' => Sabai::ROUTE_INLINE_TAB,
                'callback_path' => 'listing_contact',
                'controller_addon' => 'Directory',
                'priority' => 5,
                'weight' => $this->_config['display']['listing_default_tab'] === 'contact' ? 5 : 60,
            ),
            '/' . $this->getDirectorySlug() . '/' . $this->getConfig('pages', 'listing_slug') . '/:slug/' . $this->getSlug('reviews') . '/add' => array(
                'controller' => 'AddReview',
                'access_callback' => true,
                'title_callback' => true,
                'callback_path' => 'review',
                'controller_addon' => 'Directory',
                'priority' => 5,
            ),
            '/' . $this->getDirectorySlug() . '/' . $this->getConfig('pages', 'listing_slug') . '/:slug/' . $this->getSlug('photos') . '/add' => array(
                'controller' => 'UploadPhotos',
                'access_callback' => true,
                'title_callback' => true,
                'callback_path' => 'upload_photos',
                'controller_addon' => 'Directory',
                'priority' => 5,
            ),
            '/' . $this->getDirectorySlug() . '/' . $this->getSlug('categories') => array(
                'controller' => 'ListHierarchicalTerms',
                'access_callback' => true,
                'title_callback' => true,
                'callback_path' => 'categories',
                'controller_addon' => 'Taxonomy',
                'priority' => 5,
                'type' => Sabai::ROUTE_TAB,
                'weight' => 5,
                'ajax' => false,
            ),
            '/' . $this->getDirectorySlug() . '/' . $this->getSlug('reviews') => array(
                'controller' => 'Reviews',
                'access_callback' => true,
                'title_callback' => true,
                'callback_path' => 'reviews',
                'controller_addon' => 'Directory',
                'priority' => 5,
                'type' => Sabai::ROUTE_TAB,
                'weight' => 10,
                'ajax' => false,
            ),
            '/' . $this->getDirectorySlug() . '/' . $this->getSlug('reviews') . '/:entity_id/edit' => array(
                'controller' => 'EditReview',
                'access_callback' => true,
                'title_callback' => true,
                'callback_addon' => 'Content',
                'callback_path' => 'edit_child_post',
                'controller_addon' => 'Directory',
                'priority' => 5,
            ),
            '/' . $this->getDirectorySlug() . '/' . $this->getSlug('photos') => array(
                'controller' => 'Photos',
                'access_callback' => true,
                'title_callback' => true,
                'callback_path' => 'photos',
                'controller_addon' => 'Directory',
                'priority' => 5,
                'type' => Sabai::ROUTE_TAB,
                'weight' => 15,
                'ajax' => false,
            ),
            '/' . $this->getDirectorySlug() . '/' . $this->getSlug('categories') . '/:slug/' . $this->getDirectorySlug() => array(
                'controller' => 'TermListings',
                'title_callback' => true,
                'type' => Sabai::ROUTE_INLINE_TAB,
                'weight' => 1,
                'controller_addon' => 'Directory',
                'callback_path' => 'term_listings',
                'priority' => 5,
            ),
            '/' . $this->getDirectorySlug() . '/' . $this->getSlug('reviews') . '/:entity_id' => array(
                'controller' => 'RedirectToListing',
                'format' => array(':entity_id' => '\d+'),
                'access_callback' => true,
                'title_callback' => true,
                'callback_addon' => 'Content',
                'callback_path' => 'child_post',
                'controller_addon' => 'Directory',
                'priority' => 5,
            ),
            '/' . $this->getDirectorySlug() . '/' . $this->getSlug('photos') . '/:entity_id' => array(
                'controller' => 'RedirectToListing',
                'format' => array(':entity_id' => '\d+'),
                'access_callback' => true,
                'title_callback' => true,
                'callback_addon' => 'Content',
                'callback_path' => 'child_post',
                'controller_addon' => 'Directory',
                'priority' => 5,
            ),
            '/' . $this->getDirectorySlug() . '/' . $this->getConfig('pages', 'listing_slug') . '/:slug/' . $this->getSlug('claim') => array(
                'controller' => 'ClaimListing',
                'access_callback' => true,
                'title_callback' => true,
                'callback_path' => 'claim',
                'controller_addon' => 'Directory',
                'priority' => 5,
            ),
        );
        if (!$this->hasParent()) {
            $routes += array(
                '/' . $this->getDashboardSlug() => array(
                    'controller' => 'Dashboard',
                    'title_callback' => true,
                    'access_callback' => true,
                    'callback_path' => 'dashboard',
                    'controller_addon' => 'Directory',
                    'priority' => 5,
                ),
                '/' . $this->getDashboardSlug() . '/:listing_id' => array(
                    'format' => array(':listing_id' => '\d+'),
                    'access_callback' => true,
                    'callback_path' => 'my_listing',
                    'controller_addon' => 'Directory',
                    'priority' => 5,
                ),
                '/' . $this->getDashboardSlug() . '/:listing_id/edit' => array(
                    'controller' => 'EditMyListing',
                    'title_callback' => true,
                    'access_callback' => true,
                    'callback_path' => 'edit_my_listing',
                    'controller_addon' => 'Directory',
                    'priority' => 5,
                ),
                '/' . $this->getDashboardSlug() . '/:listing_id/upload_photos' => array(
                    'controller' => 'UploadMyListingPhotos',
                    'title_callback' => true,
                    'access_callback' => true,
                    'callback_path' => 'upload_my_listing_photos',
                    'controller_addon' => 'Directory',
                    'priority' => 5,
                ),
                '/' . $this->getDashboardSlug() . '/leads/:lead_id' => array(
                    'controller' => 'Lead',
                    'title_callback' => true,
                    'access_callback' => true,
                    'callback_path' => 'lead',
                    'controller_addon' => 'Directory',
                    'priority' => 5,
                ),
                '/' . $this->getDashboardSlug() . '/leads' => array(
                    'controller' => 'Leads',
                    'type' => Sabai::ROUTE_TAB,
                    'title_callback' => true,
                    'callback_path' => 'leads',
                    'controller_addon' => 'Directory',
                    'priority' => 5,
                ),
                '/' . $this->getDashboardSlug() . '/bookmarks' => array(
                    'controller' => 'Bookmarks',
                    'type' => Sabai::ROUTE_TAB,
                    'title_callback' => true,
                    'callback_path' => 'bookmarks',
                    'weight' => 30,
                    'controller_addon' => 'Directory',
                    'priority' => 5,
                ),
                '/sabai/directory' => array(
                    'controller' => 'AllListings',
                    'type' => Sabai::ROUTE_CALLBACK,
                    'access_callback' => true,
                    'callback_path' => 'directory',
                    'controller_addon' => 'Directory',
                    'priority' => 5,
                ),
                '/sabai/directory/map' => array(
                    'controller' => 'Map',
                    'type' => Sabai::ROUTE_CALLBACK,
                    'controller_addon' => 'Directory',
                    'priority' => 5,
                ),
                '/sabai/directory/categories' => array(
                    'controller' => 'AllCategories',
                    'type' => Sabai::ROUTE_CALLBACK,
                    'controller_addon' => 'Directory',
                    'priority' => 5,
                ),
                '/sabai/directory/geolocate' => array(
                    'controller' => 'GeoLocate',
                    'type' => Sabai::ROUTE_CALLBACK,
                    'controller_addon' => 'Directory',
                    'priority' => 5,
                ),
                '/sabai/directory/feed' => array(
                    'controller' => 'AllFeeds',
                    'type' => Sabai::ROUTE_CALLBACK,
                    'controller_addon' => 'Directory',
                    'priority' => 5,
                ),
                '/sabai/directory/searchform' => array(
                    'controller' => 'SearchForm',
                    'type' => Sabai::ROUTE_CALLBACK,
                    'controller_addon' => 'Directory',
                    'priority' => 5,
                ),
                '/sabai/directory/add' => array(
                    'controller' => 'AddListing',
                    'type' => Sabai::ROUTE_CALLBACK,
                    'controller_addon' => 'Directory',
                    'priority' => 5,
                ),
            );
        }
        
        return $routes;
    }

    public function systemOnAccessMainRoute(Sabai_Context $context, $path, $accessType, array &$route)
    {
        switch ($path) {
            case 'posts':
                return true;
            case 'review':
                if ($accessType === Sabai::ROUTE_ACCESS_LINK) {
                    return true;
                }
                if (!$this->_application->HasPermission($context->child_bundle->name . '_add')) {
                    if ($this->_application->getUser()->isAnonymous()) {
                        $context->setUnauthorizedError($this->_application->Entity_Url($context->entity, '/reviews/add'));
                    }
                    return false;
                }
                return true;
            case 'upload_photos':
                if ($this->_application->getUser()->isAnonymous()) {
                    // Guest users can upload photos through reviews only
                    return false;
                }
                if (empty($this->_config['photo']['max_num_photos'])) {
                    return false;
                }
                if ($accessType === Sabai::ROUTE_ACCESS_LINK) {
                    return true;
                }            
                return $this->_application->HasPermission($context->child_bundle->name . '_add');
            case 'categories':
                if ($accessType !== Sabai::ROUTE_ACCESS_CONTENT) return !empty($this->_config['display']['f_tabs']['categories']);
                return ($context->taxonomy_bundle = $this->_application->Entity_Bundle($this->_categoryBundleName)) ? true : false;
            case 'reviews':
                if ($accessType !== Sabai::ROUTE_ACCESS_CONTENT) return !empty($this->_config['display']['f_tabs']['reviews']);
                return ($context->child_bundle = $this->_application->Entity_Bundle($this->_reviewBundleName)) ? true : false;
            case 'listing_reviews':
                if ($accessType !== Sabai::ROUTE_ACCESS_CONTENT) return true;
                return ($context->child_bundle = $this->_application->Entity_Bundle($this->_reviewBundleName)) ? true : false;
            case 'user_reviews':
                $user_name = $context->getRequest()->asStr('user_name');
                $context->identity = $this->_application->UserIdentityByUsername(rawurldecode($user_name));
                if ($context->identity->isAnonymous()) {
                    return false;
                }
                if ($accessType !== Sabai::ROUTE_ACCESS_CONTENT) return true;
                return ($context->child_bundle = $this->_application->Entity_Bundle($this->_reviewBundleName)) ? true : false;
            case 'listing_map':
                if (!empty($this->_config['map']['disable'])) return false;
                $lat = $context->entity->getSingleFieldValue('directory_location', 'lat');
                return !empty($lat);
            case 'photos':
                if ($accessType !== Sabai::ROUTE_ACCESS_CONTENT) return !empty($this->_config['display']['f_tabs']['photos']);
                return ($context->child_bundle = $this->_application->Entity_Bundle($this->_photoBundleName)) ? true : false;
            case 'listing_photos':
            case 'user_photos':
                if ($accessType !== Sabai::ROUTE_ACCESS_CONTENT) return true;
                return ($context->child_bundle = $this->_application->Entity_Bundle($this->_photoBundleName)) ? true : false;
            case 'dashboard':
                if ($this->_application->getUser()->isAnonymous()) {
                    $context->setUnauthorizedError($route['path']);
                    return false;
                }
                $context->addTemplateDir($this->_application->getPlatform()->getAssetsDir('sabai-directory') . '/templates');
                return true;
            case 'my_listing':
                if ((!$id = $context->getRequest()->asInt('listing_id'))
                    || (!$entity = $this->_application->Entity_TypeImpl('content')->entityTypeGetEntityById($id))
                    || !$this->_application->Directory_IsListingOwner($entity, false)
                ) {
                    return false;
                }
                $context->entity = $entity;
                return true;
            case 'edit_my_listing':
            case 'upload_my_listing_photos':
                return $this->_application->Directory_IsListingOwner($context->entity, true);
            case 'lead':
                if ((!$id = $context->getRequest()->asInt('lead_id'))
                    || (!$entity = $this->_application->Entity_TypeImpl('content')->entityTypeGetEntityById($id))
                    || (!$listing = $this->_application->Content_ParentPost($entity))
                    || !$this->_application->Directory_IsListingOwner($listing, true)
                ) {
                    return false;
                }
                $context->entity = $entity;
                return true;
            case 'claim':
                if ((isset($this->_config['claims']['allow_existing']) && !$this->_config['claims']['allow_existing'])
                    || !empty($context->entity->directory_claim)
                ) {
                    return false;
                }
                if ($this->_application->HasPermission($context->bundle->name . '_claim')) {
                    return true;
                }
                if ($this->_application->getUser()->isAnonymous()) {
                    $context->setUnauthorizedError($this->_application->Entity_Url($context->entity, '/' . $this->getSlug('claim')));
                }
                return false; 
            case 'add_listing':
                if ($this->_application->HasPermission($context->bundle->name . '_add')) {
                    return true;
                }
                if ($this->_application->getUser()->isAnonymous()) {
                    $context->setUnauthorizedError($route['path']);
                }
                return false; 
            case 'edit_listing':
                // If the listing is already claimed, do not allow anyone to edit it via this route
                if (!empty($context->entity->directory_claim)) {
                    return false;
                }
                return $this->_application->HasPermission($this->_listingBundleName . '_edit_any')
                    || ($this->_application->HasPermission($this->_listingBundleName . '_edit_own') && $this->_application->Content_IsAuthor($context->entity));
            case 'directory':
                $context->addTemplateDir($this->_application->getPlatform()->getAssetsDir('sabai-directory') . '/templates');
                return true;
            case 'listing_tab':
                $context->tab_name = basename($route['path']);
                return true;
            case 'listing_contact':
                return !empty($context->entity->directory_claim)
                    && $this->_application->HasPermission($this->_leadBundleName . '_add')
                    && ($context->child_bundle = $this->_application->Entity_Bundle($this->_leadBundleName));
        }
    }

    public function systemGetMainRouteTitle(Sabai_Context $context, $path, $title, $titleType, array $route)
    {
        switch ($path) {
            case 'posts':
                return $titleType === Sabai::ROUTE_TITLE_TAB || $titleType === Sabai::ROUTE_TITLE_TAB_DEFAULT
                    ? _x('Listings', 'tab', 'sabai-directory')
                    : $this->getDirectoryPageTitle();
            case 'term_listings':
                return $titleType === Sabai::ROUTE_TITLE_TAB || $titleType === Sabai::ROUTE_TITLE_TAB_DEFAULT
                    ? sprintf(_x('Search %s', 'tab', 'sabai-directory'), $context->entity->getTitle())
                    : $context->entity->getTitle();
            case 'listing_reviews':
                if ($titleType !== Sabai::ROUTE_TITLE_TAB && $titleType !== Sabai::ROUTE_TITLE_TAB_DEFAULT) {
                    return _x('Reviews', 'tab', 'sabai-directory');
                }
                return ($count = $context->entity->getSingleFieldValue('content_children_count', 'directory_listing_review'))
                    ? sprintf(__('Reviews (%d)', 'sabai-directory'), $count)
                    : _x('Reviews', 'tab', 'sabai-directory');
            case 'listing_photos':
                if ($titleType !== Sabai::ROUTE_TITLE_TAB && $titleType !== Sabai::ROUTE_TITLE_TAB_DEFAULT) {
                    return _x('Photos', 'tab', 'sabai-directory');
                }
                return ($count = $context->entity->getSingleFieldValue('content_children_count', 'directory_listing_photo'))
                    ? sprintf(__('Photos (%d)', 'sabai-directory'), $count)
                    : _x('Photos', 'tab', 'sabai-directory');
            case 'listing_map':
                return __('Map', 'sabai-directory');
            case 'review':
                return __('Write a Review', 'sabai-directory');
            case 'upload_photos':
                return __('Add Photos', 'sabai-directory');
            case 'upload_my_listing_photos':
                return sprintf(__('Add Photos - %s', 'sabai-directory'), $context->entity->getTitle());
            case 'my_listing_leads':
                return sprintf(__('Leads - %s', 'sabai-directory'), $context->entity->getTitle());
            case 'user_reviews':
                return $titleType === Sabai::ROUTE_TITLE_TAB_DEFAULT
                    ? _x('Reviews', 'tab', 'sabai-directory')
                    : sprintf(__('Posts by %s', 'sabai-directory'), $context->identity->name);
            case 'user_photos':
                return _x('Photos', 'tab', 'sabai-directory');
            case 'dashboard':
                return $titleType === Sabai::ROUTE_TITLE_TAB_DEFAULT
                    ? _x('Listings', 'tab', 'sabai-directory')
                    : $this->getDashboardPageTitle();
            case 'edit_my_listing':
                return sprintf(__('Edit Listing - %s', 'sabai-directory'), $context->entity->getTitle());
            case 'add_listing':
                $context->popInfo();
                return __('Add Listing', 'sabai-directory');
            case 'claim':
                return __('Claim Listing', 'sabai-directory');
            case 'edit_listing':
                return __('Edit Listing', 'sabai-directory');
            case 'delete_listing':
                return __('Delete Listing', 'sabai-directory');
            case 'categories':
                return __('Categories', 'sabai-directory');
            case 'reviews':
                return $titleType === Sabai::ROUTE_TITLE_TAB ? _x('Reviews', 'tab', 'sabai-directory') : __('All Reviews', 'sabai-directory');
            case 'photos':
                return $titleType === Sabai::ROUTE_TITLE_TAB ? _x('Photos', 'tab', 'sabai-directory') : __('All Photos', 'sabai-directory');
            case 'user_bookmarks':
            case 'bookmarks':
                return _x('Bookmarks', 'tab', 'sabai-directory');
            case 'listing_contact':
                return __('Contact Us', 'sabai-directory');
            case 'leads':
                return __('Leads', 'sabai-directory');
        }
    }

    /* End implementation of Sabai_Addon_System_IMainRouter */
    
    /* Start implementation of Sabai_Addon_System_IAdminRouter */

    public function systemGetAdminRoutes()
    {
        return array(
            '/' . $this->getDirectorySlug() . '/add' => array(
                'controller' => 'AddListing',
                'title_callback' => true,
                'callback_addon' => 'Content',
                'controller_addon' => 'Directory',
                'callback_path' => 'add_post',
                'priority' => 5,
            ),
            '/' . $this->getDirectorySlug() . '/claims' => array(
                'controller' => 'ListingClaims',
                'title_callback' => true,
                'controller_addon' => 'Directory',
                'callback_path' => 'claims',
                'type' => Sabai::ROUTE_TAB,
                'priority' => 5,
            ),
            '/' . $this->getDirectorySlug() . '/claims/:claim_id' => array(
                'controller' => 'ViewListingClaim',
                'format' => array(':claim_id' => '\d+'),
                'title_callback' => true,
                'access_callback' => true,
                'controller_addon' => 'Directory',
                'callback_path' => 'claim',
                'priority' => 5,
            ),
            '/' . $this->getDirectorySlug() . '/:entity_id' => array(
                'controller' => 'EditListing',
                'format' => array(':entity_id' => '\d+'),
                'access_callback' => true,
                'title_callback' => true,
                'callback_path' => 'edit_post',
                'callback_addon' => 'Content',
                'data' => array('clear_tabs' => true),
                'controller_addon' => 'Directory',
                'priority' => 5,
            ),
            '/' . $this->getDirectorySlug() . '/settings' => array(
                'controller' => 'Settings',
                'title_callback' => true,
                'controller_addon' => 'Directory',
                'access_callback' => true,
                'callback_path' => 'settings',
                'data' => array('clear_tabs' => true),
            ),
            '/' . $this->getDirectorySlug() . '/settings/acl' => array(
                'controller' => 'AccessControl',
                'title_callback' => true,
                'type' => Sabai::ROUTE_TAB,
                'controller_addon' => 'Directory',
                'callback_path' => 'settings_acl',
            ),
            '/' . $this->getDirectorySlug() . '/settings/emails' => array(
                'controller' => 'Emails',
                'title_callback' => true,
                'type' => Sabai::ROUTE_TAB,
                'controller_addon' => 'Directory',
                'callback_path' => 'settings_emails',
            ),
        );
    }

    public function systemOnAccessAdminRoute(Sabai_Context $context, $path, $accessType, array &$route)
    {
        switch ($path) {
            case 'settings':
                return true;
            case 'claim':
                return (($id = $context->getRequest()->asInt('claim_id'))
                    && ($context->claim = $this->getModel('Claim')->fetchById($id)));
        }
    }

    public function systemGetAdminRouteTitle(Sabai_Context $context, $path, $title, $titleType, array $route)
    {
        switch ($path) {
            case 'claims':
                return __('Listing Claims', 'sabai-directory');
            case 'claim':
                return $context->claim->getLabel();
            case 'settings':
                return $titleType === Sabai::ROUTE_TITLE_TAB_DEFAULT ? __('General', 'sabai-directory') : sprintf(_x('%s Settings', 'Settings page title', 'sabai-directory'), $this->_name);
            case 'settings_acl':
                return __('Access Control', 'sabai-directory');
            case 'settings_emails':
                return __('Emails', 'sabai-directory');
        }
    }

    /* End implementation of Sabai_Addon_System_IAdminRouter */
    
    /* Start implementation of Sabai_Addon_Content_IContentTypes */

    public function contentGetContentTypeNames()
    {
        return array($this->_listingBundleName, $this->_reviewBundleName, $this->_photoBundleName, $this->_leadBundleName);
    }

    public function contentGetContentType($name)
    {
        require_once $this->_path . '/ContentType.php';
        return new Sabai_Addon_Directory_ContentType($this, $name);
    }

    /* End implementation of Sabai_Addon_Content_IContentTypes */
    
    public function getDefaultConfig()
    {
        return array(
            'display' => array(
                'perpage' => 20,
                'review_perpage' => 10,
                'bookmark_perpage' => 10,
                'sort' => 'newest',
                'review_sort' => 'helpfulness',
                'photo_sort' => 'votes',
                'bookmark_sort' => 'added',
                'view' => 'list',
                'f_tabs' => array(
                    'categories' => true,
                    'reviews' => true,
                    'photos' => true,
                ),
                'grid_columns' => 4,
                'listing_default_tab' => 'reviews',
                'listing_tabs' => null,
                'category_columns' => 2,
                'no_photo_comments' => false,
                'stick_featured' => true,
                'buttons' => array(
                    'search' => 'sabai-btn-primary',
                    'listing' => 'sabai-btn-success',
                    'review' => 'sabai-btn-success',
                    'photos' => 'sabai-btn-success',
                    'directions' => 'sabai-btn-primary',
                ),
            ),
            'map' => array(
                'disable' => false,
                'height' => 500,
                'list_show' => true,
                'list_height' => 400,
                'icon' => null,
                'style' => '',
                'listing_default_zoom' => 15,
                'distance_mode' => 'km',
                'options' => array('marker_clusters' => true, 'scrollwheel' => false),
            ),
            'search' => array(
                'min_keyword_len' => 3,
                'no_loc' => false,
                'country' => null,
            ),
            'photo' => array(
                'max_file_size' => 1024,
                'max_num' => 1,
                'max_num_owner' => 10,
                'max_num_photos' => 10,
                'max_num_review' => 12,
            ),
            'claims' => array(
                'duration' => 365,
                'grace_period' => 7,
                'no_comment' => false,
                'tac' => array('type' => 'none', 'required' => true),
                'allow_existing' => true,
            ),
            'spam' => array(
                'threshold' => array('listing' => 30, 'review' => 15, 'photo' => 15),
                'auto_delete' => true,
                'delete_after' => 7,
            ),
            'listing_name' => strtolower($this->_name) . '_listing',
            'pages' => array(
                'directory_slug' => strtolower($this->_name),
                'directory_title' => $this->_name,
                'dashboard_slug' => 'directory-dashboard',
                'dashboard_title' => __('Directory Dashboard', 'sabai-directory'),
                'dashboard_nolink' => false,
                'listing_slug' => 'listing',
            ),
        );
    }
    
    public function getListingDefaultTabs()
    {
        $tabs = array(
            'reviews' => __('Reviews', 'sabai-directory'),
            'photos' => __('Photos', 'sabai-directory'),
            'map' => __('Map', 'sabai-directory'),
            'contact' => __('Contact Us', 'sabai-directory'),
            'sample' => __('Custom Tab Sample', 'sabai-directory'),
        );
        $path = $this->_application->Entity_Bundle($this->_listingBundleName)->info['permalink_path'] . '/:slug/';
        $routes = $this->_application->getModel('Route', 'System')
            ->type_is(Sabai::ROUTE_INLINE_TAB)
            ->path_startsWith($path)
            ->fetch(0, 0, 'weight', 'ASC');
        foreach ($routes as $route) {
            $tab_name = str_replace($path, '', $route->path);
            if (isset($tabs[$tab_name])
                || strpos($tab_name, '/')
            ) {
                continue;
            }
            $tabs[$tab_name] = $this->_application->Translate($route->title);
        }
        return $tabs;
    }
    
    /* Start implmentation of Sabai_Addon_Widgets_IWidgets */
    
    public function widgetsGetWidgetNames()
    {
        if ($this->hasParent()) {
            return array();
        }
        return array('directory_recent', 'directory_recent_reviews', 'directory_categories', 'directory_recent_photos',
            'directory_submitbtn', 'directory_featured', 'directory_related'
        );
    }
    
    public function widgetsGetWidget($name)
    {
        require_once $this->_path . '/Widget.php';
        return new Sabai_Addon_Directory_Widget($this, substr($name, strlen($this->_name . '_')));
    }
    
    /* End implmentation of Sabai_Addon_Widgets_IWidgets */
    
    /* Start implmentation of Sabai_Addon_System_IUserMenus */
    
    public function systemGetUserMenus()
    {
        if ($this->hasParent() || !empty($this->_config['pages']['dashboard_nolink'])) {
            return array();
        }
        return array(
            strtolower($this->_name) => array(
                'title' => $this->getDashboardPageTitle(),
                'url' => $this->_application->MainUrl('/' . $this->getDashboardSlug()),
            ),
        );
    }
    
    public function systemGetUserProfileMenus(Sabai_UserIdentity $identity)
    {
        return array();
    }
    
    /* End implmentation of Sabai_Addon_System_IUserMenus */
    
    /* Start implmentation of Sabai_Addon_System_IAdminMenus */
    
    public function systemGetAdminMenus()
    {
        $icon_path = str_replace($this->_application->getPlatform()->getSiteUrl() . '/', '', $this->_application->getPlatform()->getAssetsUrl('sabai-directory'));
        return array(
            '/' . $this->getDirectorySlug() => array(
                'label' => $this->_name,
                'title' => __('Listings', 'sabai-directory'),
                'icon' => $icon_path . '/images/icon.png',
                'icon_dark' => $icon_path . '/images/icon_dark.png',
            ),
            '/' . $this->getDirectorySlug() . '/add' => array(
                'title' => __('Add Listing', 'sabai-directory'),
                'parent' => '/' . $this->getDirectorySlug(),
            ),
            '/' . $this->getDirectorySlug() . '/' . $this->getSlug('reviews') => array(
                'title' => __('Reviews', 'sabai-directory'),
                'parent' => '/' . $this->getDirectorySlug(),
            ),
            '/' . $this->getDirectorySlug() . '/' . $this->getSlug('photos') => array(
                'title' => __('Photos', 'sabai-directory'),
                'parent' => '/' . $this->getDirectorySlug(),
            ),
            '/' . $this->getDirectorySlug() . '/' . $this->getSlug('leads') => array(
                'title' => __('Leads', 'sabai-directory'),
                'parent' => '/' . $this->getDirectorySlug(),
            ),
            '/' . $this->getDirectorySlug() . '/' . $this->getSlug('categories') => array(
                'title' => __('Categories', 'sabai-directory'),
                'parent' => '/' . $this->getDirectorySlug(),
            ),
            '/' . $this->getDirectorySlug() . '/settings' => array(
                'title' => __('Settings', 'sabai-directory'),
                'parent' => '/' . $this->getDirectorySlug(),
                'weight' => 99,
            ),
        );
    }
    
    /* End implmentation of Sabai_Addon_System_IAdminMenus */
    
    /* Start implementation of Sabai_Addon_Taxonomy_ITaxonomies */

    public function taxonomyGetTaxonomyNames()
    {
        return array($this->_categoryBundleName);
    }

    public function taxonomyGetTaxonomy($name)
    {
        require_once $this->_path . '/Taxonomy.php';
        return new Sabai_Addon_Directory_Taxonomy($this, $name);
    }

    /* End implementation of Sabai_Addon_Taxonomy_ITaxonomies */
    
    /* Start implementation of Sabai_Addon_Field_ITypes */

    public function fieldGetTypeNames()
    {
        return array('directory_contact', 'directory_rating', 'directory_social', 'directory_claim', 'directory_photo');
    }

    public function fieldGetType($name)
    {
        require_once $this->_path . '/FieldType.php';
        return new Sabai_Addon_Directory_FieldType($this, $name);
    }

    /* End implementation of Sabai_Addon_Field_ITypes */
        
    /* Start implementation of Sabai_Addon_Field_IWidgets */

    public function fieldGetWidgetNames()
    {
        return array('directory_contact', 'directory_rating', 'directory_social', 'directory_claim');
    }

    public function fieldGetWidget($name)
    {
        require_once $this->_path . '/FieldWidget.php';
        return new Sabai_Addon_Directory_FieldWidget($this, $name);
    }

    /* End implementation of Sabai_Addon_Field_IWidgets */
    
    public function isInstallable($version)
    {
        return parent::isInstallable($version) && $this->_application->CheckAddonVersion('GoogleMaps');
    }
    
    public function isUpgradeable($currentVersion, $newVersion)
    {
        if (!parent::isUpgradeable($currentVersion, $newVersion)) {
            return false;
        }        
        return true;
    }
            
    public function hasSettingsPage($currentVersion)
    {
        return '/' . $this->getDirectorySlug() . '/settings';
    }
    
    public function onEntityRenderEntities($bundle, $entities, $displayMode)
    {
        if ($bundle->entitytype_name !== 'content') {
            return;
        }
        
        if ($bundle->name === $this->_listingBundleName) {
            // Fetch listing photos
            $photos = $this->_application->Entity_Query()
                ->propertyIs('post_entity_bundle_name', $this->_photoBundleName)
                ->propertyIs('post_status', Sabai_Addon_Content::POST_STATUS_PUBLISHED)
                ->fieldIsIn('content_parent', array_keys($entities))
                ->fieldIsNotNull('directory_photo', 'official') // official photos
                ->sortByField('directory_photo', 'ASC', 'display_order')
                ->fetch();
            foreach ($photos as $photo) {
                if ($listing = $photo->getSingleFieldValue('content_parent')) {
                    $entities[$listing->getId()]->data['directory_photos'][] = $photo;
                }
            }
        } elseif ($bundle->name === $this->_reviewBundleName) {
            // Fetch listing photos
            $photos = $this->_application->Entity_Query()
                ->propertyIs('post_entity_bundle_name', $this->_photoBundleName)
                ->propertyIs('post_status', Sabai_Addon_Content::POST_STATUS_PUBLISHED)
                ->fieldIsIn('content_reference', array_keys($entities))
                ->sortByProperty('post_id', 'ASC')
                ->fetch();
            foreach ($photos as $photo) {
                if ($review = $photo->getSingleFieldValue('content_reference')) {
                    $entities[$review->getId()]->data['directory_photos'][] = $photo;
                }
            }
            
            // Load listings for each review if in summary display mode
            if ($displayMode !== 'full') {
                $listing_ids = $review_ids = array();
                foreach ($entities as $entity) {
                    if ($listing = $entity->getSingleFieldValue('content_parent')) {
                        $listing_ids[] = $listing->getId();
                        $review_ids[$listing->getId()][] = $entity->getId();
                    }
                }
                if (!empty($listing_ids)) {
                    // Fetch listing photos
                    $listing_photos = $this->_application->Entity_Query()
                        ->propertyIs('post_entity_bundle_name', $this->_photoBundleName)
                        ->propertyIs('post_status', Sabai_Addon_Content::POST_STATUS_PUBLISHED)
                        ->fieldIsIn('content_parent', $listing_ids)
                        ->fieldIsNotNull('directory_photo', 'official') // official photos
                        ->sortByField('directory_photo', 'ASC', 'display_order')
                        ->fetch();
                    foreach ($listing_photos as $photo) {
                        if ($listing = $photo->getSingleFieldValue('content_parent')) {
                            foreach ($review_ids[$listing->getId()] as $review_id) {
                                $entities[$review_id]->data['directory_listing_photos'][] = $photo;
                            }
                        }
                    }
                }
            }
        }
    }
    
    public function onEntityRenderContentDirectoryListingHtml($bundle, $entity, $displayMode, $id, &$classes, &$links)
    {
        if ($bundle->name !== $this->_listingBundleName) return;
        
        if ($entity->isFeatured()) {
            $classes[] = 'sabai-directory-listing-featured';
        }
        $claims = $entity->getFieldValue('directory_claim');
        $is_claimed = !empty($claims);
        $user = $this->_application->getUser();
        
        if ($is_claimed) {
            $entity->data['content_labels']['claimed'] = array(
                'label' => __('Verified', 'sabai-directory'),
                'title' => __('This is an owner verified listing.', 'sabai-directory'),
                'class' => 'sabai-directory-listing-claimed',
                'icon' => 'ok',
            );
            // Add title icon
            $entity->data['content_icons']['claimed'] = array(
                'icon' => 'ok-sign',
                'class' => 'sabai-directory-listing-claimed',
                'title' => __('This is an owner verified listing.', 'sabai-directory'),
            );
            $classes[] = 'sabai-directory-listing-claimed';
            if ($displayMode === 'full') {
                // Add link to edit my listing page if the user has claimed this listing
                if (!empty($claims[$user->id]) && (empty($claims[$user->id]['expires_at']) || $claims[$user->id]['expires_at'] > time())) {
                    $links['edit'] = $this->_application->LinkTo(__('Edit', 'sabai-directory'), $this->_application->Url('/'. $this->getDashboardSlug() .'/' . $entity->getId() . '/edit'), array('icon' => 'edit'), array('title' => sprintf(__('Edit this %s', 'sabai-directory'), $this->_application->Entity_BundleLabel($bundle, true))));
                    $links['photo'] = $this->_application->LinkTo(__('Add Photos', 'sabai-directory'), $this->_application->Url('/'. $this->getDashboardSlug() .'/' . $entity->getId() . '/upload_photos'), array('icon' => 'camera'), array('title' => __('Add frontpage photos', 'sabai-directory')));
                }
            }
            
            return;
        }
        
        if ($displayMode === 'full') {
            if (!isset($this->_config['claims']['allow_existing']) || $this->_config['claims']['allow_existing']) {
                if ($user->isAnonymous() || $this->_application->HasPermission($this->_listingBundleName . '_claim')) {
                    $links['claim'] = $this->_application->LinkTo(__('Claim', 'sabai-directory'), $this->_application->Entity_Url($entity, '/' . $this->getSlug('claim')), array('icon' => 'check'), array('title' => sprintf(__('Claim this %s', 'sabai-directory'), $this->_application->Entity_BundleLabel($bundle, true))));
                }
            }
            if ($this->_application->HasPermission($this->_listingBundleName . '_edit_any')
                || ($this->_application->HasPermission($this->_listingBundleName . '_edit_own') && $this->_application->Content_IsAuthor($entity, $user))
            ) {
                $links['edit'] = $this->_application->LinkTo(__('Edit', 'sabai-directory'), $this->_application->Entity_Url($entity, '/edit'), array('icon' => 'edit'), array('title' => sprintf(__('Edit this %s', 'sabai-directory'), $this->_application->Entity_BundleLabel($bundle, true))));
            }
            if ($this->_application->HasPermission($this->_listingBundleName . '_manage')
                || ($this->_application->HasPermission($this->_listingBundleName . '_trash_own') && $this->_application->Content_IsAuthor($entity, $user))
            ) {
                $links['delete'] = $this->_application->LinkToModal(__('Delete', 'sabai-directory'), $this->_application->Entity_Url($entity, '/delete'), array('icon' => 'trash', 'width' => 470), array('title' => sprintf(__('Delete this %s', 'sabai-directory'), $this->_application->Entity_BundleLabel($bundle, true))));
            }
        }
    }
    
    public function onEntityRenderContentDirectoryListingReviewHtml($bundle, $entity, $displayMode, $id, &$classes, &$links)
    {
        if ($displayMode === 'preview') return;

        if ($entity->isFeatured()) {
            $classes[] = 'sabai-directory-listing-featured';
        }
        
        if ($displayMode === 'full') {
            $user = $this->_application->getUser();
            $bundle_label_singular = $this->_application->Entity_BundleLabel($bundle, true);
            $can_manage = $this->_application->HasPermission($this->_reviewBundleName . '_manage');
            if ($can_manage) {
                $links['edit'] = $this->_application->LinkTo(__('Edit', 'sabai-directory'), $this->_application->Entity_Url($entity, '/edit'), array('icon' => 'edit'), array('title' => sprintf(__('Edit this %s', 'sabai-directory'), $bundle_label_singular)));
                $links['delete'] = $this->_application->LinkToModal(__('Delete', 'sabai-directory'), $this->_application->Entity_Url($entity, '/delete', array('delete_target_id' => $id)), array('width' => 470, 'icon' => 'trash'), array('title' => sprintf(__('Delete this %s', 'sabai-directory'), $bundle_label_singular)));
            } else { 
                $is_author = $this->_application->Content_IsAuthor($entity, $user);
                if ($this->_application->HasPermission($this->_reviewBundleName . '_edit_any')
                    || ($is_author && $this->_application->HasPermission($this->_reviewBundleName . '_edit_own'))
                ) {
                    $links['edit'] = $this->_application->LinkTo(__('Edit', 'sabai-directory'), $this->_application->Entity_Url($entity, '/edit'), array('icon' => 'edit'), array('title' => sprintf(__('Edit this %s', 'sabai-directory'), $bundle_label_singular)));
                }
                if ($this->isReviewTrashable($entity, $user)) {
                    $links['delete'] = $this->_application->LinkToModal(__('Delete', 'sabai-directory'), $this->_application->Entity_Url($entity, '/delete', array('delete_target_id' => $id)), array('width' => 470, 'icon' => 'trash'), array('title' => sprintf(__('Delete this %s', 'sabai-directory'), $bundle_label_singular)));
                }
            }
        }
    }
    
    public function onEntityRenderContentDirectoryListingPhotoHtml($bundle, $entity, $displayMode, $id, &$classes, &$links)
    {
        if ($bundle->name !== $this->_photoBundleName) return;
        
        if ($entity->directory_photo[0]['official'] == 1) {
            $entity->data['content_labels']['official'] = array(
                'label' => __('Verified', 'sabai-directory'),
                'title' => $title = __('This is a photo uploaded by the listing owner.', 'sabai-directory'),
                'icon' => 'ok',
                'class' => 'sabai-directory-listing-claimed',
            );
            // Add title icon
            $entity->data['content_icons']['official'] = array(
                'icon' => 'ok-sign',
                'class' => 'sabai-directory-listing-claimed',
                'title' => $title,
            );
            $classes[] = 'sabai-directory-photo-official';
        } else {
            if ($this->_application->HasPermission($this->_photoBundleName . '_manage')) {
                $links['delete'] = $this->_application->LinkToModal(__('Delete', 'sabai-directory'), $this->_application->Entity_Url($entity, '/delete'), array('width' => 470, 'icon' => 'trash'), array('title' => sprintf(__('Delete this %s', 'sabai-directory'), $this->_application->Entity_BundleLabel($bundle, true))));
            }
        }
    }
    
    public function onEntityCreateContentDirectoryListingEntity($bundle, &$values)
    {
        if ($bundle->name !== $this->_listingBundleName) return;
        
        // Initialize review count
        $values['content_children_count'][] = array('value' => 0, 'child_bundle_name' => 'directory_listing_review');
    }
    
    public function onFormBuildContentAdminListposts(&$form, &$storage)
    {
        if ($form['#bundle']->name !== $this->_listingBundleName) {
            return;
        }
        $form['entities']['#header']['reviews'] = array(
            'order' => 12,
            'label' => __('Reviews', 'sabai-directory'),
        );
        $form['entities']['#header']['photos'] = array(
            'order' => 14,
            'label' => '<i class="sabai-icon sabai-icon-camera" title="'. __('Photos', 'sabai-directory') .'">',
        );
        $form['entities']['#header']['leads'] = array(
            'order' => 13,
            'label' => __('Leads', 'sabai-directory'),
        );
        $form['entities']['#header']['owner'] = array(
            'order' => 2,
            'label' => __('Owner', 'sabai-directory'),
        );
    
        if (!empty($form['entities']['#options'])) {
            $pending_counts = array();
            foreach (array($this->_reviewBundleName, $this->_photoBundleName, $this->_leadBundleName) as $bundle_name) {
                $pending_counts[$bundle_name] = $this->_application->Entity_Query('content')
                    ->propertyIs('post_entity_bundle_name', $bundle_name)
                    ->propertyIsIn('post_status', array(Sabai_Addon_Content::POST_STATUS_DRAFT, Sabai_Addon_Content::POST_STATUS_PENDING))
                    ->fieldIsIn('content_parent', array_keys($form['entities']['#options']))
                    ->groupByField('content_parent')
                    ->count();
            }
            $form['entities']['#header']['author']['order'] = 3;
            foreach ($form['entities']['#options'] as $entity_id => $data) {
                $entity = $data['#entity'];
                $icons = array();
                if ($claims = $entity->getFieldValue('directory_claim')) {
                    $owner_user_id = array_pop(array_keys($claims));
                    $icons[] = '<i class="sabai-content-icon sabai-directory-listing-claimed sabai-icon-ok-sign"></i>';
                    $form['entities']['#options'][$entity_id]['owner'] = $this->_application->UserIdentityLink($this->_application->UserIdentity($owner_user_id));
                }
                if ($entity->isFeatured()) {
                    $icons[] = '<i class="sabai-content-icon sabai-content-featured sabai-icon-certificate"></i>';
                }
                $form['entities']['#options'][$entity_id]['title'] = '<span class="sabai-directory-icons">' . implode(PHP_EOL, $icons) . '</span> ' . $form['entities']['#options'][$entity_id]['title'];
                $review_count = (int)$entity->getSingleFieldValue('content_children_count', 'directory_listing_review');
                $photo_count = (int)$entity->getSingleFieldValue('content_children_count', 'directory_listing_photo');
                $lead_count = (int)$entity->getSingleFieldValue('content_children_count', 'directory_listing_lead');
                $form['entities']['#options'][$entity_id] += array(
                    'reviews' => $this->_application->LinkTo(
                        empty($pending_counts[$this->_reviewBundleName][$entity_id]) ? $review_count : sprintf('%d (%d)', $review_count, $pending_counts[$this->_reviewBundleName][$entity_id]),
                        $this->_application->Url('/' . $this->getDirectorySlug() . '/' . $this->getSlug('reviews'), array('content_parent' => $entity_id))
                    ),
                    'photos' => $this->_application->LinkTo(
                        empty($pending_counts[$this->_photoBundleName][$entity_id]) ? $photo_count : sprintf('%d (%d)', $photo_count, $pending_counts[$this->_photoBundleName][$entity_id]),
                        $this->_application->Url('/' . $this->getDirectorySlug() . '/' . $this->getSlug('photos'), array('content_parent' => $entity_id))
                    ),
                    'leads' => $this->_application->LinkTo(
                        empty($pending_counts[$this->_leadBundleName][$entity_id]) ? $lead_count : sprintf('%d (%d)', $lead_count, $pending_counts[$this->_leadBundleName][$entity_id]),
                        $this->_application->Url('/' . $this->getDirectorySlug() . '/' . $this->getSlug('leads'), array('content_parent' => $entity_id))
                    ),
                );
            }
        }
        //unset($form['entities']['#header']['rating']);
        $this->_addAdminListPostsFormHeader($form);
            
        $form['#filters']['directory_claim'] = array(
            'order' => 5,
            'default_option_label' => sprintf(__('Claimed / Unclaimed', 'sabai-directory')),
            'options' => array(1 => __('Claimed', 'sabai-directory'), 2 => __('Unclaimed', 'sabai-directory'), 3 => __('Claim expired', 'sabai-directory'), 4 => __('Claim expiring (7 days)', 'sabai-directory'), 5 => __('Claim expiring (30 days)', 'sabai-directory')),
        );
        $form['#filters']['directory_featured'] = array(
            'order' => 6,
            'default_option_label' => sprintf(__('Featured / Unfeatured', 'sabai-directory')),
            'options' => array(1 => __('Featured', 'sabai-directory'), 2 => __('Unfeatured', 'sabai-directory')),
        );
    }
    
    public function onFormBuildTaxonomyAdminListterms(&$form, &$storage)
    {
        if ($form['#bundle']->name !== $this->_categoryBundleName) {
            return;
        }
        $form['entities']['#header']['icon'] = array(
            'order' => 1,
            'label' => '',
        );
        $form['entities']['#row_attributes']['@all']['icon']['style'] = 'text-align:center;';
        if (!empty($form['entities']['#options'])) {
            foreach ($form['entities']['#options'] as $entity_id => $data) {
                $entity = $data['#entity'];
                
                if (!$entity->directory_map_marker) continue;
                
                $form['entities']['#options'][$entity_id]['icon'] = '<img src="' . $this->_application->File_ThumbnailUrl($entity->directory_map_marker[0]['name']) . '" alt="" />';
            }
        }
    }
    
    public function onContentAdminPostsUrlParamsFilter(&$urlParams, $context, $bundle)
    {
        if ($bundle->name === $this->_listingBundleName) {
            if ($directory_claim = $context->getRequest()->asInt('directory_claim')){
                $urlParams['directory_claim'] = $directory_claim;
            }
            if ($directory_featured = $context->getRequest()->asInt('directory_featured')){
                $urlParams['directory_featured'] = $directory_featured;
            }
        } elseif ($bundle->name === $this->_photoBundleName) {
            if ($directory_photos = $context->getRequest()->asInt('directory_photos')){
                $urlParams['directory_photos'] = $directory_photos;
            }
        }
    }
    
    public function onContentAdminPostsQuery($context, $bundle, $query, $countQuery, $sort, $order)
    {
        if ($bundle->name === $this->_listingBundleName) {
            if ($directory_claim = $context->getRequest()->asInt('directory_claim')){
                switch ($directory_claim) {
                    case 1:
                        $query->startCriteriaGroup('OR')
                            ->fieldIsGreaterThan('directory_claim', time(), 'expires_at')
                            ->fieldIs('directory_claim', 0, 'expires_at')
                            ->finishCriteriaGroup();
                        $countQuery->startCriteriaGroup('OR')
                            ->fieldIsGreaterThan('directory_claim', time(), 'expires_at')
                            ->fieldIs('directory_claim', 0, 'expires_at')
                            ->finishCriteriaGroup();
                    break;
                    case 2:
                        $query->fieldIsNull('directory_claim', 'expires_at');
                        $countQuery->fieldIsNull('directory_claim', 'expires_at');
                    break;
                    case 3:
                        $query->fieldIsOrSmallerThan('directory_claim', time(), 'expires_at')
                            ->fieldIsNot('directory_claim', 0, 'expires_at');
                        $countQuery->fieldIsOrSmallerThan('directory_claim', time(), 'expires_at')
                            ->fieldIsNot('directory_claim', 0, 'expires_at');
                    break;
                    case 4:
                        $query->fieldIsOrSmallerThan('directory_claim', time() + 86400 * 7, 'expires_at')
                            ->fieldIsNot('directory_claim', 0, 'expires_at');
                        $countQuery->fieldIsOrSmallerThan('directory_claim', time() + 86400 * 7, 'expires_at')
                            ->fieldIsNot('directory_claim', 0, 'expires_at');
                    break;
                    case 5:
                        $query->fieldIsOrSmallerThan('directory_claim', time() + 86400 * 30, 'expires_at')
                            ->fieldIsNot('directory_claim', 0, 'expires_at');
                        $countQuery->fieldIsOrSmallerThan('directory_claim', time() + 86400 * 30, 'expires_at')
                            ->fieldIsNot('directory_claim', 0, 'expires_at');
                    break;
                }
            }
            if ($directory_featured = $context->getRequest()->asInt('directory_featured')){
                switch ($directory_featured) {
                    case 1:
                        $query->fieldIs('content_featured', 1);
                        $countQuery->fieldIs('content_featured', 1);
                    break;
                    case 2:
                        $query->fieldIsNull('content_featured');
                        $countQuery->fieldIsNull('content_featured');
                    break;
                }
            }
        } elseif ($bundle->name === $this->_photoBundleName) {
            if ($directory_photos = $context->getRequest()->asInt('directory_photos')){
                switch ($directory_photos) {
                    case 1:
                        $query->fieldIs('directory_photo', 1, 'official');
                        $countQuery->fieldIs('directory_photo', 1, 'official');
                    break;
                    case 2:
                        $query->fieldIsNotNull('content_reference');
                        $countQuery->fieldIsNotNull('content_reference');
                    break;
                }
            }
        }
    }
    
    public function onFormBuildContentAdminListchildposts(&$form, &$storage)
    {
        if ($form['#bundle']->name === $this->_reviewBundleName) {
            $form['entities']['#header']['photos'] = array(
                'order' => 14,
                'label' => '<i class="sabai-icon sabai-icon-camera" title="'. __('Photos', 'sabai-directory') .'">',
            );
            if (!empty($form['entities']['#options'])) {
                foreach (array(Sabai_Addon_Content::POST_STATUS_DRAFT, Sabai_Addon_Content::POST_STATUS_PENDING, Sabai_Addon_Content::POST_STATUS_PUBLISHED) as $status) {
                    $photo_counts[$status] = $this->_application->Entity_Query('content')
                        ->propertyIs('post_entity_bundle_name', $this->_photoBundleName)
                        ->propertyIs('post_status', $status)
                        ->fieldIsIn('content_reference', array_keys($form['entities']['#options']))
                        ->groupByField('content_reference')
                        ->count();
                }
                foreach ($form['entities']['#options'] as $entity_id => $data) {
                    $entity = $data['#entity'];
                    $icons = array();
                    $icons[] = '<span class="sabai-rating sabai-rating-' . $entity->directory_rating[0] * 10 . '"></span>';
                    $form['entities']['#options'][$entity_id]['title'] = '<span class="sabai-directory-icons">' . implode(PHP_EOL, $icons) . '</span> ' . $form['entities']['#options'][$entity_id]['title'];
                    $photo_count = (int)@$photo_counts[Sabai_Addon_Content::POST_STATUS_PUBLISHED][$entity_id];
                    $pending_photo_count = (int)@$photo_counts[Sabai_Addon_Content::POST_STATUS_PENDING][$entity_id] + (int)@$photo_counts[Sabai_Addon_Content::POST_STATUS_DRAFT][$entity_id];
                    $form['entities']['#options'][$entity_id] += array(
                        'photos' => $this->_application->LinkTo(
                            empty($pending_photo_count) ? $photo_count : sprintf('%d (%d)', $photo_count, $pending_photo_count),
                            $this->_application->Url('/' . $this->getDirectorySlug() . '/' . $this->getSlug('photos'), array('content_reference' => $entity_id))
                        ),
                    );
                }
            }
            $this->_addAdminListPostsFormHeader($form);
        } elseif ($form['#bundle']->name === $this->_photoBundleName) {
            $form['entities']['#header']['thumbnail'] = array(
                'order' => 0,
                'label' => '',
            );
            $form['entities']['#header']['review'] = array(
                'order' => 12,
                'label' => __('Review', 'sabai-directory'),
            );
            foreach ($form['entities']['#options'] as $entity_id => $data) {
                $entity = $data['#entity'];
                if (isset($entity->directory_photo[0]['official']) && $entity->directory_photo[0]['official'] == 1) {
                    $form['entities']['#options'][$entity_id]['title'] = '<i class="sabai-content-icon sabai-directory-listing-claimed sabai-icon-ok-sign"></i> ' . $form['entities']['#options'][$entity_id]['title'];
                }
                $form['entities']['#options'][$entity_id]['thumbnail'] = sprintf('<img src="%s" alt="" width="60" height="60" />', $this->_application->Directory_PhotoUrl($entity, 'thumbnail'));
                if ($entity->content_reference) {
                    $review = array_shift($entity->content_reference);
                    $form['entities']['#options'][$entity_id]['review'] = $this->_application->LinkTo(
                        mb_strimwidth($review->getTitle(), 0, 70, '...'),
                        $this->_application->Url($form['#bundle']->getPath(), array('content_reference' => $review->getId()))
                    );
                }
            }
            $form['#filters']['directory_photos'] = array(
                'order' => 5,
                'default_option_label' => sprintf(__('All photos', 'sabai-directory')),
                'options' => array(1 => __('Official photos', 'sabai-directory'), 2 => __('Review photos', 'sabai-directory')),
            );
            $form[Sabai_Addon_Form::FORM_SUBMIT_BUTTON_NAME]['action']['#options'] += array(
                'mark_official' => __('Mark Official', 'sabai-directory'),
                'unmark_official' => __('Unmark Official', 'sabai-directory'),
            );
            $form['#submit'][0][] = array($this, 'updateEntities');
            $this->_addAdminListPostsFormHeader($form);
        } elseif ($form['#bundle']->name === $this->_leadBundleName) {
            $form['entities']['#header']['title'] = array(
                'label' => __('Message', 'sabai-directory'),
                'order' => 1,
            );
            foreach ($form['entities']['#options'] as $entity_id => $data) {
                $entity = $data['#entity'];
                $form['entities']['#options'][$entity_id]['title'] = Sabai::h($entity->getSummary(200)) . '<div class="sabai-row-action">' . $this->_application->Menu($data['#links']) . '</div>';
            }
        }
    }
    
    public function updateEntities(Sabai_Addon_Form_Form $form)
    {
        if (empty($form->values['entities'])) return;
        
        switch ($form->values['action']) {
            case 'mark_official':
                foreach ($this->_application->Entity_Entities('content', $form->values['entities']) as $entity) {
                    if ($entity->getSingleFieldValue('directory_photo', 'official') == 1 // Already marked as official
                        || $entity->getSingleFieldValue('entity_reference') // Review photos may not become official 
                    ) {
                        continue;
                    }
                    $this->_application->getAddon('Entity')->updateEntity($entity, array('directory_photo' => array('official' => 1, 'display_order' => 99)));
                }
                break;
            case 'unmark_official':
                foreach ($this->_application->Entity_Entities('content', $form->values['entities']) as $entity) {
                    if (!$entity->getSingleFieldValue('directory_photo', 'official')) { // Not marked as official
                        continue;
                    }
                    $this->_application->getAddon('Entity')->updateEntity($entity, array('directory_photo' => false));
                }
                break;
        }
    }
    
    private function _addAdminListPostsFormHeader(&$form)
    {
        if ($form['#status'] === 'trashed') {
            $form['#header'] = array(
                '<p>' . sprintf(__('Posts <span class="sabai-tr-error">highlighted</span> are marked as SPAM.', 'sabai-directory')) . '</p>'
            );
        }
    }
    
    public function onVotingDirectoryListingEntityVotedFlag(Sabai_Addon_Entity_IEntity $entity, $results, $vote)
    {
        if ($entity->getBundleName() !== $this->_listingBundleName) return;
        
        $this->_application->Directory_SendListingNotification('flagged', $entity, null, array('{flag_score_total}' => (int)$results['sum']) + $this->_application->Voting_TemplateTags($vote));
        $this->_trashPostIfSpam($entity, $results);
    }
    
    public function onVotingDirectoryListingReviewEntityVotedFlag(Sabai_Addon_Entity_IEntity $entity, $results, $vote)
    {
        if ($entity->getBundleName() !== $this->_reviewBundleName) return;
        
        $this->_application->Directory_SendReviewNotification('flagged', $entity, null, array('{flag_score_total}' => (int)$results['sum']) + $this->_application->Voting_TemplateTags($vote));
        $this->_trashPostIfSpam($entity, $results);
    }
    
    public function onVotingDirectoryListingPhotoEntityVotedFlag(Sabai_Addon_Entity_IEntity $entity, $results, $vote)
    {
        if ($entity->getBundleName() !== $this->_photoBundleName) return;
        
        $this->_application->Directory_SendPhotoNotification('flagged', $entity, null, array('{flag_score_total}' => (int)$results['sum']) + $this->_application->Voting_TemplateTags($vote));
        $this->_trashPostIfSpam($entity, $results);
    }
    
    private function _trashPostIfSpam(Sabai_Addon_Entity_IEntity $entity, $results)
    {
        if ($entity->isTrashed()) return; // trashed posts can not be flagged, but just in case

        // Has the spam score reached the threshold?
        switch ($entity->getBundleName()) {
            case $this->_listingBundleName:
                $threshold = $this->_config['spam']['threshold']['listing'] + 0.3 * (int)$entity->getSingleFieldValue('voting_rating', 'sum');
                break;
            case $this->_reviewBundleName:
                $threshold = $this->_config['spam']['threshold']['review'] + 0.3 * (int)$entity->getSingleFieldValue('voting_helpful', 'sum');
                break;
            case $this->_photoBundleName:
                $threshold = $this->_config['spam']['threshold']['photo'] + 0.3 * (int)$entity->getSingleFieldValue('voting_helpful', 'sum');
                break;
            default:
                return;
        }
        if ($results['sum'] > $threshold) {
            // Move to trash and clear flags
            $this->_application->Content_TrashPosts($entity, Sabai_Addon_Content::TRASH_TYPE_SPAM, '', 0);
        }
    }
    
    public function onSabaiRunCron($lastRunTimestamp, $logs)
    {
        if (time() - $lastRunTimestamp < 86400) return; // Run this cron once a day

        $claims_deleted = 0;
        // Fetch claims that will expire in 7 days
        $listings = $this->_application->Entity_Query('content')
            ->propertyIs('post_entity_bundle_name', $this->_listingBundleName)
            ->propertyIs('post_status', Sabai_Addon_Content::POST_STATUS_PUBLISHED)
            ->fieldIsOrSmallerThan('directory_claim', time() + 7 * 86400, 'expires_at')
            ->fieldIsGreaterThan('directory_claim', 0, 'expires_at')
            ->fetch(); 
        foreach ($listings as $listing) {
            if (!$claims = $listing->directory_claim) {
                continue;
            }
            $update_claims = false;
            $owners = $this->_application->UserIdentities(array_keys($claims));
            // Notify listing owners
            foreach ($claims as $user_id => $claim) {
                $tags = array(
                    '{expiration_date}' => $this->_application->Date($claim['expires_at']),
                    '{expiration_date_diff}' => $this->_application->DateDiff($claim['expires_at']),
                    '{listing_renew_url}' => $this->_application->Url('/' . $this->getDashboardSlug() . '/' . $listing->getId() .'/renew'),
                );
                if ($claim['expires_at'] < time()) {
                    if ($claim['expires_at'] < time() - $this->_config['claims']['grace_period'] * 86400) { // more than X days after expiration?
                        // Delete claim
                        unset($claims[$user_id]);
                        ++$claims_deleted;
                        $update_claims = true;
                    } else {
                        $this->_application->Directory_SendListingNotification('expired', $listing, $owners[$user_id], $tags);
                    }
                } else {
                    $this->_application->Directory_SendListingNotification('expires', $listing, $owners[$user_id], $tags);
                }
            }
            if ($update_claims) {
                $this->_application->getAddon('Entity')->updateEntity($listing, array('directory_claim' => empty($claims) ? false : $claims));
            }
        }
        if ($claims_deleted > 0) {
            $logs[] = sprintf(__('Deleted %d expired listing claims', 'sabai-directory'), $claims_deleted);
        }

        if (!$this->_config['spam']['auto_delete']) {
            // Auto-delete spam not enabled
            return;
        }    
        // Fetch posts marked as spam and were trashed more than X days ago
        $days = $this->_config['spam']['delete_after'];
        $spam_posts = $this->_application->Entity_Query('content')
            ->propertyIsIn('post_entity_bundle_name', array($this->_listingBundleName, $this->_reviewBundleName, $this->_photoBundleName))
            ->propertyIs('post_status', Sabai_Addon_Content::POST_STATUS_TRASHED) // trashed posts
            ->fieldIs('content_trashed', Sabai_Addon_Content::TRASH_TYPE_SPAM, 'type') // marked as spam
            ->fieldIsOrSmallerThan('content_trashed', time() - $days * 86400, 'trashed_at') // more than X days after trashed
            ->fetch(0, 0, false);    
        if (empty($spam_posts)) {
            return;
        }
        // Delete
        $this->_application->Content_DeletePosts($spam_posts);
        $logs[] = sprintf(
            __('Deleted %d spam posts (listings and/or reviews) from trash', 'sabai-directory'),
            count($spam_posts)
        );
    }

    public function isListingTrashable($review, $user)
    {
        return $this->_application->Content_IsAuthor($review, $user)
            && $this->_application->HasPermission($this->_listingBundleName . '_trash_own');
    }

    public function isReviewTrashable($review, $user)
    {
        return $this->_application->Content_IsAuthor($review, $user)
            && $this->_application->HasPermission($this->_reviewBundleName . '_trash_own');
    }
    
    public function onContentDirectoryListingReviewPostsTrashed($bundleName, $entities)
    {
        if ($bundleName !== $this->_reviewBundleName) return;
        // Uncast listing votes
        foreach ($entities as $entity) {
            $this->_application->Voting_CastVote(
                $this->_application->Content_ParentPost($entity),
                'rating',
                $entity->getSingleFieldValue('directory_rating'),
                array('name' => '', 'user_id' => $entity->getAuthorId(), 'reference_id' => $entity->getId())
            );
        }
        // Trash associated photos
        $this->_application->Content_TrashReferencingPosts(array_keys($entities));
    }
        
    public function onContentDirectoryListingReviewPostsRestored($bundleName, $entities)
    {
        if ($bundleName !== $this->_reviewBundleName) return;
        // Re-cast listing votes
        foreach ($entities as $entity) {
            $this->_application->Voting_CastVote(
                $this->_application->Content_ParentPost($entity),
                'rating',
                $entity->getSingleFieldValue('directory_rating'),
                array('name' => '', 'user_id' => $entity->getAuthorId(), 'reference_id' => $entity->getId(), 'is_edit' => true)
            );
        }
        // Restore associated photos
        $this->_application->Content_RestoreReferencingPosts(array_keys($entities));
    }
    
    public function onEntityBulkDeleteContentDirectoryListingReviewEntitySuccess($bundle, $entities, $extra)
    {
        if ($bundle->name !== $this->_reviewBundleName) return;
        
        // Delete associated photos
        $this->_application->Content_DeleteReferencingPosts(array_keys($entities));
    }
    
    public function onSabaiWebResponseRenderHtmlLayout(Sabai_Context $context, Sabai_WebResponse $response, &$content)
    {
        if ($this->hasParent()) return;
        
        $templates = $context->getTemplates();
        if (in_array('directory_listings', $templates)
            || in_array('directory_category_single_full', $templates)
            || in_array('directory_listings_geolocate', $templates)
        ) {
            $this->_loadGoogleMaps($response);
            $this->_application->LoadJs($response, $this->_application->getPlatform()->getAssetsUrl('sabai-directory') . '/js/jquery.jsticky.js', 'jquery-jsticky', 'jquery');
            $this->_application->LoadJs($response, $this->_application->getPlatform()->getAssetsUrl('sabai-directory') . '/js/sabai-directory-googlemap.js', 'sabai-directory-googlemap', 'sabai');
            $this->_application->LoadJs($response, $this->_application->getPlatform()->getAssetsUrl('sabai-directory') . '/js/markerclusterer_packed.js', 'sabai-directory-markerclusterer', 'sabai');
            $this->_application->LoadJs($response, $this->_application->getPlatform()->getAssetsUrl('sabai-directory') . '/js/sabai-googlemaps-autocomplete.js', 'sabai-googlemaps-autocomplete', 'sabai');
        } elseif (in_array('directory_searchform', $templates)) {
            $this->_loadGoogleMaps($response);
            $this->_application->LoadJs($response, $this->_application->getPlatform()->getAssetsUrl('sabai-directory') . '/js/sabai-googlemaps-autocomplete.js', 'sabai-googlemaps-autocomplete', 'sabai');
        } elseif (in_array('directory_map', $templates)) {
            $this->_loadGoogleMaps($response);
            $this->_application->LoadJs($response, $this->_application->getPlatform()->getAssetsUrl('sabai-directory') . '/js/sabai-directory-googlemap.js', 'sabai-directory-googlemap', 'sabai');
            $this->_application->LoadJs($response, $this->_application->getPlatform()->getAssetsUrl('sabai-directory') . '/js/markerclusterer_packed.js', 'sabai-directory-markerclusterer', 'sabai');
        } elseif (in_array('directory_listings_slider', $templates)) {
            $this->_application->LoadJs($response, $this->_application->getPlatform()->getAssetsUrl('sabai-directory') . '/js/jquery.bxslider.min.js', 'jquery-bxslider', 'jquery');
            $this->_application->LoadCss($response, $this->_application->getPlatform()->getAssetsUrl('sabai-directory') . '/css/jquery.bxslider.css', 'jquery-bxslider');
        }

        // The main stylesheet should already have been included by the platform if not requesting the full page content
        if ($context->getContainer() === '#sabai-content') {
            if (!$last_update = $this->_application->getAddonsLoadedTimestamp()) {
                $last_update = time();
            }
            $css_file = $context->isAdmin() ? 'admin.css' : 'main.css';
            $this->_application->LoadCss($response, $this->_application->getPlatform()->getAssetsUrl('sabai-directory') . '/css/' . $css_file, 'sabai-directory', $last_update);
            if (!$context->isAdmin() && $this->_application->getPlatform()->isLanguageRTL()) {
                $this->_application->LoadCss($response, $this->_application->getPlatform()->getAssetsUrl('sabai-directory') . '/css/main-rtl.css', 'sabai-directory-rtl', $last_update);
            }
        }
    }
    
    private function _loadGoogleMaps(Sabai_WebResponse $response)
    {
        if (self::$_gmapLoaded) {
            return;
        }
        $js = array();
        if (!defined('SABAI_DIRECTORY_NO_GOOGLE_MAPS_API') || !SABAI_DIRECTORY_NO_GOOGLE_MAPS_API) {
            $js[] = 'google.load("maps", "3", {other_params:"sensor=false&libraries=places&language=' . $this->_application->GoogleMaps_Language(). '"});';
        }
        $js[] = sprintf(
            'google.setOnLoadCallback(function(){
    if (typeof SABAI.GoogleMaps != "undefined" && typeof SABAI.GoogleMaps.autocomplete != "undefined") {
        SABAI.GoogleMaps.autocomplete(".sabai-directory-search-location input", {%s});
    }
});',
            strlen(@$this->_config['search']['country']) ? 'componentRestrictions: {country: "' . strtolower($this->_config['search']['country']) . '"}' : ''
        );
        $response->addJs(implode(PHP_EOL, $js), false);
        self::$_gmapLoaded = true;
    }
    
    public function onEntityCreateContentDirectoryListingEntitySuccess($bundle, $entity, $values)
    {
        if ($bundle->name !== $this->_listingBundleName) return;
        
        if ($entity->isPublished()) {
            $this->_application->Directory_SendListingNotification('published', $entity);
        } else {
            $this->_application->Directory_SendListingNotification(
                'submitted_admin',
                $entity,
                null,
                array('{listing_url}' => $this->_application->AdminUrl('/' . $this->getDirectorySlug() . '/' . $entity->getId()))
            );
        }
    }
    
    public function onEntityCreateContentDirectoryListingReviewEntitySuccess($bundle, $entity, $values)
    {
        if ($bundle->name !== $this->_reviewBundleName) return;
        
        if ($entity->isPublished()) {
            // Cast vote for the parent listing
            $this->_application->Voting_CastVote(
                $this->_application->Content_ParentPost($entity, false),
                'rating',
                $entity->getSingleFieldValue('directory_rating'),
                array('name' => '', 'reference_id' => $entity->getId(), 'user_id' => $entity->getAuthorId())
            );
            $this->_application->Directory_SendReviewNotification('published', $entity);
            $this->_notifyListingOwners($entity);
        } else {
            $this->_application->Directory_SendReviewNotification(
                'submitted_admin',
                $entity,
                null,
                array('{review_url}' => $this->_application->AdminUrl('/' . $this->getDirectorySlug() . '/' . $this->getSlug('reviews') . '/' . $entity->getId()))
            );
        }
    }
    
    public function onEntityUpdateContentDirectoryListingReviewEntitySuccess($bundle, $entity, $oldEntity, $values)
    {
        if ($bundle->name !== $this->_reviewBundleName) return;

        if ($entity->isPublished()) {
            if (isset($values['directory_rating']) // rating changed
                || isset($values['content_post_status']) // review was just published
            ) {
                // Cast vote for the parent listing
                $this->_application->Voting_CastVote(
                    $this->_application->Content_ParentPost($entity, false),
                    'rating',
                    $entity->getSingleFieldValue('directory_rating'),
                    array('name' => '', 'reference_id' => $entity->getId(), 'user_id' => $entity->getAuthorId(), 'is_edit' => true)
                );
            }
        }
    }

    public function onEntityCreateContentDirectoryListingPhotoEntitySuccess($bundle, $entity, $values)
    {
        if ($bundle->name !== $this->_photoBundleName) return;
        
        if ($entity->isPublished()) {
            $this->_application->Directory_SendPhotoNotification('published', $entity);
            $this->_notifyListingOwners($entity);
        } else {
            $this->_application->Directory_SendPhotoNotification(
                'submitted_admin',
                $entity,
                null,
                array('{photo_url}' => $this->_application->AdminUrl('/' . $this->getDirectorySlug() . '/' . $this->getSlug('photos') . '/' . $entity->getId()))
            );
        }
    }

    public function onEntityCreateContentDirectoryListingLeadEntitySuccess($bundle, $entity, $values)
    {
        if ($bundle->name !== $this->_leadBundleName) return;
        
        if ($entity->isPublished()) {
            $this->_notifyListingOwners($entity);
        } else {
            $this->_application->Directory_SendLeadNotification(
                'submitted_admin',
                $entity,
                null,
                array('{lead_url}' => $this->_application->AdminUrl('/' . $this->getDirectorySlug() . '/' . $this->getSlug('leads') . '/' . $entity->getId()))
            );
        }
    }
    
    public function onEntityCreateTaxonomyDirectoryCategoryEntitySuccess($bundle, $entity, $values)
    {
        $this->_maybeDeleteCategoryMapMarkerURLsCache($bundle, $values);
    }
    
    public function onEntityUpdateTaxonomyDirectoryCategoryEntitySuccess($bundle, $entity, $oldEntity, $values)
    {
        $this->_maybeDeleteCategoryMapMarkerURLsCache($bundle, $values);
    }
    
    private function _maybeDeleteCategoryMapMarkerURLsCache($bundle, $values)
    {
        if ($bundle->name !== $this->_categoryBundleName
            || !isset($values['directory_map_marker'])
        ) return;

        // Delete cached category custom map marker URLs 
        $this->_application->getPlatform()->deleteCache($this->_categoryBundleName . '_map_marker_urls');
    }
    
    public function onContentPostPublished($entity)
    {
        if ($entity->getBundleName() === $this->_listingBundleName) {
            $this->_application->Directory_SendListingNotification(array('published', 'approved'), $entity);
        } elseif ($entity->getBundleName() === $this->_reviewBundleName) {
            $this->_application->Directory_SendReviewNotification(array('published', 'approved'), $entity);
            $this->_notifyListingOwners($entity);
            if ($listing = $this->_application->Content_ParentPost($entity)) {
                // Cast vote for the parent listing
                $this->_application->Voting_CastVote(
                    $listing,
                    'rating',
                    $entity->getSingleFieldValue('directory_rating'),
                    array('name' => '', 'reference_id' => $entity->getId(), 'user_id' => $entity->getAuthorId())
                );
            }
        } elseif ($entity->getBundleName() === $this->_photoBundleName) {
            $this->_application->Directory_SendPhotoNotification(array('published', 'approved'), $entity);
            $this->_notifyListingOwners($entity);
        } elseif ($entity->getBundleName() === $this->_leadBundleName) {
            $this->_notifyListingOwners($entity);
        }
    }

    public function onCommentSubmitCommentSuccess($comment, $isEdit, $entity)
    {
        if ($isEdit
            || $entity->getAuthorId() === $comment->user_id
        ) {
            return;
        }
        switch ($entity->getBundleName()) {
            case $this->_reviewBundleName:
                $this->_application->Directory_SendReviewNotification('commented', $entity, null, $this->_application->Comment_TemplateTags($comment));
                break;
            case $this->_photoBundleName:
                $this->_application->Directory_SendPhotoNotification('commented', $entity, null, $this->_application->Comment_TemplateTags($comment));
                break;
        }
    }
    
    private function _notifyListingOwners($entity)
    {
        // Notify listing owners
        $listing = $this->_application->Content_ParentPost($entity);
        if ($listing && ($claims = $listing->directory_claim)) {
            unset($claims[$entity->getAuthorId()]); // do not notify if owner is the poster
            if (!empty($claims)) {
                $owners = $this->_application->UserIdentities(array_keys($claims));
                if ($entity->getBundleName() === $this->_reviewBundleName) {
                    $this->_application->Directory_SendReviewNotification('added', $entity, $owners);
                } elseif ($entity->getBundleName() === $this->_photoBundleName) {
                    $this->_application->Directory_SendPhotoNotification('added', $entity, $owners);
                } elseif ($entity->getBundleName() === $this->_leadBundleName) {
                    $this->_application->Directory_SendLeadNotification('added', $entity, $owners);
                }
            }
        }
    }
    
    /* Start implmentation of Sabai_Addon_System_IMainMenus */
    
    public function systemGetMainMenus()
    {
        return array(
            '/' . $this->getDirectorySlug() => array(
                'title' => $this->getDirectoryPageTitle(),
            ),
            '/' . $this->getDashboardSlug() => array(
                'title' => $this->getDashboardPageTitle(),
            ),
        );
    }
    
    /* End implmentation of Sabai_Addon_System_IMainMenus */
    
    public function onSystemUserProfileActivityFilter(&$activity, $identity, $counts)
    {
        if ($this->hasParent()) return;
        
        // Count all non-official photos
        $counts = $this->_application->Entity_Query('content')
            ->propertyIs('post_status', Sabai_Addon_Content::POST_STATUS_PUBLISHED)
            ->propertyIs('post_user_id', $identity->id)
            ->propertyIs('post_entity_bundle_type', 'directory_listing_photo')
            ->fieldIsNull('directory_photo', 'official')
            ->groupByProperty('post_entity_bundle_name')
            ->count() + $counts;
        // Activity for this add-on
        $activity[] = array(               
            'stats' => array(
                $this->_reviewBundleName => array(
                    'url' => '/' . $this->getDirectorySlug() . '/users/' . $identity->username,
                    'format' => _n('%s review', '%s reviews', $count = isset($counts[$this->_reviewBundleName]) ? $counts[$this->_reviewBundleName] : 0, 'sabai-directory'),
                    'is_bundle' => true,
                    'count' => $count,
                ),
                $this->_photoBundleName => array(
                    'url' => '/' . $this->getDirectorySlug() . '/users/' . $identity->username . '/photos',
                    'format' => _n('%s photo', '%s photos', $count = isset($counts[$this->_photoBundleName]) ? $counts[$this->_photoBundleName] : 0, 'sabai-directory'),
                    'is_bundle' => true,
                    'count' => $count,
                ),
            ),
            'title' => $this->getDirectoryPageTitle(),
        );
        // Activity for cloned add-ons
        foreach ($this->_application->getModel('Addon', 'System')->parentAddon_is('Directory')->fetch() as $addon) {
            $directory_addon = $this->_application->getAddon($addon->name);
            $review_bundle_name = $directory_addon->getReviewBundleName();
            $photo_bundle_name = $directory_addon->getPhotoBundleName();
            $activity[] = array(               
                'stats' => array(
                    $review_bundle_name => array(
                        'url' => '/' . $directory_addon->getDirectorySlug() . '/users/' . $identity->username,
                        'format' => _n('%s review', '%s reviews', $count = isset($counts[$review_bundle_name]) ? $counts[$review_bundle_name] : 0, 'sabai-directory'),
                        'is_bundle' => true,
                        'count' => $count,
                    ),
                    $photo_bundle_name => array(
                        'url' => '/' . $directory_addon->getDirectorySlug() . '/users/' . $identity->username . '/photos',
                        'format' => _n('%s photo', '%s photos', $count = isset($counts[$photo_bundle_name]) ? $counts[$photo_bundle_name] : 0, 'sabai-directory'),
                        'is_bundle' => true,
                        'count' => $count,
                    ),
                ),
                'title' => $directory_addon->getDirectoryPageTitle(),
            );
        }
    }
        
    public function onDirectoryInstallSuccess($addon)
    {
        if ($addon->getName() !== $this->_name) return;
        
        //$this->_application->Directory_CreateSampleData($addon->getName());
    }
    
    public function onDirectoryListingClaimStatusChange($claim)
    {
        if ($claim->entity_bundle_name !== $this->_listingBundleName) {
            return;
        }
        $this->_application->Directory_SendClaimNotification($claim->status, $claim);
    }
    
    public function onSystemRoutesFilter(&$routes, $rootPath, $entityName)
    {
        if ($entityName !== 'Route' || $rootPath !== '/'. $this->getDirectorySlug()) return;
        
        $path = '/'. $this->getDirectorySlug() . '/' . $this->getConfig('pages', 'listing_slug') . '/:slug/';
        foreach ($this->_config['display']['listing_tabs']['options'] as $tab_name => $tab_label) {
            $tab_name = strtolower($tab_name);
            if (!in_array($tab_name, $this->_config['display']['listing_tabs']['default'])) {
                $routes[$path . $tab_name]['type'] = Sabai::ROUTE_NORMAL;
            } else {
                if (!isset($routes[$path . $tab_name])) {
                    // custom tab
                    $routes[$path . $tab_name] = array(
                        'path' => $path . $tab_name,
                        'addon' => $this->_name,
                        'controller_addon' => 'Directory',
                        'callback_addon' => 'Directory',
                        'callback_path' => 'listing_tab',
                        'access_callback' => true,
                        'type' => Sabai::ROUTE_INLINE_TAB,
                        'title' => $tab_label,
                        'controller' => 'ListingTab',
                        'ajax' => true,
                        'class' => '',
                        'method' => '',
                        'data' => array(),
                    );
                    $routes[rtrim($path, '/')]['routes'][$tab_name] = $path . $tab_name;
                }
                $routes[$path . $tab_name]['weight'] = array_search($tab_name, $this->_config['display']['listing_tabs']['default']);
            }
        }
    }
}
