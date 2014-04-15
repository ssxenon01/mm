<?php
class Sabai_Addon_Directory_Widget implements Sabai_Addon_Widgets_IWidget
{
    private $_addon, $_name;
    
    public function __construct(Sabai_Addon_Directory $addon, $name)
    {
        $this->_addon = $addon;
        $this->_name = $name;
    }
    
    public function widgetsWidgetGetTitle()
    {
        switch ($this->_name) {
            case 'recent':
                return __('Recent Listings', 'sabai-directory');
            case 'recent_reviews':
                return __('Recent Listing Reviews', 'sabai-directory');
            case 'recent_photos':
                return __('Recent Listing Photos', 'sabai-directory');
            case 'featured':
                return __('Featured Listings', 'sabai-directory');
            case 'related':
                return __('Related Listings', 'sabai-directory');
            case 'categories':
                return __('Listing Categories', 'sabai-directory');
            case 'submitbtn':
                return __('Add Listing', 'sabai-directory');
        }
    }
    
    public function widgetsWidgetGetSummary()
    {
        switch ($this->_name) {
            case 'recent':
                return __('Recently posted listings', 'sabai-directory');
            case 'recent_reviews':
                return __('Recently posted listing reviews', 'sabai-directory');
            case 'recent_photos':
                return __('Recently posted listing photos', 'sabai-directory');
            case 'featured':
                return __('Featured listings', 'sabai-directory');
            case 'recent':
                return __('Related listings', 'sabai-directory');
            case 'categories':
                return __('A list of categories', 'sabai-directory');
            case 'submitbtn':
                return __('A call to action button', 'sabai-directory');              
        }
    }
    
