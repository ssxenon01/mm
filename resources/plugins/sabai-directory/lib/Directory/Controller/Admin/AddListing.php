<?php
class Sabai_Addon_Directory_Controller_Admin_AddListing extends Sabai_Addon_Content_Controller_Admin_AddPost
{
    protected function _doGetFormSettings(Sabai_Context $context, array &$formStorage)
    {
        $context->getRoute()->setControllerEventName('ContentAdminAddPost');
        $form = parent::_doGetFormSettings($context, $formStorage);
        $form['photos'] = array(
            '#type' => 'file_upload',
            '#max_file_size' => $this->getAddon()->getConfig('photo', 'max_file_size'),
            '#multiple' => true,
            '#allow_only_images' => true,
            '#default_value' => null,
            '#max_num_files' => $this->getAddon()->getConfig('photo', 'max_num_owner'),
            '#weight' => 99,
            '#sortable' => true,
            '#title' => __('Photos', 'sabai-directory'),
        );
        // Do not auto-populate e-mail/website fields
        $form['directory_contact'][0]['email']['#auto_populate'] = null;
        $form['directory_contact'][0]['website']['#auto_populate'] = null;
        return $form;
    }
    
    public function submitForm(Sabai_Addon_Form_Form $form, Sabai_Context $context)
    {
        $entity = parent::submitForm($form, $context);

        // Add new photos if any
        if (!empty($form->values['photos'])) {
            $display_order = 0;
            foreach ($form->values['photos'] as $file) {
                $this->_application->getAddon('Entity')->createEntity(
                    $this->getAddon()->getPhotoBundleName(),
                    array(
                        'content_post_title' => $file['title'],
                        'content_post_status' => Sabai_Addon_Content::POST_STATUS_PUBLISHED,
                        'file_image' => $file,
                        'content_parent' => $entity->getId(),
                        'directory_photo' => array('official' => 1, 'display_order' => ++$display_order),
                    ),
                    array('content_skip_update_parent' => true) // we'll update parent listing later
                );
            }
            // Update parent listing
            $this->getAddon('Content')->updateParentPost($entity, false, true, true);
        }
    }
}