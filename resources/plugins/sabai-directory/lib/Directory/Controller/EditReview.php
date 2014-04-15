<?php
class Sabai_Addon_Directory_Controller_EditReview extends Sabai_Addon_Content_Controller_EditChildPost
{
    protected $_currentPhotos;
    
    protected function _doGetFormSettings(Sabai_Context $context, array &$formStorage)
    {
        if (!$listing = $this->Content_ParentPost($context->entity)) {
            return false;
        }
        $form = parent::_doGetFormSettings($context, $formStorage);
        $form['listing'] = array(
            '#type' => 'item',
            '#title' => __('Listing', 'sabai-directory'),
            '#markup' => $this->Entity_Permalink($listing),
        );
        // Fetch current photos
        $this->_currentPhotos = $this->Entity_Query()
            ->propertyIs('post_entity_bundle_name', $this->getAddon()->getPhotoBundleName())
            ->fieldIs('content_reference', $context->entity->getId())
            ->sortByProperty('post_id', 'ASC')
            ->fetch();
        // Add photo upload field if the user has a valid permission
        if ($this->getUser()->hasPermission($this->getAddon()->getPhotoBundleName() . '_add')) {
            // Fetch photo file IDs
            $file_ids = array();
            foreach ($this->_currentPhotos as $photo) {
                $file_ids[] = $photo->file_image[0]['id'];
            }
            $photo_config = $this->getAddon()->getConfig('photo');
            if ($photo_config['max_num_review'] > 0) {
                $form['photos'] = array(
                    '#type' => 'file_upload',
                    '#title' => __('Photos', 'sabai-directory'),
                    '#description' => sprintf(
                        __('Maximum number of files %d, maximum file size %s.', 'sabai-directory'),
                        $photo_config['max_num_review'],
                        $photo_config['max_file_size'] >= 1024 ? round($photo_config['max_file_size'] / 1024, 1) . 'MB' : $photo_config['max_file_size'] . 'KB'
                    ),
                    '#max_file_size' => $photo_config['max_file_size'],
                    '#multiple' => true,
                    '#allow_only_images' => true,
                    '#default_value' => empty($file_ids) ? null : $file_ids,
                    '#max_num_files' => $photo_config['max_num_review'],
                    '#weight' => 99,
                );
            }
        }
        
        return $form;
    }
    
    public function submitForm(Sabai_Addon_Form_Form $form, Sabai_Context $context)
    {
        $review = parent::submitForm($form, $context);
        if (!$listing = $this->Content_ParentPost($review)) {
            return false;
        }
        
        $this->Voting_CastVote(
            $listing,
            'rating',
            $review->getSingleFieldValue('directory_rating'),
            array('name' => '', 'reference_id' => $review->getId(), 'user_id' => $review->getAuthorId(), 'is_edit' => true)
        );
        
        // Update photos
        $current_photos = $submitted_photos = array();
        // Fetch current photos
        foreach ($this->_currentPhotos as $current_photo) {
            $current_photos[$current_photo->file_image[0]['id']] = $current_photo;
        }
        // Fetch submitted photos
        if (isset($form->settings['photos']) && !empty($form->values['photos'])) {
            foreach ($form->values['photos'] as $file) {
                $submitted_photos[$file['id']] = $file['title'];
            }
        }
        // Remove deleted photos if any
        if ($deleted_photos = array_diff_key($current_photos, $submitted_photos)) {
            $this->getAddon('Entity')->deleteEntities(
                'content',
                $deleted_photos,
                array('content_skip_update_parent' => true) // we'll update parent listing later
            );
        }
        if (!empty($submitted_photos)) {
            // Add new photos if any
            if ($new_photos = array_diff_key($submitted_photos, $current_photos)) {
                foreach ($new_photos as $new_photo_id => $new_photo_title) {
                    $this->_application->getAddon('Entity')->createEntity(
                        $this->getAddon()->getPhotoBundleName(),
                        array(
                            'file_image' => array('id' => $new_photo_id),
                            'content_parent' => $listing->getId(),
                            'content_reference' => $review->getId(),
                            'content_post_status' => $review->getStatus(),
                            'content_post_title' => $new_photo_title,
                            'content_guest_author' => ($guest_author = $review->getFieldValue('content_guest_author')) ? $guest_author[0] : null,
                        ),
                        array('content_skip_update_parent' => true) // we'll update parent listing later
                    );
                }
            }
            // Update title of current photos if changed
            if ($current_photos = array_intersect_key($current_photos, $submitted_photos)) {
                foreach ($current_photos as $file_id => $current_photo) {
                    $photo_title = $submitted_photos[$file_id];
                    if ($photo_title != $current_photo->getTitle()) {
                        $this->_application->getAddon('Entity')->updateEntity(
                            $current_photo,
                            array('content_post_title' => $photo_title),
                            array('content_skip_update_parent' => true) // we'll update parent listing later
                        );
                    }
                }
            }
        }
        
        if (!empty($deleted_photos) || !empty($new_photos)) {
            // Update parent listing
            $this->getAddon('Content')->updateParentPost($listing, false, true, true);
        }
    }
}
