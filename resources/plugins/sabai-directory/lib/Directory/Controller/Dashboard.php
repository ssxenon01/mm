<?php
class Sabai_Addon_Directory_Controller_Dashboard extends Sabai_Addon_Form_Controller
{
    protected function _doGetFormSettings(Sabai_Context $context, array &$formStorage)
    {
        $this->_submitable = false;
        
        // Init variables
        $sortable_headers = array('title', 'directory', 'expires', 'featured');
        $sort = $context->getRequest()->asStr('sort', 'title', $sortable_headers);
        $order = $context->getRequest()->asStr('order', 'ASC', array('ASC', 'DESC'));
        $url_params = array('sort' => $sort, 'order' => $order);
        
        $listings = $this->Entity_Query('content')
            ->propertyIs('post_entity_bundle_type', 'directory_listing')
            ->propertyIs('post_status', Sabai_Addon_Content::POST_STATUS_PUBLISHED)
            ->fieldIs('directory_claim', $this->getUser()->id, 'claimed_by');
        if ($sort === 'expires') {
            $listings->sortByField('directory_claim', $order, 'expires_at');
        } elseif ($sort === 'featured') {
            $listings->sortByField('content_featured', $order, 'expires_at');
        } elseif ($sort === 'directory') {
            $listings->sortByProperty('post_entity_bundle_name', $order);
        } else {
            $listings->sortByProperty('post_title', $order);
        }
        
        // Init form
        $form = array(
            'entities' => array(
                '#type' => 'tableselect',
                '#header' => array(
                    'title' => __('Title', 'sabai-directory'),
                    'directory' => __('Directory', 'sabai-directory'),
                    'expires' => __('Claim Expires', 'sabai-directory'),
                    'featured' => __('Featured', 'sabai-directory'),
                    'actions' => '',
                ),
                '#options' => array(),
                '#disabled' => true,
            ),
        );
        
        $this->_makeTableSortable($context, $form['entities'], $sortable_headers, array(), $sort, $order, $url_params);

        $pager = $listings->paginate();
        $directory_links = array();
        foreach ($pager->getElements() as $listing) {
            $path = '/' . $this->getAddon()->getDashboardSlug() . '/' . $listing->getId();
            $expired = !empty($listing->directory_claim[$this->getUser()->id]['expires_at']) && $listing->directory_claim[$this->getUser()->id]['expires_at'] < time();
            $actions = array(
                array('label' => __('Edit', 'sabai-directory'), 'path' => '/edit', 'icon' => 'edit', 'title' => __('Edit listing', 'sabai-directory'), 'disabled' => $expired),
                array('label' => __('Add Photos', 'sabai-directory'), 'path' => '/upload_photos', 'icon' => 'camera', 'title' => __('Add official photos', 'sabai-directory'), 'disabled' => $expired),
            );
            $actions = $this->Filter('DirectoryMyListingActions', $actions, array($this->Entity_Bundle($listing), $listing, $this->getUser()->getIdentity(), $expired));
            ksort($actions);
            foreach ($actions as $k => $action) {
                $actions[$k] = $this->LinkTo(
                    $action['label'],
                    is_string($action['path']) ? $path . $action['path'] : $action['path'],
                    array('icon' => $action['icon'], 'disabled' => !empty($action['disabled'])),
                    empty($action['disabled']) ? array('title' => $action['title']) : array('title' => $action['title'], 'onclick' => 'return false;')
                );
            }
            
            if ($listing->isFeatured()) {
                if (empty($listing->content_featured[0]['expires_at'])) { // never expires
                    $featured = '<span class="sabai-label sabai-label-success">' . __('Yes', 'sabai-directory') . '</span>';
                } elseif ($listing->content_featured[0]['expires_at'] < time()) { // expired
                    $featured = '<span class="sabai-label">' . __('No', 'sabai-directory') . '</span>';
                } elseif ($listing->content_featured[0]['expires_at'] < time() + 259200) { // expires in 3 days
                    $featured = '<span class="sabai-label sabai-label-warning">' . $this->DateDiff($listing->content_featured[0]['expires_at']) . '</span>';
                } else {
                    $featured = '<span class="sabai-label sabai-label-success">' . $this->DateDiff($listing->content_featured[0]['expires_at']) . '</span>';
                }
            } else {
                $featured = '<span class="sabai-label">' . __('No', 'sabai-directory') . '</span>';
            }
            $rating = empty($listing->voting_rating['']['count']) ? '' : sprintf(
                '%s<span class="sabai-directory-rating-average">%s</span><span class="sabai-directory-rating-count">(%d)</span>',
                $this->Voting_RenderRating($listing),
                number_format($listing->voting_rating['']['average'], 2),
                $listing->voting_rating['']['count']
            );
            $listing_title = $listing->isPublished() ? $this->Entity_Permalink($listing) : Sabai::h($listing->getTitle());
            if (empty($listing->directory_claim[$this->getUser()->id]['expires_at'])) { // never expires
                $expires = '<span class="sabai-label sabai-label-success">' . __('Never', 'sabai-directory') . '</span>';
            } elseif ($listing->directory_claim[$this->getUser()->id]['expires_at'] < time()) { // expired
                $expires = '<span class="sabai-label sabai-label-important">' . $this->Date($listing->directory_claim[$this->getUser()->id]['expires_at']) . '</span>';
            } elseif ($listing->directory_claim[$this->getUser()->id]['expires_at'] < time() + 604800) { // expires in 7 days
                $expires = '<span class="sabai-label sabai-label-warning">' . $this->Date($listing->directory_claim[$this->getUser()->id]['expires_at']) . '</span>';
            } else {
                $expires = '<span class="sabai-label sabai-label-success">' . $this->Date($listing->directory_claim[$this->getUser()->id]['expires_at']) . '</span>';
            }
            
            ksort($actions);
            if (!isset($directory_links[$listing->getBundleName()])) {
                $listing_addon = $this->Entity_Addon($listing);
                $directory_links[$listing->getBundleName()] = '<a href="'. $this->Url('/' . $listing_addon->getDirectorySlug()) .'">' . Sabai::h($listing_addon->getDirectoryPageTitle()) . '</a>';
            }
            $form['entities']['#options'][$listing->getId()] = array(
                'title' => '<strong class="sabai-row-title">' . $listing_title . '</strong> ' . $rating,
                'directory' => $directory_links[$listing->getBundleName()],
                'expires' => $expires,
                'featured' => $featured,
                'actions' => '<div class="sabai-btn-group sabai-pull-right">' . $this->ButtonLinks($actions, 'mini', true, false) . '</div>',
            );
        }
        // Remove Directory column if only 1 directory
        if (count($directory_links) <= 1) {
            unset($form['entities']['#header']['directory']);            
        }
        
        // Add link to submit listings if the user has the permission to submit listings to any of the directories
        $links = array();
        $directory_addons = $this->Directory_DirectoryList('addon');
        foreach (array_keys($directory_addons) as $directory_addon) {
            if ($this->getUser()->hasPermission($this->getAddon($directory_addon)->getListingBundleName() . '_add')) {
                $links[] = $this->LinkTo(
                    $this->getAddon($directory_addon)->getDirectoryPageTitle(),
                    $this->Url('/' . $this->getAddon($directory_addon)->getDirectorySlug() . '/add')
                );
            }
        }
        if ($count = count($links)) {
            if ($count > 1) {
                array_unshift($links, $this->LinkTo(
                    __('Add Listing', 'sabai-directory'),
                    $this->Url('/' . $this->getAddon('Directory')->getDirectorySlug() . '/add'),
                    array(),
                    array('class' => 'sabai-btn-success')
                ));
            } else {
                $links[0]->setLabel(__('Add Listing', 'sabai-directory'))
                    ->setAttribute('class', 'sabai-btn-success');
            }
        }
        
        $context->addTemplate('directory_dashboard')->setAttributes(array(
            'paginator' => $pager,
            'links' => $links,
        ));
        
        return $form;
    }
}