    public function widgetsWidgetGetSettings()
    {
        $settings = array(
            'no_cache' => array(
                '#type' => 'checkbox',
                '#title' => __('Do not cache output', 'sabai-directory'),
                '#default_value' => false,
                '#weight' => 99,
            ),
        );
        switch ($this->_name) {
            case 'recent':
                return $settings + array(
                    'bundle' => array(
                        '#title' => __('Select directory', 'sabai-directory'),
                        '#options' => $directory_options = array('' => __('All directories', 'sabai-directory')) + $this->_addon->getApplication()->Directory_DirectoryList(),
                        '#type' => count($directory_options) <= 1 ? 'hidden' : 'select',
                        '#default_value' => array_shift(array_keys($directory_options)),
                    ),
                    'num' => array(
                        '#type' => 'textfield',
                        '#title' => __('Number of listings to show', 'sabai-directory'),
                        '#integer' => true,
                        '#default_value' => 5, 
                    ),
                    'summary' => array(
                        '#type' => 'checkbox',
                        '#title' => __('Show summary', 'sabai-directory'),
                        '#default_value' => true, 
                    ),
                    'num_chars' => array(
                        '#type' => 'textfield',
                        '#title' => __('Number of characters in the summary', 'sabai-directory'),
                        '#integer' => true,
                        '#default_value' => 100, 
                    ),
                );
            case 'recent_reviews':
                return $settings + array(
                    'bundle' => array(
                        '#title' => __('Select directory', 'sabai-directory'),
                        '#options' => $directory_options = array('' => __('All directories', 'sabai-directory')) + $this->_addon->getApplication()->Directory_DirectoryList('review'),
                        '#type' => count($directory_options) <= 1 ? 'hidden' : 'select',
                        '#default_value' => array_shift(array_keys($directory_options)),
                    ),
                    'num' => array(
                        '#type' => 'textfield',
                        '#title' => __('Number of reviews to show', 'sabai-directory'),
                        '#integer' => true,
                        '#default_value' => 5, 
                    ),
                    'summary' => array(
                        '#type' => 'checkbox',
                        '#title' => __('Show summary', 'sabai-directory'),
                        '#default_value' => true, 
                    ),
                    'num_chars' => array(
                        '#type' => 'textfield',
                        '#title' => __('Number of characters in the summary', 'sabai-directory'),
                        '#integer' => true,
                        '#default_value' => 100, 
                    ),
                );
            case 'recent_photos':
                return $settings + array(
                    'bundle' => array(
                        '#title' => __('Select directory', 'sabai-directory'),
                        '#options' => $directory_options = array('' => __('All directories', 'sabai-directory')) + $this->_addon->getApplication()->Directory_DirectoryList('photo'),
                        '#type' => count($directory_options) <= 1 ? 'hidden' : 'select',
                        '#default_value' => array_shift(array_keys($directory_options)),
                    ),
                    'cols' => array(
                        '#type' => 'select',
                        '#title' => __('Number of columns', 'sabai-directory'),
                        '#options' => array(1 => 1, 2 => 2, 3 => 3, 4 => 4, 6 => 6),
                        '#default_value' => 3, 
                    ),
                    'rows' => array(
                        '#type' => 'textfield',
                        '#title' => __('Number of rows', 'sabai-directory'),
                        '#integer' => true,
                        '#min_value' => 1,
                        '#max_value' => 10,
                        '#default_value' => 4, 
                    ),
                );
            case 'featured':
                return $settings + array(
                    'bundle' => array(
                        '#title' => __('Select directory', 'sabai-directory'),
                        '#options' => $directory_options = array('' => __('All directories', 'sabai-directory')) + $this->_addon->getApplication()->Directory_DirectoryList(),
                        '#type' => count($directory_options) <= 1 ? 'hidden' : 'select',
                        '#default_value' => array_shift(array_keys($directory_options)),
                    ),
                    'num' => array(
                        '#type' => 'textfield',
                        '#title' => __('Number of listings to show', 'sabai-directory'),
                        '#integer' => true,
                        '#default_value' => 5, 
                    ),
                    'sort' => array(
                        '#type' => 'select',
                        '#title' => __('Sort listings by', 'sabai-directory'),
                        '#options' => array(
                            'post_published' => __('Date published', 'sabai-directory'),
                            'voting_rating.average' => __('Average review rating', 'sabai-directory'),
                            'content_children_count.value' => __('Number of reviews', 'sabai-directory'),
                            'voting_favorite.count' => __('Number of bookmarks', 'sabai-directory'),
                            '_random' => __('Random', 'sabai-directory'),
                        ),
                        '#default_value' => '_random', 
                    ),
                    'summary' => array(
                        '#type' => 'checkbox',
                        '#title' => __('Show summary', 'sabai-directory'),
                        '#default_value' => true, 
                    ),
                    'num_chars' => array(
                        '#type' => 'textfield',
                        '#title' => __('Number of characters in the summary', 'sabai-directory'),
                        '#integer' => true,
                        '#default_value' => 100, 
                    ),
                );
            case 'related':
                return $settings + array(
                    'num' => array(
                        '#type' => 'textfield',
                        '#title' => __('Number of listings to show', 'sabai-directory'),
                        '#integer' => true,
                        '#default_value' => 5, 
                    ),
                    'sort' => array(
                        '#type' => 'select',
                        '#title' => __('Sort listings by', 'sabai-directory'),
                        '#options' => array(
                            'post_published' => __('Date published', 'sabai-directory'),
                            'voting_rating.average' => __('Average review rating', 'sabai-directory'),
                            'content_children_count.value' => __('Number of reviews', 'sabai-directory'),
                            'voting_favorite.count' => __('Number of bookmarks', 'sabai-directory'),
                            '_random' => __('Random', 'sabai-directory'),
                        ),
                        '#default_value' => '_random', 
                    ),
                    'summary' => array(
                        '#type' => 'checkbox',
                        '#title' => __('Show summary', 'sabai-directory'),
                        '#default_value' => true, 
                    ),
                    'num_chars' => array(
                        '#type' => 'textfield',
                        '#title' => __('Number of characters in the summary', 'sabai-directory'),
                        '#integer' => true,
                        '#default_value' => 100, 
                    ),
                );
            case 'categories':
                return $settings + array(
                    'bundle' => array(
                        '#title' => __('Select directory', 'sabai-directory'),
                        '#options' => $directory_options = $this->_addon->getApplication()->Directory_DirectoryList('category'),
                        '#type' => count($directory_options) <= 1 ? 'hidden' : 'select',
                        '#default_value' => array_shift(array_keys($directory_options)),
                    ),
                    'depth' => array(
                        '#type' => 'textfield',
                        '#title' => __('Category depth (0 for unlimited)', 'sabai-directory'),
                        '#integer' => true,
                        '#default_value' => 0, 
                    ),
                    'icon' => array(
                        '#type' => 'checkbox',
                        '#title' => __('Show icon', 'sabai-directory'),
                        '#default_value' => true, 
                    ),
                    'post_count' => array(
                        '#type' => 'checkbox',
                        '#title' => __('Show post count', 'sabai-directory'),
                        '#default_value' => true, 
                    ),
                    'no_posts_hide' => array(
                        '#type' => 'checkbox',
                        '#title' => __('Hide if no posts', 'sabai-directory'),
                        '#default_value' => false, 
                    ),
                );
            case 'submitbtn':
                return array(
                    'addon' => array(
                        '#title' => __('Select directory', 'sabai-directory'),
                        '#options' => $directory_options = $this->_addon->getApplication()->Directory_DirectoryList('addon'),
                        '#type' => count($directory_options) <= 1 ? 'hidden' : 'select',
                        '#default_value' => array_shift(array_keys($directory_options)),
                    ),
                    'label' => array(
                        '#title' => __('Button label', 'sabai-directory'),
                        '#type' => 'textfield',
                        '#default_value' => __('Add Listing', 'sabai-directory'), 
                    ),
                    'size' => array(
                        '#type' => 'select',
                        '#title' => __('Button size', 'sabai-directory'),
                        '#options' => array(
                            'mini' => __('Mini', 'sabai-directory'),
                            'small' => __('Small', 'sabai-directory'),
                            '' => __('Medium', 'sabai-directory'),
                            'large' => __('Large', 'sabai-directory'),
                        ),
                        '#default_value' => 'large', 
                    ),
                    'color' => array(
                        '#type' => 'select',
                        '#title' => __('Button color', 'sabai-directory'),
                        '#options' => array(
                            '' => __('White', 'sabai-directory'),
                            'primary' => __('Blue', 'sabai-directory'),
                            'info' => __('Light blue', 'sabai-directory'),
                            'success' => __('Green', 'sabai-directory'),
                            'warning' => __('Orange', 'sabai-directory'),
                            'danger' => __('Red', 'sabai-directory'),
                            'inverse' => __('Black', 'sabai-directory'),
                        ),
                        '#default_value' => 'success', 
                    ),
                );
        }
    }
    
