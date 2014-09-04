<?php
class Sabai_Addon_Directory_Controller_Admin_Settings extends Sabai_Addon_Form_Controller
{    
    protected function _doGetFormSettings(Sabai_Context $context, array &$formStorage)
    {
        $config = $this->getAddon()->getConfig();
        $this->_submitButtons[] = array('#value' => __('Save Changes', 'sabai-directory'), '#btn_type' => 'primary');
        $this->_successFlash = __('Settings saved.', 'sabai-directory');
        $listing_default_tabs = $this->getAddon()->getListingDefaultTabs();
        if (isset($config['display']['listing_tabs'])) {
            foreach ($listing_default_tabs as $listing_default_tab => $listing_default_tab_title) {
                $config['display']['listing_tabs']['options'][$listing_default_tab] = $listing_default_tab_title;
            }
        } else {
            $listing_tabs_options = $listing_default_tabs;
            unset($listing_tabs_options['sample']); // unselect sample tab
            $config['display']['listing_tabs'] = array(
                'options' => $listing_tabs_options,
                'default' => array_keys($listing_default_tabs),
            );
        }
        $form = array('#tree' => true);
        $form['display'] = array(
            '#title' => __('Display Settings', 'sabai-directory'),
            '#collapsed' => false,
            '#config' => $config,
            'perpage' => array(
                '#type' => 'textfield',
                '#title' => __('Listings per page', 'sabai-directory'),
                '#default_value' => $config['display']['perpage'],
                '#size' => 5,
                '#integer' => true,
                '#required' => true,
                '#max_value' => 500,
                '#min_value' => 1,
                '#field_suffix' => sprintf(__('(max. limit %d)', 'sabai-directory'), 500),
            ),
            'review_perpage' => array(
                '#type' => 'textfield',
                '#title' => __('Reviews per page', 'sabai-directory'),
                '#default_value' => $config['display']['review_perpage'],
                '#size' => 5,
                '#integer' => true,
                '#required' => true,
                '#max_value' => 100,
                '#min_value' => 1,
                '#field_suffix' => sprintf(__('(max. limit %d)', 'sabai-directory'), 100),
            ),
            'bookmark_perpage' => array(
                '#type' => 'textfield',
                '#title' => __('Bookmarks per page', 'sabai-directory'),
                '#default_value' => $config['display']['bookmark_perpage'],
                '#size' => 5,
                '#integer' => true,
                '#required' => true,
                '#max_value' => 100,
                '#min_value' => 1,
                '#field_suffix' => sprintf(__('(max. limit %d)', 'sabai-directory'), 100),
            ),
            'sorts' => array(
                '#type' => 'checkboxes',
                '#default_value' => isset($config['display']['sorts']) ? $config['display']['sorts'] : array('newest', 'reviews', 'rating', 'title', 'distance'),
                '#title' => __('Listings sorting options', 'sabai-directory'),
                '#options' => array(
                    'newest' => __('Sort by most recent', 'sabai-directory'),
                    'reviews' => __('Sort by most reviewed', 'sabai-directory'),
                    'rating' => __('Sort by highest rated', 'sabai-directory'),
                    'title' => __('Sort by title', 'sabai-directory'),
                    'random' => __('Sort by random', 'sabai-directory'),
                    'distance' => __('Sort by distance', 'sabai-directory'),
                ),
                '#class' => 'sabai-form-inline',
                '#required' => true,
            ),
            'sort' => array(
                '#type' => 'radios',
                '#default_value' => $config['display']['sort'],
                '#title' => __('Listings default sorting order', 'sabai-directory'),
                '#options' => array(
                    'newest' => __('Sort by most recent', 'sabai-directory'),
                    'reviews' => __('Sort by most reviewed', 'sabai-directory'),
                    'rating' => __('Sort by highest rated', 'sabai-directory'),
                    'title' => __('Sort by title', 'sabai-directory'),
                    'random' => __('Sort by random', 'sabai-directory'),
                ),
                '#class' => 'sabai-form-inline',
                '#required' => true,
            ),
            'review_sort' => array(
                '#type' => 'radios',
                '#default_value' => $config['display']['review_sort'],
                '#title' => __('Reviews default sorting order', 'sabai-directory'),
                '#options' => array(
                    'newest' => __('Sort by most recent', 'sabai-directory'),
                    'rating' => __('Sort by highest rating', 'sabai-directory'),
                    'helpfulness' => __('Sort by most helpful', 'sabai-directory'),
                ),
                '#class' => 'sabai-form-inline',
                '#required' => true,
            ),
            'photo_sort' => array(
                '#type' => 'radios',
                '#default_value' => $config['display']['photo_sort'],
                '#title' => __('Photos default sorting order', 'sabai-directory'),
                '#options' => array(
                    'newest' => __('Sort by most recent', 'sabai-directory'),
                    'votes' => __('Sort by most voted', 'sabai-directory'),
                ),
                '#class' => 'sabai-form-inline',
                '#required' => true,
            ),
            'bookmark_sort' => array(
                '#type' => 'radios',
                '#default_value' => $config['display']['bookmark_sort'],
                '#title' => __('Bookmarks default sorting order', 'sabai-directory'),
                '#options' => array(
                    'newest' => __('Sort by most recent', 'sabai-directory'),
                    'added' => __('Sort by date added', 'sabai-directory'),
                ),
                '#class' => 'sabai-form-inline',
                '#required' => true,
            ),
            'view' => array(
                '#type' => 'radios',
                '#default_value' => $config['display']['view'],
                '#title' => __('Listings default view', 'sabai-directory'),
                '#options' => array(
                    'list' => __('List view', 'sabai-directory'),
                    'grid' => __('Grid view', 'sabai-directory'),
                    'map' => __('Map view', 'sabai-directory'),
                ),
                '#class' => 'sabai-form-inline',
                '#required' => true,
                '#states' => array(
                    'visible' => array(
                        'input[name="map[disable][]"]' => array('type' => 'checked', 'value' => false),
                    ), 
                ),
            ),
            'grid_columns' => array(
                '#type' => 'radios',
                '#class' => 'sabai-form-inline',
                '#title' => __('Grid view column count', 'sabai-directory'),
                '#options' => array(2 => 2, 3 => 3, 4 => 4, 6 => 6),
                '#default_value' => isset($config['display']['grid_columns']) ? $config['display']['grid_columns'] : 4,
            ),
            'f_tabs' => array(
                '#title' => __('Frontpage Tabs'),
                '#collapsible' => false,
                '#class' => 'sabai-form-group',
                'categories' => array(
                    '#type' => 'checkbox',
                    '#title' => __('Show the "Categories" tab on the front page', 'sabai-directory'),
                    '#default_value' => !empty($config['display']['f_tabs']['categories']),
                ),
                'reviews' => array(
                    '#type' => 'checkbox',
                    '#title' => __('Show the "Reviews" tab on the front page', 'sabai-directory'),
                    '#default_value' => !empty($config['display']['f_tabs']['reviews']),
                ),
                'photos' => array(
                    '#type' => 'checkbox',
                    '#title' => __('Show the "Photos" tab on the front page', 'sabai-directory'),
                    '#default_value' => !empty($config['display']['f_tabs']['photos']),
                ),
            ),
            'listing_tabs' => array(
                '#type' => 'options',
                '#title' => __('Single listing page tabs', 'sabai-directory'),
                '#multiple' => true,
                '#default_value' => $config['display']['listing_tabs'],
                '#options_disabled' => array_keys($this->getAddon()->getListingDefaultTabs()),
                '#value_title' => __('slug', 'sabai-directory'),
                '#value_regex' => '/^[a-z0-9][a-z0-9_]*[a-z0-9]$/',
                '#value_regex_error_message' => __('Slugs must consist of lowercase alphanumeric characters and underscores.', 'sabai-directory'),
                '#description' => sprintf(__('Add, edit, remove, and sort tabs. When you add a custom tab, make sure to create a template file named "directory_listing_tab_[slug].html.php" under %s.', 'sabai-directory'), $this->getPlatform()->getCustomAssetsDir()),
            ),
            'category_columns' => array(
                '#type' => 'radios',
                '#class' => 'sabai-form-inline',
                '#title' => __('Category list page column count', 'sabai-directory'),
                '#options' => array(1 => 1, 2 => 2, 3 => 3, 4 => 4),
                '#default_value' => isset($config['display']['category_columns']) ? $config['display']['category_columns'] : 2,
            ),
            'buttons' => array(
                '#title' => __('Buttons', 'sabai-directory'),
                '#class' => 'sabai-form-group',
                '#collapsible' => false,
                'search' => array(
                    '#class' => 'sabai-form-inline',
                    '#type' => 'radios',
                    '#options' => $this->ButtonOptions('<i class="sabai-icon-search"></i>'),
                    '#title_no_escape' => true,
                    '#default_value' => isset($config['display']['buttons']['search']) ? $config['display']['buttons']['search'] : 'sabai-btn-primary',
                ),
                'listing' => array(
                    '#class' => 'sabai-form-inline',
                    '#type' => 'radios',
                    '#options' => $this->ButtonOptions(__('Add Listing', 'sabai-directory')),
                    '#title_no_escape' => true,
                    '#default_value' => isset($config['display']['buttons']['listing']) ? $config['display']['buttons']['listing'] : 'sabai-btn-success',
                ),
                'review' => array(
                    '#class' => 'sabai-form-inline',
                    '#type' => 'radios',
                    '#options' => $this->ButtonOptions('<i class="sabai-icon-edit"></i> ' . __('Write a Review', 'sabai-directory')),
                    '#title_no_escape' => true,
                    '#default_value' => isset($config['display']['buttons']['review']) ? $config['display']['buttons']['review'] : 'sabai-btn-success',
                ),
                'photos' => array(
                    '#class' => 'sabai-form-inline',
                    '#type' => 'radios',
                    '#options' => $this->ButtonOptions('<i class="sabai-icon-camera"></i> ' . __('Add Photos', 'sabai-directory')),
                    '#title_no_escape' => true,
                    '#default_value' => isset($config['display']['buttons']['photos']) ? $config['display']['buttons']['photos'] : 'sabai-btn-success',
                ),
                'directions' => array(
                    '#class' => 'sabai-form-inline',
                    '#type' => 'radios',
                    '#options' => $this->ButtonOptions(__('Get Directions', 'sabai-directory')),
                    '#title_no_escape' => true,
                    '#default_value' => isset($config['display']['buttons']['directions']) ? $config['display']['buttons']['directions'] : 'sabai-btn-primary',
                ),
            ),
            'no_photo_comments' => array(
                '#type' => 'checkbox',
                '#title' => __('Disable photo comments', 'sabai-directory'),
                '#default_value' => !empty($config['display']['no_photo_comments']),
            ),
            'stick_featured' => array(
                '#type' => 'checkbox',
                '#title' => __('Stick featured listings to the top of the frontpage and category pages', 'sabai-directory'),
                '#default_value' => !isset($config['display']['stick_featured']) || !empty($config['display']['stick_featured']),
            ),
        );
        $form['map'] = array(
            '#title' => __('Map Settings', 'sabai-directory'),
            '#collapsed' => false,
            'disable' => array(
                '#type' => 'checkbox',
                '#default_value' => !empty($config['map']['disable']),
                '#title' => __('Disable map', 'sabai-directory'),
            ),
            'height' => array(
                '#type' => 'textfield',
                '#title' => __('Map height', 'sabai-directory'),
                '#default_value' => $config['map']['height'],
                '#size' => 5,
                '#integer' => true,
                '#required' => true,
                '#field_suffix' => 'px',
                '#states' => array(
                    'visible' => array(
                        'input[name="map[disable][]"]' => array('type' => 'checked', 'value' => false),
                    ), 
                ),
            ),
            'list_show' => array(
                '#type' => 'checkbox',
                '#title' => __('Display small map on List view', 'sabai-directory'),
                '#default_value' => $config['map']['list_show'],
                '#states' => array(
                    'visible' => array(
                        'input[name="map[disable][]"]' => array('type' => 'checked', 'value' => false),
                    ), 
                ),
            ),
            'list_height' => array(
                '#type' => 'textfield',
                '#title' => __('Map height on List view', 'sabai-directory'),
                '#default_value' => $config['map']['list_height'],
                '#size' => 5,
                '#integer' => true,
                '#required' => true,
                '#field_suffix' => 'px',
                '#states' => array(
                    'visible' => array(
                        'input[name="map[disable][]"]' => array('type' => 'checked', 'value' => false),
                        'input[name="map[list_show][]"]' => array('type' => 'checked', 'value' => true),
                    ), 
                ),
            ),
            'icon' => array(
                '#type' => 'textfield',
                '#url' => true,
                '#title' => __('Custom marker icon URL', 'sabai-directory'),
                '#default_value' => $config['map']['icon'],
                '#size' => 60,
                '#states' => array(
                    'visible' => array(
                        'input[name="map[disable][]"]' => array('type' => 'checked', 'value' => false),
                    ), 
                ),
            ),
            'style' => array(
                '#type' => 'select',
                '#options' => array('' => __('Default style', 'sabai-directory')) + $this->GoogleMaps_Style(),
                '#title' => __('Map style', 'sabai-directory'),
                '#default_value' => $config['map']['style'],
                '#required' => true,
                '#states' => array(
                    'visible' => array(
                        'input[name="map[disable][]"]' => array('type' => 'checked', 'value' => false),
                    ), 
                ),
            ),
            
            'listing_default_zoom' => array(
                '#type' => 'textfield',
                '#title' => __('Default zoom level for single listing', 'sabai-directory'),
                '#size' => 3,
                '#min_value' => 0,
                '#integer' => true,
                '#required' => true,
                '#default_value' => isset($config['map']['listing_default_zoom']) ? $config['map']['listing_default_zoom'] : 15,
                '#states' => array(
                    'visible' => array(
                        'input[name="map[disable][]"]' => array('type' => 'checked', 'value' => false),
                    ), 
                ),
            ),
            'distance_mode' => array(
                '#type' => 'radios',
                '#title' => __('Distance mode', 'sabai-directory'),
                '#options' => array('km' => __('Kilometers', 'sabai-directory'), 'mil' => __('Miles', 'sabai-directory')),
                '#default_value' => isset($config['map']['distance_mode']) ? $config['map']['distance_mode'] : 'km',
                '#class' => 'sabai-form-inline',
                '#states' => array(
                    'visible' => array(
                        'input[name="map[disable][]"]' => array('type' => 'checked', 'value' => false),
                    ), 
                ),
            ),
            'options' => array(
                'marker_clusters' => array(
                    '#type' => 'checkbox',
                    '#default_value' => !empty($config['map']['options']['marker_clusters']),
                    '#title' => __('Enable marker clusters', 'sabai-directory'),
                ),
                'marker_cluster_imgurl' => array(
                    '#type' => 'textfield',
                    '#url' => true,
                    '#default_value' => @$config['map']['options']['marker_cluster_imgurl'],
                    '#title' => __('Custom marker cluster image directory URL', 'sabai-directory'),
                    '#description' => sprintf(__('Default: %s'), 'http://google-maps-utility-library-v3.googlecode.com/svn/trunk/markerclusterer/images'),
                    '#size' => 60,
                    '#states' => array(
                        'visible' => array(
                            'input[name="map[options][marker_clusters][]"]' => array('type' => 'checked', 'value' => true),
                        ), 
                    ),
                ),
                'scrollwheel' => array(
                    '#type' => 'checkbox',
                    '#default_value' => !empty($config['map']['options']['scrollwheel']),
                    '#title' => __('Enable scrollwheel zooming on the map', 'sabai-directory'),
                ),
                '#states' => array(
                    'visible' => array(
                        'input[name="map[disable][]"]' => array('type' => 'checked', 'value' => false),
                    ), 
                ),
            ),
        );
        $radius_options = array(0 => __('None', 'sabai-directory'));
        foreach (array(2, 5, 10, 20, 50, 100) as $distance) {
            $radius_options[$distance] = sprintf(__('%d km/mil', 'sabai-directory'), $distance);
        }
        $form['search'] = array(
            '#title' => __('Search Settings', 'sabai-directory'),
            '#collapsed' => false,
            'min_keyword_len' => array(
                '#type' => 'textfield',
                '#title' => __('Min. length of keywords in characters', 'sabai-directory'),
                '#size' => 3,
                '#default_value' => isset($config['search']['min_keyword_len']) ? $config['search']['min_keyword_len'] : 3,
                '#integer' => true,
                '#required' => true,
                '#min_value' => 1,
            ),
            'radius' => array(
                '#type' => 'radios',
                '#title' => __('Default search radius', 'sabai-directory'),
                '#options' => $radius_options,
                '#default_value' => isset($config['search']['radius']) ? $config['search']['radius'] : 0,
                '#class' => 'sabai-form-inline',
            ),
            'no_loc' => array(
                '#type' => 'checkbox',
                '#default_value' => !empty($config['search']['no_loc']),
                '#title' => __('Disable location search', 'sabai-directory'),
            ),
            'country' => array(
                '#title' => __('Country', 'sabai-directory'),
                '#description' => __('Enter one of the <a href="http://en.wikipedia.org/wiki/ISO_3166-1_alpha-2" target="_blank">two-letter country codes</a> to restrict location autocomplete suggestions to a specific country.', 'sabai-directory'),
                '#type' => 'textfield',
                '#size' => 3,
                '#default_value' => isset($config['search']['country']) ? $config['search']['country'] : null,
                '#min_length' => 2,
                '#max_length' => 2,
                '#states' => array(
                    'visible' => array(
                        'input[name="search[no_loc][]"]' => array('type' => 'checked', 'value' => false),
                    ), 
                ),
            ),
        );
        $form['photo'] = array(
            '#title' => __('Photo Upload Settings', 'sabai-directory'),
            '#collapsed' => false,
            'max_file_size' => array(
                '#type' => 'textfield',
                '#title' => __('Max. file size', 'sabai-directory'),
                '#size' => 7,
                '#field_suffix' => 'KB',
                '#default_value' => $config['photo']['max_file_size'],
                '#integer' => true,
                '#required' => true,
            ),
            'max_num' => array(
                '#type' => 'textfield',
                '#title' => __('Max. number of photos for new listings', 'sabai-directory'),
                '#description' => __('Enter the maximum number of main photos users can upload when submitting listings. This does not include photos uploaded with reviews.', 'sabai-directory'),
                '#size' => 7,
                '#default_value' => $config['photo']['max_num'],
                '#integer' => true,
                '#required' => true,
            ),
            'max_num_owner' => array(
                '#type' => 'textfield',
                '#title' => __('Max. number of photos for claimed listings', 'sabai-directory'),
                '#description' => __('Enter the maximum number of main photos listing owners can upload for their claimed listings. This does not include photos uploaded with reviews.', 'sabai-directory'),
                '#size' => 7,
                '#default_value' => isset($config['photo']['max_num_owner']) ? $config['photo']['max_num_owner'] : 10,
                '#integer' => true,
                '#required' => true,
            ),
            'max_num_photos' => array(
                '#type' => 'textfield',
                '#title' => __('Max. number of user photos', 'sabai-directory'),
                '#description' => __('Enter the maximum number of photos users can upload for each listing. This does not include photos uploaded with reviews.', 'sabai-directory'),
                '#size' => 7,
                '#default_value' => isset($config['photo']['max_num_photos']) ? $config['photo']['max_num_photos'] : 10,
                '#integer' => true,
                '#required' => true,
            ),
            'max_num_review' => array(
                '#type' => 'textfield',
                '#title' => __('Max. number of user photos in reviews', 'sabai-directory'),
                '#description' => __('Enter the maximum number of photos users can upload in reviews.', 'sabai-directory'),
                '#size' => 7,
                '#default_value' => isset($config['photo']['max_num_review']) ? $config['photo']['max_num_review'] : 12,
                '#integer' => true,
                '#required' => true,
            ),
        );
        $claim_form_header = $this->getPlatform()->getOption($this->getAddon()->getName() . '_claim_form_header');
        if (!strlen($claim_form_header)) {
            $claim_form_header = __('If the listing is for your organisation, please complete the details below. Once we have confirmed your identity, we will give you full control over your listing and its contents.', 'sabai-directory');
        }
        $form['claims'] = array(
            '#title' => __('Listing Claim Settings', 'sabai-directory'),
            '#collapsed' => false,
            'duration' => array(
                '#type' => 'textfield',
                '#title' => __('Duration', 'sabai-directory'),
                '#description' => __('Enter the number of days claims are valid before they expire. Enter 0 for no expiration.', 'sabai-directory'),
                '#integer' => true,
                '#default_value' => $config['claims']['duration'],
                '#field_suffix' => __('days', 'sabai-directory'),
                '#required' => true,
                '#size' => 4,
            ),
            'grace_period' => array(
                '#type' => 'textfield',
                '#title' => __('Grace period duration', 'sabai-directory'),
                '#description' => __('Enter the number of days after which expired claims are deleted from the database.', 'sabai-directory'),
                '#integer' => true,
                '#default_value' => $config['claims']['grace_period'],
                '#field_suffix' => __('days', 'sabai-directory'),
                '#required' => true,
                '#size' => 4,
            ),
            'claim_form_header' => array(
                '#type' => 'textarea',
                '#title' => __('Claim listing form header', 'sabai-directory'),
                '#default_value' => $claim_form_header,
                '#rows' => 3,
                '#tree' => false,
            ),
            'no_comment' => array(
                '#type' => 'checkbox',
                '#title' => __('Do not require comment', 'sabai-directory'),
                '#default_value' => !empty($config['claims']['no_comment']),
            ),
            'tac' => array(
                '#title' => __('Terms and Conditions', 'sabai-directory'),
                '#collapsible' => false,
                '#class' => 'sabai-form-group',
                'type' => array(
                    '#type' => 'radios',
                    '#options' => array(
                        'none' => __('No terms and conditions', 'sabai-directory'),
                        'link' => __('Add terms and conditions link', 'sabai-directory'),
                        'inline' => __('Add terms and conditions inline', 'sabai-directory'),
                    ),
                    '#default_value' => isset($config['claims']['tac']['type']) ? $config['claims']['tac']['type'] : 'none',
                ),
                'link' => array(
                    '#type' => 'textfield',
                    '#field_prefix' => rtrim($this->getScriptUrl('main'), '/') . '/',
                    '#states' => array(
                        'visible' => array(
                            'input[name="claims[tac][type][0]"]' => array('type' => 'value', 'value' => 'link'),
                        ), 
                    ),
                    '#size' => 30,
                    '#default_value' => isset($config['claims']['tac']['link']) ? $config['claims']['tac']['link'] : null,
                ),
                'claim_tac' => array(
                    '#type' => 'textarea',
                    '#tree' => false,
                    '#rows' => 10,
                    '#default_value' => $this->getPlatform()->getOption($this->getAddon()->getName() . '_claim_tac'),
                    '#states' => array(
                        'visible' => array(
                            'input[name="claims[tac][type][0]"]' => array('type' => 'value', 'value' => 'inline'),
                        ), 
                    ),
                ),
                'required' => array(
                    '#type' => 'checkbox',
                    '#title' => __('Users must agree to terms and conditions', 'sabai-directory'),
                    '#states' => array(
                        'invisible' => array(
                            'input[name="claims[tac][type][0]"]' => array('type' => 'value', 'value' => 'none'),
                        ), 
                    ),
                    '#default_value' => !empty($config['claims']['tac']['required']),
                ),
            ),
        );
        $form['spam'] = array(
            '#title' => __('Spam Settings', 'sabai-directory'),
            '#collapsed' => false,
            'threshold' => array(
                '#title' => __('Spam score threshold', 'sabai-directory'),
                '#collapsible' => false,
                '#class' => 'sabai-form-group',
                'header' => array(
                    '#type' => 'markup',
                    '#value' => '<p>' . __('When a post is flagged, the post is assigned a "spam score". Posts with spam scores exceeding the threshold value are marked as spam and moved to trash automatically by the system. Also, posts with higher number of votes will have higher threshold. For example, if the value set here is 11, and a post has 10 votes, then the spam score threshold for the post will be 14 (11 + 0.3 x 10).', 'sabai-directory') . '</p>',
                ),
                'listing' => array(
                    '#type' => 'textfield',
                    '#field_prefix' => __('Listings:', 'sabai-directory'),
                    '#field_suffix' => '+ 0.3 x ' . __('number of rating stars', 'sabai-directory'),
                    '#default_value' => $config['spam']['threshold']['listing'],
                    '#size' => 4,
                    '#integer' => true,
                    '#required' => true,
                ),
                'review' => array(
                    '#type' => 'textfield',
                    '#field_prefix' => __('Reviews:', 'sabai-directory'),
                    '#field_suffix' => '+ 0.3 x ' . __('number of "helpful" votes', 'sabai-directory'),
                    '#default_value' => $config['spam']['threshold']['review'],
                    '#size' => 4,
                    '#integer' => true,
                    '#required' => true,
                ),
                'photo' => array(
                    '#type' => 'textfield',
                    '#field_prefix' => __('Photos', 'sabai-directory'),
                    '#field_suffix' => '+ 0.3 x ' . __('number of votes', 'sabai-directory'),
                    '#default_value' => $config['spam']['threshold']['photo'],
                    '#size' => 4,
                    '#integer' => true,
                    '#required' => true,
                ),
            ),
            'auto_delete' => array(
                '#type' => 'checkbox',
                '#title' => __('Auto-delete spam', 'sabai-directory'),
                '#default_value' => $config['spam']['auto_delete'],
                '#description' => __('When checked, posts that have been marked as spam will be deleted by the system after the period of time specified in the "Delete Spam After" option.', 'sabai-directory'),
            ),
            'delete_after' => array(
                '#type' => 'textfield',
                '#default_value' => $config['spam']['delete_after'],
                '#field_prefix' => __('Delete spam after:', 'sabai-directory'),
                '#description' => __('Enter the number of days the system will wait before auto-deleting posts marked as spam.', 'sabai-directory'),
                '#field_suffix' => __('days', 'sabai-directory'),
                '#size' => 4,
                '#integer' => true,
                '#states' => array(
                    'visible' => array(
                        'input[name="spam[auto_delete][]"]' => array('type' => 'checked', 'value' => true),
                    ),
                ),
            ),
        );
        $form += $this->Directory_PageSettingsForm($this->getCurrentAddonName());
        
        return $form;
    }

    public function submitForm(Sabai_Addon_Form_Form $form, Sabai_Context $context)
    {
        $new_config = array(
            'display' => $form->values['display'],
            'map' => $form->values['map'],
            'photo' => $form->values['photo'],
            'spam' => $form->values['spam'],
            'claims' => array('allow_existing' => $this->getAddon()->getConfig('claims', 'allow_existing')) + $form->values['claims'],
            'pages' => $form->values['pages'],
            'search' => $form->values['search'],
        );
        // Save claim form header and tac to platform instead of the add-ons table
        $this->getPlatform()->setOption($this->getAddon()->getName() . '_claim_form_header', $this->HTML_Filter($form->values['claim_form_header']))
            ->setOption($this->getAddon()->getName() . '_claim_tac', $form->values['claim_tac']);
        
        // Run upgrade process to refresh all slug related data
        $log = new ArrayObject();
        $addon = $this->UpgradeAddon($this->getAddon()->getName(), $new_config, $log);
        $this->reloadAddons()
            ->doEvent('SabaiAddonUpgraded', array($addon, $addon->version, $log));
        $this->getPlatform()->clearCache();
        $context->setSuccess('/' . $this->getAddon()->getDirectorySlug() . '/settings');
    }
}
