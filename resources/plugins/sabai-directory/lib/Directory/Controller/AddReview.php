<?php
class Sabai_Addon_Directory_Controller_AddReview extends Sabai_Addon_Content_Controller_AddChildPost
{
    protected function _doGetFormSettings(Sabai_Context $context, array &$formStorage)
    {
        $form = parent::_doGetFormSettings($context, $formStorage);
        $form['listing'] = array(
            '#type' => 'item',
            '#title' => __('Listing', 'sabai-directory'),
            '#markup' => $this->Entity_Permalink($context->entity),
        );
        // Add photo upload field if the user has a valid permission
        if ($this->getUser()->hasPermission($this->getAddon()->getPhotoBundleName() . '_add')) {
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
                    '#default_value' => null,
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
        
        if ($review->isPublished()) {
            // Cast vote for the parent listing
            $this->Voting_CastVote(
                $context->entity,
                'rating',
                $review->getSingleFieldValue('directory_rating'),
                array('name' => '', 'reference_id' => $review->getId(), 'user_id' => $review->getAuthorId())
            );
        }
        // Create photo entities
        if (isset($form->settings['photos']) && !empty($form->values['photos'])) {
            foreach ($form->values['photos'] as $file) {
                $this->getAddon('Entity')->createEntity(
                    $this->getAddon()->getPhotoBundleName(),
                    array(
                        'file_image' => $file,
                        'content_parent' => $context->entity->getId(),
                        'content_reference' => $review->getId(),
                        'content_post_status' => $review->getStatus(),
                        'content_post_title' => $file['title'],
                        'content_guest_author' => ($guest_author = $review->getFieldValue('content_guest_author')) ? $guest_author[0] : null,
                    ),
                    array('content_skip_update_parent' => true) // we'll update parent listing later
                );
            }
            // Update parent listing
            $this->getAddon('Content')->updateParentPost($context->entity, false, true, true);
        }
    }
}