    public function widgetsWidgetGetLabel()
    {
        switch ($this->_name) {
            case 'submitbtn':
                return '';
            default:
                return $this->widgetsWidgetGetTitle();
        }
    }
    
    public function widgetsWidgetGetContent(array $settings)
    {
        if ($this->_name === 'submitbtn') {
            return $this->_getSubmitButton($settings);
        } elseif ($this->_name === 'related') {
            return $this->_getRelatedListings($settings);
        }
        
        if (!empty($settings['no_cache'])
            || false === $ret = $this->_addon->getApplication()
                ->getPlatform()
                ->getCache($cache_id = $this->_addon->getName() . '_widget_' . $this->_name . '_' . md5(serialize($settings)))
        ) {
            switch ($this->_name) {
                case 'categories':
                    $ret = $this->_getCategories($settings);
                    break;
                case 'recent_photos':
                    $ret = $this->_getRecentPhotos($settings);
                    break;
                case 'recent_reviews':            
                    $ret = $this->_getRecentReviews($settings);
                    break;
                case 'recent':            
                    $ret = $this->_getRecentListings($settings);
                    break;
                case 'featured': 
                    $ret = $this->_getFeaturedListings($settings);
                    break;
                default:
                    return;
            }
            if (empty($settings['no_cache'])) {
                $this->_addon->getApplication()->getPlatform()->setCache($ret, $cache_id, 600);
            }
        }
        return $ret;
    }
    
    public function widgetsWidgetOnSettingsSaved(array $settings, array $oldSettings)
    {        
        // Delete cache
        $cache_id = $this->_addon->getName() . '_widget_' . $this->_name . '_' . md5(serialize($oldSettings));
        $this->_addon->getApplication()->getPlatform()->deleteCache($cache_id);
    }
    
