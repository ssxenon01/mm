<?php
class Sabai_Addon_Directory_Controller_Admin_EditListing extends Sabai_Addon_Content_Controller_Admin_EditPost
{
    protected function _doGetFormSettings(Sabai_Context $context, array &$formStorage)
    {
        $context->getRoute()->setControllerEventName('ContentAdminEditPost');
        $form = parent::_doGetFormSettings($context, $formStorage);        
        // Fetch current photos
        $this->_currentPhotos = $this->_getCurrentPhotos($context);
        // Fetch photo file IDs
        $file_ids = $row_attr = array();
        foreach ($this->_currentPhotos as $photo) {
            $file_ids[] = $file_id = $photo->file_image[0]['id'];
            if (!$photo->isPublished()) {
                $row_attr[$file_id]['@row']['class'] = 'sabai-muted';
            }
        }
        $form['photos'] = array(
            '#type' => 'file_upload',
            '#max_file_size' => $this->getAddon($context->bundle->addon)->getConfig('photo', 'max_file_size'),
            '#multiple' => true,
            '#allow_only_images' => true,
            '#default_value' => empty($file_ids) ? null : $file_ids,
            '#max_num_files' => $this->getAddon($context->bundle->addon)->getConfig('photo', 'max_num_owner'),
            '#weight' => 99,
            '#sortable' => true,
            '#title' => __('Photos', 'sabai-directory'),
            '#row_attributes' => $row_attr,
        );
        
        return $form;
    }
    
    protected function _getCurrentPhotos(Sabai_Context $context)
    {
        return $this->Entity_Query()
            ->propertyIs('post_entity_bundle_name', $this->getAddon($context->bundle->addon)->getPhotoBundleName())
            ->propertyIsIn('post_status', array(Sabai_Addon_Content::POST_STATUS_DRAFT, Sabai_Addon_Content::POST_STATUS_PENDING, Sabai_Addon_Content::POST_STATUS_PUBLISHED))
            ->fieldIsNotNull('directory_photo', 'official')
            ->fieldIs('content_parent', $context->entity->getId())
            ->sortByField('directory_photo', 'ASC', 'display_order')
            ->fetch();
    }
    
    public function submitForm(Sabai_Addon_Form_Form $form, Sabai_Context $context)
    {
        $entity = parent::submitForm($form, $context);
        
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
                        $this->getAddon($context->bundle->addon)->getPhotoBundleName(),
                        array(
                            'content_post_status' => $entity->getStatus(),
                            'content_post_title' => $new_photo_title,
                            'file_image' => array('id' => $new_photo_file_id),
                            'content_parent' => $context->entity->getId(),
                            'directory_photo' => array('official' => 1, 'display_order' => $display_order),
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
                    ) {
                        $this->_application->getAddon('Entity')->updateEntity(
                            $current_photo,
                            array(
                                'directory_photo' => array('official' => $current_photo->directory_photo[0]['official'], 'display_order' => $display_order),
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
            $this->getAddon('Content')->updateParentPost($context->entity, false, true, true);
        }
    }
}
