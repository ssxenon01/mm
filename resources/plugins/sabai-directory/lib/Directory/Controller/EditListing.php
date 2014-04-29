<?php
class Sabai_Addon_Directory_Controller_EditListing extends Sabai_Addon_Content_Controller_EditPost
{    
    protected function _doGetFormSettings(Sabai_Context $context, array &$formStorage)
    {
        $form = parent::_doGetFormSettings($context, $formStorage);
        unset($form['directory_claim']);
        
        // Add photo upload field if the user has a valid permission
        // Fetch current photos
        $this->_currentPhotos = $this->_getCurrentPhotos($context);
        // Fetch photo file IDs
        $file_ids = array();
        foreach ($this->_currentPhotos as $photo) {
            $file_ids[] = $photo->file_image[0]['id'];
        }
        if ($this->HasPermission($this->getAddon()->getPhotoBundleName() . '_add')) {
            $photo_config = $this->getAddon()->getConfig('photo');
            if ($photo_config['max_num'] > 0) {
                $form['photos'] = array(
                    '#type' => 'file_upload',
                    '#title' => __('Photos', 'sabai-directory'),
                    '#description' => sprintf(
                        __('Maximum number of files %d, maximum file size %s.', 'sabai-directory'),
                        $photo_config['max_num'],
                        $photo_config['max_file_size'] >= 1024 ? round($photo_config['max_file_size'] / 1024, 1) . 'MB' : $photo_config['max_file_size'] . 'KB'
                    ),
                    '#max_file_size' => $photo_config['max_file_size'],
                    '#multiple' => true,
                    '#allow_only_images' => true,
                    '#default_value' => $file_ids,
                    '#max_num_files' => $photo_config['max_num'],
                    '#weight' => 99,
                    '#sortable' => true,
                );
            }
        }
        
        return $form;
    }
    
    protected function _getCurrentPhotos(Sabai_Context $context)
    {
        return $this->Entity_Query()
            ->propertyIs('post_entity_bundle_name', $this->getAddon()->getPhotoBundleName())
            ->propertyIs('post_status', Sabai_Addon_Content::POST_STATUS_PUBLISHED)
            ->fieldIs('content_parent', $context->entity->getId())
            ->fieldIs('directory_photo', 2, 'official')
            ->fieldIsNull('content_reference')
            ->sortByField('directory_photo', 'ASC', 'display_order')
            ->fetch();
    }
    
    public function submitForm(Sabai_Addon_Form_Form $form, Sabai_Context $context)
    {        
        // Update listing
        $listing = $this->getAddon('Entity')->updateEntity($context->entity, $form->values);
             
        // Update photos
        $current_photos = $submitted_photos = $submitted_photos_order = array();
        // Fetch current photos
        foreach ($this->_currentPhotos as $current_photo) {
            $current_photos[$current_photo->file_image[0]['id']] = $current_photo;
        }
        // Fetch submitted photos
        if (!empty($form->values['photos'])) {
            $display_order = 0;
            foreach ($form->values['photos'] as $file) {
                $submitted_photos[$file['id']] = $file['title'];
                $submitted_photos_order[$file['id']] = ++$display_order;
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
                foreach ($new_photos as $new_photo_file_id => $new_photo_title) {
                    $this->_application->getAddon('Entity')->createEntity(
                        $this->Entity_Addon($context->entity)->getPhotoBundleName(),
                        array(
                            'content_post_status' => $listing->getStatus(),
                            'content_post_title' => $new_photo_title,
                            'file_image' => array('id' => $new_photo_file_id),
                            'content_parent' => $listing->getId(),
                            'directory_photo' => array('official' => 2, 'display_order' => $display_order),
                        ),
                        array('content_skip_update_parent' => true) // we'll update parent listing later
                    );
                }
            }
            // Update display order and title of current photos if changed
            if ($current_photos = array_intersect_key($current_photos, $submitted_photos)) {
                foreach ($current_photos as $file_id => $current_photo) {
                    $display_order = $submitted_photos_order[$file_id];
                    $photo_title = $submitted_photos[$file_id];
                    if ($display_order != $current_photo->directory_photo[0]['display_order']
                        || $photo_title != $current_photo->getTitle()
                        || $current_photo->directory_photo[0]['official'] != 2 // mark the photo unofficial
                    ) {
                        $this->_application->getAddon('Entity')->updateEntity(
                            $current_photo,
                            array(
                                'directory_photo' => array('official' => 2, 'display_order' => $display_order),
                                'content_post_title' => $photo_title,
                            ),
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
        
        $context->setSuccess($this->Entity_Url($listing));
    }
}