    private function _getRecentListings($settings)
    {
        $application = $this->_addon->getApplication();
        if ($settings['bundle']) {
            $bundle_key = 'post_entity_bundle_name';
            $bundle_value = $settings['bundle'];
        } else {
            $bundle_key =  'post_entity_bundle_type';
            $bundle_value = 'directory_listing';
        }
        $listings = $application->Entity_Query('content')
            ->propertyIs($bundle_key, $bundle_value)
            ->propertyIs('post_status', Sabai_Addon_Content::POST_STATUS_PUBLISHED)
            ->sortByProperty('post_published', 'DESC')
            ->fetch($settings['num']);
        if (empty($listings)) {
            return array();
        }
        $photos = $this->_getListingPhotos(array_keys($listings));
        $ret = array();
        foreach ($listings as $listing) {
            $ret[] = array(
                'summary' => !empty($settings['summary']) ? $listing->getSummary($settings['num_chars']) : null,
                'url' => $application->Entity_Url($listing),
                'title' => $listing->getTitle(),
                'meta' => array(
                    '<i class="sabai-icon-time"></i> ' . $application->DateDiff($listing->getTimestamp())
                ),
                'image' => !isset($photos[$listing->getId()])
                    ? $application->Entity_Permalink($listing, array('no_escape' => true, 'title' => '<img src="' . $application->ImageUrl('no_image_small.png') . '" alt="" />'))
                    : $application->File_ThumbnailLink($listing, $photos[$listing->getId()][0]->file_image[0], array('link_entity' => true)),
            );
        }
        return $ret;
    }
    
    private function _getFeaturedListings($settings)
    {
        $application = $this->_addon->getApplication();
        if ($settings['bundle']) {
            $bundle_key = 'post_entity_bundle_name';
            $bundle_value = $settings['bundle'];
        } else {
            $bundle_key =  'post_entity_bundle_type';
            $bundle_value = 'directory_listing';
        }
        $query = $application->Entity_Query('content')
            ->propertyIs($bundle_key, $bundle_value)
            ->propertyIs('post_status', Sabai_Addon_Content::POST_STATUS_PUBLISHED)
            ->fieldIs('content_featured', true);
        $settings += array('num' => 5, 'sort' => 'post_published');
        if (strpos($settings['sort'], '.')) {
            list($field, $column) = explode('.', $settings['sort']);
            $query->sortByField($field, 'DESC', $column);
        } elseif ($settings['sort'] === '_random') {
            $query->sortByRandom();
        } else {
            $query->sortByProperty($settings['sort'], 'DESC');
        }
        $listings = $query->fetch($settings['num']);
        if (empty($listings)) {
            return array();
        }
        $photos = $this->_getListingPhotos(array_keys($listings));
        $ret = array();
        foreach ($listings as $listing) {
            $meta = array(
                '<i class="sabai-icon-comments"></i> ' . (int)$listing->getSingleFieldValue('content_children_count', $this->_addon->getReviewBundleName()),
                '<i class="sabai-icon-camera"></i> ' . (int)$listing->getSingleFieldValue('content_children_count', $this->_addon->getPhotoBundleName()),
                '<i class="sabai-icon-bookmark"></i> ' . (int)$listing->getSingleFieldValue('voting_favorite', 'count'),
            );
            $ret[] = array(
                'summary' => !empty($settings['summary']) ? $listing->getSummary($settings['num_chars']) : null,
                'url' => $application->Entity_Url($listing),
                'title' => $listing->getTitle(),
                'image' => !isset($photos[$listing->getId()])
                    ? $application->Entity_Permalink($listing, array('no_escape' => true, 'title' => '<img src="' . $application->ImageUrl('no_image_small.png') . '" alt="" />'))
                    : $application->File_ThumbnailLink($listing, $photos[$listing->getId()][0]->file_image[0], array('link_entity' => true, 'title' => $listing->getTitle())),
                'meta' => $meta,
            );
        }
        return $ret;
    }
    
    private function _getRelatedListings($settings)
    {
        if (!isset($GLOBALS['sabai_content_entity'])
            || !$GLOBALS['sabai_content_entity'] instanceof Sabai_Addon_Content_Entity
            || $GLOBALS['sabai_content_entity']->getBundleType() !== 'directory_listing'
        ) {
            return;
        }
        
        if (!empty($settings['no_cache'])
            || false === $ret = $this->_addon->getApplication()
                ->getPlatform()
                ->getCache($cache_id = $this->_addon->getName() . '_widget_' . $this->_name . '_' . $GLOBALS['sabai_content_entity']->getId())
        ) {
            $ret = $this->_doGetRelatedListings($GLOBALS['sabai_content_entity'], $settings);
            if (empty($settings['no_cache'])) {
                $this->_addon->getApplication()->getPlatform()->setCache($ret, $cache_id, 600);
            }
        }
        return $ret;
    }
    
