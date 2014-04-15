<?php
class Sabai_Addon_Directory_ContentType implements Sabai_Addon_Content_IContentType
{
    private $_addon, $_name;

    public function __construct(Sabai_Addon_Directory $addon, $name)
    {
        $this->_addon = $addon;
        $this->_name = $name;
    }
    
    public function contentTypeGetInfo()
    {
        if ($this->_name === $this->_addon->getListingBundleName()) {
            $listing_slug = $this->_addon->getConfig('pages', 'listing_slug');
            return array(
                'type' => 'directory_listing',
                'path' => '/' . $this->_addon->getDirectorySlug(),
                'icon' => 'edit',
                'label' => $this->_addon->getApplication()->_t(_n_noop('Listings', 'Listings', 'sabai-directory'), 'sabai-directory'),
                'label_singular' => $this->_addon->getApplication()->_t(_n_noop('Listing', 'Listing', 'sabai-directory'), 'sabai-directory'),
                'permalink_path' => '/' . $this->_addon->getDirectorySlug() . '/' . ($listing_slug ? $listing_slug : 'listing'),
                'properties' => array(
                    'post_title' => array(
                        'required' => true, // overrwrite the core setting
                        'weight' => 6,
                    ),
                    'post_published' => array(
                        'required' => true, // overrwrite the core setting
                        'weight' => 23,
                    ),
                    'post_user_id' => array(
                        'required' => true, // overrwrite the core setting
                        'weight' => 25,
                    ),
                ),
                'fields' => array(
                    'directory_header_user' => array(
                        'type' => 'sectionbreak',
                        'weight' => -1,
                        'title' => __('Your Info', 'sabai-directory'),
                        'data' => array('user_roles' => array('_guest_')),
                    ),
                    'directory_header_essential' => array(
                        'type' => 'sectionbreak',
                        'weight' => 5,
                        'title' => __('Essential Info', 'sabai-directory'),
                    ),
                    'directory_header_contact' => array(
                        'type' => 'sectionbreak',
                        'weight' => 10,
                        'title' => __('Contact Info', 'sabai-directory'),
                    ),
                    'directory_header_social' => array(
                        'type' => 'sectionbreak',
                        'weight' => 15,
                        'title' => __('Social Accounts', 'sabai-directory'),
                    ),
                    'directory_header_additional' => array(
                        'type' => 'sectionbreak',
                        'weight' => 20,
                        'title' => __('Additional Info', 'sabai-directory'),
                    ),
                    'directory_location' => array(
                        'type' => 'googlemaps_marker',
                        'settings' => array(),
                        'max_num_items' => 1,
                        'weight' => 7,
                        'title' => __('Address', 'sabai-directory'),
                        'required' => true,
                    ),
                    'directory_contact' => array(
                        'type' => 'directory_contact',
                        'settings' => array(),
                        'widget' => 'directory_contact',
                        'widget_settings' => array(),
                        'title' => '',
                        'max_num_items' => 1,
                        'weight' => 12,
                        'admin_title' => __('Contact Info', 'sabai-directory'),
                    ),
                    'directory_social' => array(
                        'type' => 'directory_social',
                        'settings' => array(),
                        'widget' => 'directory_social',
                        'widget_settings' => array(),
                        'title' => '',
                        'max_num_items' => 1,
                        'weight' => 18,
                        'admin_title' => __('Social Accounts', 'sabai-directory'),
                    ),
                    'directory_claim' => array(
                        'type' => 'directory_claim',
                        'title' => __('Owner', 'sabai-directory'),
                        'max_num_items' => 0,
                    ),
                ),
                'taxonomy_terms' => array(
                    $this->_addon->getCategoryBundleName() => array(
                        'required' => false,
                        'max_num_items' => 0,
                        'weight' => 9,
                    ),
                ),
                'voting_favorite' => array('button_enable' => true, 'title' => __('Bookmarks', 'sabai-directory')),
                'voting_flag' => true,
                'voting_rating' => true,
                'content_body' => array(
                    'required' => false,
                    'title' => __('Listing Description', 'sabai-directory'),
                    'widget_settings' => array('rows' => 10),
                    'weight' => 23,
                ),
                'content_featurable' => true,
                'content_guest_author' => array(
                    'weight' => 0,
                    'title' => '',
                ),
                'content_permissions' => array(
                    'edit_own' => array('label' => __('Edit own unclaimed %2$s', 'sabai-directory'), 'default' => true),
                    'edit_any' => array('label' => __('Edit any unclaimed %2$s', 'sabai-directory')),
                    'trash_own' => array('label' => __('Delete own unclaimed %2$s', 'sabai-directory')),
                    'manage' => array('label' => __('Delete any unclaimed %2$s', 'sabai-directory')),
                    'voting_rating' => false,
                    'voting_own_rating' => false,
                ),
                'file_content_icons' => false,
            );
        } elseif ($this->_name === $this->_addon->getReviewBundleName()) {
            return array(
                'type' => 'directory_listing_review',
                'path' => '/' . $this->_addon->getDirectorySlug() . '/' . $this->_addon->getSlug('reviews'),
                'parent' => $this->_addon->getListingBundleName(),
                'icon' => 'comments',
                'label' => $this->_addon->getApplication()->_t(_n_noop('Reviews', 'Reviews', 'sabai-directory'), 'sabai-directory'),
                'label_singular' => $this->_addon->getApplication()->_t(_n_noop('Review', 'Review', 'sabai-directory'), 'sabai-directory'),
                'properties' => array(
                    'post_title' => array(
                        'required' => true, // overrwrite the core setting
                        'weight' => 5,
                    ),
                    'post_published' => array(
                        'required' => true, // overrwrite the core setting
                        'weight' => 23,
                    ),
                    'post_user_id' => array(
                        'weight' => 25,
                    ),
                ),
                'fields' => array(
                    'directory_rating' => array(
                        'type' => 'directory_rating',
                        'max_num_items' => 1,
                        'weight' => 3,
                        'widget' => 'directory_rating',
                        'title' => __('Rating', 'sabai-directory'),
                        'required' => true,
                    ),
                ),
                'comment_comments' => array(),
                'voting_favorite' => array('button_enable' => true),
                'voting_flag' => true,
                'voting_helpful' => array('title' => __('Votes', 'sabai-directory')),
                'content_body' => array(
                    'required' => true,
                    'title' => __('Review', 'sabai-directory'),
                    'widget_settings' => array('rows' => 15),
                    'weight' => 10,
                ),
                'content_featurable' => true,
                'content_guest_author' => array(
                    'weight' => 1,
                    'title' => '',
                ),
                'file_content_icons' => false,
                'content_permissions' => array(
                    'manage' => array('label' => __('Delete any %2$s', 'sabai-directory')),
                ),
            );
        } elseif ($this->_name === $this->_addon->getPhotoBundleName()) {
            return array(
                'type' => 'directory_listing_photo',
                'path' => '/' . $this->_addon->getDirectorySlug() . '/' . $this->_addon->getSlug('photos'),
                'parent' => $this->_addon->getListingBundleName(),
                'icon' => 'picture',
                'label' => $this->_addon->getApplication()->_t(_n_noop('Photos', 'Photos', 'sabai-directory'), 'sabai-directory'),
                'label_singular' => $this->_addon->getApplication()->_t(_n_noop('Photo', 'Photo', 'sabai-directory'), 'sabai-directory'),
                'properties' => array(
                    'post_title' => array(
                        'widget' => 'content_post_title_hidden', // disable title property field
                        'weight' => 2,
                    ),
                    'post_published' => array(
                        'required' => true, // overrwrite the core setting
                        'weight' => 23,
                    ),
                    'post_user_id' => array(
                        'required' => true, // overrwrite the core setting
                        'weight' => 25,
                    ),
                ),
                'fields' => array(
                    'directory_photo' => array(
                        'type' => 'directory_photo',
                        'widget' => false, // no widget
                        'max_num_items' => 1,
                    ),
                ),
                'voting_helpful' => array('button_enable' => true, 'title' => __('Votes', 'sabai-directory')),
                'voting_favorite' => array('button_enable' => true, 'title' => __('Bookmarks', 'sabai-directory')),
                'voting_flag' => true,
                'content_body' => false,
                'content_guest_author' => array(
                    'weight' => 1,
                    'title' => '',
                ),
                'content_reference' => true,
                'fieldui_enable' => false,
                'file_image' => array(
                    'title' => __('Photo', 'sabai-directory'),
                    'settings' => array(),
                    'max_num_items' => 1,
                    'weight' => 13,
                    'widget' => 'file_upload'
                ),
                'file_content_icons' => false,
                'content_permissions' => array(
                    'add2' => array('guest_allowed' => false), // guests are allowed to upload photos with reviews only
                    'edit_own' => false,
                    'edit_any' => false,
                    'trash_own' => false,
                    'manage' => array('label' => __('Delete any non-official %2$s', 'sabai-directory')),
                ),
                'comment_comments' => array(),
            );
        } elseif ($this->_name === $this->_addon->getLeadBundleName()) {
            return array(
                'type' => 'directory_listing_lead',
                'path' => '/' . $this->_addon->getDirectorySlug() . '/' . $this->_addon->getSlug('leads'),
                'parent' => $this->_addon->getListingBundleName(),
                'icon' => 'picture',
                'label' => $this->_addon->getApplication()->_t(_n_noop('Leads', 'Leads', 'sabai-directory'), 'sabai-directory'),
                'label_singular' => $this->_addon->getApplication()->_t(_n_noop('Lead', 'Lead', 'sabai-directory'), 'sabai-directory'),
                'viewable' => false,
                'properties' => array(
                    'post_title' => array(
                        'widget' => 'content_post_title_hidden', // disable title property field
                        'weight' => 2,
                    ),
                    'post_published' => array(
                        'required' => true, // overrwrite the core setting
                        'weight' => 23,
                    ),
                    'post_user_id' => array(
                        'required' => true, // overrwrite the core setting
                        'weight' => 25,
                    ),
                ),
                'fields' => array(),
                'content_body' => array(
                    'required' => true,
                    'title' => __('Message', 'sabai-directory'),
                    'widget_settings' => array('rows' => 10, 'hide_buttons' => true, 'hide_preview' => true),
                    'weight' => 10,
                ),
                'content_guest_author' => array(
                    'weight' => 1,
                    'title' => '',
                ),
                'content_permissions' => array(
                    'edit_own' => false,
                    'edit_any' => false,
                    'trash_own' => false,
                    'manage' => false,
                ),
            );
        }
    }
    
    public function contentTypeIsPostTrashable(Sabai_Addon_Content_Entity $entity, SabaiFramework_User $user)
    {
        if ($this->_name === $this->_addon->getListingBundleName()) {
            return $this->_addon->isListingTrashable($entity, $user);
        } elseif ($this->_name === $this->_addon->getReviewBundleName()) {
            return $this->_addon->isReviewTrashable($entity, $user);
        } elseif ($this->_name === $this->_addon->getPhotoBundleName()) {
            return false;
        } elseif ($this->_name === $this->_addon->getLeadBundleName()) {
            return false;
        }
    }
}