    private function _doGetRelatedListings($entity, $settings)
    {        
        $application = $this->_addon->getApplication();
        $query = $application->Entity_Query('content')
            ->propertyIs('post_entity_bundle_name', $entity->getBundleName())
            ->propertyIs('post_status', Sabai_Addon_Content::POST_STATUS_PUBLISHED)
            ->propertyIsNot('post_id', $entity->getId());
        if (!empty($entity->directory_category)) {
            $term_ids = array();
            foreach ($entity->directory_category as $category) {
                $term_ids[] = $category->getId();
            }
            $query->fieldIsIn('directory_category', $term_ids);
        } else {
            $query->fieldIsNull('directory_category');
        }
        $settings += array('num' => 5, 'sort' => 'post_published');
        if (strpos($settings['sort'], '.')) {
            list($field, $column) = explode('.', $settings['sort']);
            $query->sortByField($field, 'DESC', $column);
        } elseif ($settings['sort'] === '_random') {
            $query->sortByRandom();
        } else {
            $query->sortByProperty($settings['sort'], 'DESC');
        }
        $listings = $query->fetch($settings['num']);
        if (empty($listings)) {
            return array();
        }
        $photos = $this->_getListingPhotos(array_keys($listings));
        $ret = array();
        foreach ($listings as $listing) {
            $ret[] = array(
                'summary' => !empty($settings['summary']) ? $listing->getSummary($settings['num_chars']) : null,
                'url' => $application->Entity_Url($listing),
                'title' => $listing->getTitle(),
                'meta' => empty($listing->voting_rating['']['count'])
                    ? array('<i class="sabai-icon-time"></i> ' . $application->DateDiff($listing->getTimestamp()))
                    : array(sprintf('%s<span class="sabai-directory-rating-average">%s</span><span class="sabai-directory-rating-count">(%d)</span>', $application->Voting_RenderRating($listing), number_format($listing->voting_rating['']['average'], 2), $listing->voting_rating['']['count'])),
                'image' => !isset($photos[$listing->getId()])
                    ? $application->Entity_Permalink($listing, array('no_escape' => true, 'title' => '<img src="' . $application->ImageUrl('no_image_small.png') . '" alt="" />'))
                    : $application->File_ThumbnailLink($listing, $photos[$listing->getId()][0]->file_image[0], array('link_entity' => true)),
            );
        }
        return $ret;
    }
    
    private function _getListingPhotos($listingIds)
    {
        $photos = $this->_addon->getApplication()->Entity_Query()
            ->propertyIs('post_entity_bundle_type', 'directory_listing_photo')
            ->propertyIs('post_status', Sabai_Addon_Content::POST_STATUS_PUBLISHED)
            ->fieldIsIn('content_parent', $listingIds)
            ->fieldIsNotNull('directory_photo', 'official') // official photos
            ->sortByField('directory_photo', 'ASC', 'display_order')
            ->fetch();
        $ret = array();
        foreach ($photos as $photo) {
            if ($listing = $photo->getSingleFieldValue('content_parent')) {
                $ret[$listing->getId()][] = $photo;
            }
        }
        return $ret;
    }
    
    private function _getRecentReviews($settings)
    {
        $settings += array('num' => 5);
        $application = $this->_addon->getApplication();
        if ($settings['bundle']) {
            $bundle_key = 'post_entity_bundle_name';
            $bundle_value = $settings['bundle'];
        } else {
            $bundle_key =  'post_entity_bundle_type';
            $bundle_value = 'directory_listing_review';
        }
        $reviews = $application->Entity_Query('content')
            ->propertyIs($bundle_key, $bundle_value)
            ->propertyIs('post_status', Sabai_Addon_Content::POST_STATUS_PUBLISHED)
            ->sortByProperty('post_published', 'DESC')
            ->fetch($settings['num']);
        if (empty($reviews)) {
            return array();
        }
        $listings = $listing_ids = array();
        foreach (array_keys($reviews) as $review_id) {
            $listing = $application->Content_ParentPost($reviews[$review_id], false);
            if (!$listing) continue;
            
            $listing_ids[] = $listing->getId();
            $listings[$review_id] = $listing;
        }
        if (!empty($listing_ids)) {
            $photos = $this->_getListingPhotos($listing_ids);
        }
        $ret = array();
        foreach (array_keys(array_intersect_key($reviews, $listings)) as $review_id) {
            $review = $reviews[$review_id];
            $listing = $listings[$review_id];
            $ret[] = array(
                'summary' => !empty($settings['summary']) ? $review->getSummary($settings['num_chars']) : null,
                'url' => $application->Entity_Url($review),
                'title_html' => '<span class="sabai-rating sabai-rating-' . $review->directory_rating[0] * 10 . '" title="' . sprintf(__('%.1f out of 5 stars', 'sabai-directory'), $review->directory_rating[0]) . '"></span>&nbsp;'
                    . $application->Entity_Permalink($review) . $application->Entity_Permalink($listing, array('atts' => array('style' => 'display:none;'))),
                'meta' => array(
                    '<i class="sabai-icon-time"></i> ' . $application->DateDiff($review->getTimestamp()),
                    '<i class="sabai-icon-user"></i> ' . Sabai::h($application->Content_Author($review)->name)
                ),
                'image' => !isset($photos[$listing->getId()])
                    ? $application->Entity_Permalink($listing, array('no_escape' => true, 'title' => '<img src="' . $application->ImageUrl('no_image_small.png') . '" alt="" />'))
                    : $application->File_ThumbnailLink($listing, $photos[$listing->getId()][0]->file_image[0], array('link_entity' => true, 'title' => $listing->getTitle())),
            );
        }
        return $ret;
    }
    
    private function _getRecentPhotos($settings)
    {
        $settings += array('rows' => 4, 'cols' => 3);
        $application = $this->_addon->getApplication();
        if ($settings['bundle']) {
            $bundle_key = 'post_entity_bundle_name';
            $bundle_value = $settings['bundle'];
        } else {
            $bundle_key =  'post_entity_bundle_type';
            $bundle_value = 'directory_listing_photo';
        }
        $photos = $application->Entity_Query('content')
            ->propertyIs($bundle_key, $bundle_value)
            ->propertyIs('post_status', Sabai_Addon_Content::POST_STATUS_PUBLISHED)
            ->sortByProperty('post_published', 'DESC')
            ->fetch($settings['cols'] * $settings['rows']);
        if (empty($photos)) {
            return array();
        }
        $html = array();
        $i = 0;
        $span = 12 / $settings['cols'];
        while ($_photos = array_slice($photos, $i * $settings['cols'], $settings['cols'])) {
            $html[] = '<div class="sabai-row-fluid">';
            foreach ($_photos as $photo) {
                $photo_title = Sabai::h($photo->getTitle());
                $photo_url = $application->Directory_PhotoUrl($photo, 'thumbnail');
                if ($listing = $application->Content_ParentPost($photo, false)) {
                    $html[] = sprintf('<div class="sabai-span%d"><a href="%s" title="%s"><img src="%s" alt="" /></a></div>', $span, $application->Entity_Url($listing, '/' . $this->_addon->getSlug('photos'), array('photo_id' => $photo->getId())), $photo_title, $photo_url);
                } else {
                    // For some reason, listing of the photo could not be fetched. Normally, this should not happen.
                    $html[] = sprintf('<div class="sabai-span%d"><span title="%s"><img src="%s" alt="" /></span></div>', $span, $photo_title, $photo_url);
                }
            }
            $html[] = '</div>';
            ++$i;
        }
        return implode(PHP_EOL, $html);
    }
    
    private function _getCategories($settings)
    {
        $format = empty($settings['post_count']) ? '%s' : __('%s (%d)', 'sabai-directory');
        return $this->_addon->getApplication()->Taxonomy_HtmlList(
            $settings['bundle'],
            array(
                'content_bundle' => 'directory_listing',
                'format' => empty($settings['icon']) ? $format : '<i class="sabai-icon-folder-open"></i> ' . $format,
                'content_empty_skip' => !empty($settings['no_posts_hide']),
                'depth' => (int)$settings['depth'],
                'content_count' => !empty($settings['post_count']),
            )
        );
    }
    
    private function _getSubmitButton($settings)
    {
        $application = $this->_addon->getApplication();
        $addon = isset($settings['addon']) ? $settings['addon'] : 'Directory';
        return sprintf(
            '<a href="%s" class="sabai-btn %s %s">%s</a>',
            $application->Url('/'. $application->getAddon($addon)->getDirectorySlug() . '/add'),
            !empty($settings['size']) ? 'sabai-btn-' . $settings['size'] : '',
            !empty($settings['color']) ? 'sabai-btn-' . $settings['color'] : '',
            Sabai::h($settings['label'])
        );
    }
}