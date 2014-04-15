<?php
class Sabai_Addon_File_UploadFormField implements Sabai_Addon_Form_IField
{
    private $_addon;
    private static $_uploadFields = array(), $_uploadFieldCount = 0;

    public function __construct(Sabai_Addon_File $addon)
    {
        $this->_addon = $addon;
    }

    public function formFieldGetFormElement($name, array &$data, Sabai_Addon_Form_Form $form)
    {
        ++self::$_uploadFieldCount;

        // Add file upload field
        $allowed_extensions = isset($data['#allowed_extensions']) ? $data['#allowed_extensions'] : array('jpeg', 'jpg', 'gif', 'png', 'txt', 'pdf', 'zip');
        $max_file_size = !empty($data['#max_file_size']) ? $data['#max_file_size'] : 1000;
        $allow_only_images = !empty($data['#allow_only_images']);

        $file_settings = $data;
        $file_settings['#type'] = 'file_file';
        $file_settings['#label'] = array(
            '', // no title for this element
            sprintf(
                __('Supported file formats: %s', 'sabai'),
                $allow_only_images ? 'gif jpeg png' : implode(' ', $allowed_extensions)
            )
        );
        $file_settings['#upload_dir'] = null; // file is uploaded by the storage plugin
        $file_settings['#max_file_size'] = $max_file_size * 1024;
        $file_settings['#allowed_extensions'] = $allowed_extensions;
        $file_settings['#allowed_only_images'] = $allow_only_images;
        $file_settings['#collapsible'] = false;
        $file_settings['#class'] = 'file-input-file';
        if (!isset($file_settings['#attributes']['id'])) {
            $file_settings['#attributes']['id'] = $form->getFieldId($name) . '-upload';
        }
        $file_settings['#prefix'] = sprintf('<div class="sabai-file-upload-container" id="%s">', $file_settings['#attributes']['id'] . '-uploader');
        $file_settings['#suffix'] = '</div>';

        // Define element settings
        $data = array(
            '#tree' => true,
            '#type' => $data['#type'],
            '#label' => $data['#label'],
            '#required' => $data['#required'],
            '#tree' => true,
            '#multiple' => !empty($data['#multiple']),
            '#class' => 'sabai-form-group ' . $data['#class'],
            '#children' => array(),
            '#max_num_files' => (int)@$file_settings['#max_num_files'],
        ) + $form->defaultElementSettings();

        // Add current file selection fields
        $current_file_options = $data['#_current_files'] = array();
        $row_attr = isset($file_settings['#row_attributes']) ? $file_settings['#row_attributes'] : array();
        if (!empty($file_settings['#default_value'])) {
            // Make sure the default value is an array of file IDs instead of values returned by this form element
            if (is_array($file_settings['#default_value'])) {
                if (isset($file_settings['#default_value']['id'])) {
                    $file_settings['#default_value'] = array($file_settings['#default_value']['id']);
                } else {
                    foreach ((array)$file_settings['#default_value'] as $k => $v) {
                        if (!is_int($k)) {
                            unset($file_settings['#default_value'][$k]);
                            continue;
                        }
                        if (is_array($v)) {
                            if (!empty($v['id'])) {
                                $file_settings['#default_value'][$k] = $v['id'];
                            } else {
                                unset($file_settings['#default_value'][$k]);
                            }
                        }
                    }
                }
            } else {
                $file_settings['#default_value'] = array($file_settings['#default_value']);
            }
            if (!empty($file_settings['#default_value'])) {
                foreach ($this->_addon->getModel('File')->fetchByIds($file_settings['#default_value']) as $file) {
                    $current_file_options[$file->id] = array(
                        'name' => Sabai::h($file->title),
                        'size' => $file->getHumanReadableSize(),
                    );
                    if ($allow_only_images) {
                        $current_file_options[$file->id]['thumbnail'] = sprintf('<img src="%s" alt="" />', $this->_addon->getApplication()->File_ThumbnailUrl($file->name));
                    }
                    $data['#_current_files'][$file->id] = $file;
                    if (!isset($row_attr[$file->id]['@row']['class'])) {
                        $row_attr[$file->id]['@row']['class'] = 'sabai-file-row';
                    } else {
                        $row_attr[$file->id]['@row']['class'] .= ' sabai-file-row'; 
                    }
                }
                if (!empty($current_file_options)) {
                    $_current_file_options = array();
                    // Reorder options as it was stored
                    foreach ($file_settings['#default_value'] as $file_id) {
                        if (isset($current_file_options[$file_id])) {
                            $_current_file_options[$file_id] = $current_file_options[$file_id];
                        }
                    }
                    $current_file_options = $_current_file_options;
                }
            }
        }

        $current_file_element = array(
            '#type' => 'grid',
            '#attributes' => array('id' => $file_settings['#attributes']['id'] . '-current'),
            '#class' => 'sabai-file-current-files',
            '#empty_text' => isset($file_settings['#empty_text']) ? $file_settings['#empty_text'] : __('There are currently no files uploaded.', 'sabai'),
            '#column_attributes' => $allow_only_images
                ? array('thumbnail' => array('style' => 'width:25%;'))
                : array(),
            '#row_attributes' => $row_attr,
        );
        if ($allow_only_images) {
            $current_file_element['#children'][0] = array(
                'check' => array(
                    '#type' =>  'checkbox',
                    '#title' => '',
                    '#class' => 'sabai-form-check',
                )  + $form->defaultElementSettings(),
                'thumbnail' => array(
                    '#type' => 'item',
                    '#title' => __('Thumbnail', 'sabai'),
                ),
                'name' => array(
                    '#type' => 'textfield',
                    '#title' => __('Name', 'sabai'),
                ) + $form->defaultElementSettings(),
                'size' => array(
                    '#type' => 'item',
                    '#title' => __('Size', 'sabai'),
                ) + $form->defaultElementSettings(),
            );
        } else {
            $current_file_element['#children'][0] = array(
                'check' => array(
                    '#type' => 'checkbox',
                    '#title' => '',
                    '#class' => 'sabai-form-check',
                )  + $form->defaultElementSettings(),
                'name' => array(
                    '#type' => 'textfield',
                    '#title' => __('Name', 'sabai'),
                ) + $form->defaultElementSettings(),
                'size' => array(
                    '#type' => 'item',
                    '#title' => __('Size', 'sabai'),
                ) + $form->defaultElementSettings(),
            );
        }
        foreach ($current_file_options as $current_file_id => $current_file_option) {
            $current_file_element['#default_value'][$current_file_id] = array(
                'check' => true,
                'name' => $current_file_option['name'],
                'size' => $current_file_option['size'],
            );
            if ($allow_only_images) {
                $current_file_element['#default_value'][$current_file_id]['thumbnail'] = $current_file_option['thumbnail'];
            }
        }
        $data['#children'][0]['current'] = $current_file_element + $form->defaultElementSettings();
        
        // Add upload field if not explicitly disabled
        if (!isset($data['#upload']) || $data['#upload'] !== false) {
            $data['#children'][0]['upload'] = $data['#_file_settings'] = $file_settings;

            // Register pre render callback if this is the first file_upload element
            if (empty(self::$_uploadFields)) {
                $form->settings['#pre_render'][] = array($this, 'preRenderCallback');
            }
            self::$_uploadFields[$file_settings['#attributes']['id']] = array(
                'name' => $name,
                'multiple' => $data['#multiple'],
                'uploader_settings' => array(
                    'allowed_extensions' => $allowed_extensions,
                    'max_file_size' => $max_file_size * 1024,
                    'image_only' => $allow_only_images,
                    'min_image_width' => isset($file_settings['#min_image_width']) ? $file_settings['#min_image_width'] : null,
                    'min_image_height' => isset($file_settings['#min_image_height']) ? $file_settings['#min_image_height'] : null,
                    'max_image_width' => isset($file_settings['#max_image_width']) ? $file_settings['#max_image_width'] : null,
                    'max_image_height' => isset($file_settings['#max_image_height']) ? $file_settings['#max_image_height'] : null,
                    'max_num_files' => $data['#max_num_files'],
                    'thumbnail' => !isset($file_settings['#thumbnail']) || false !== $file_settings['#thumbnail'],
                    'thumbnail_width' => !empty($file_settings['#thumbnail_width']) ? $file_settings['#thumbnail_width'] : null,
                    'medium_image' => !isset($file_settings['#medium_image']) || false !== $file_settings['#medium_image'],
                    'medium_image_width' => !empty($file_settings['#medium_image_width']) ? $file_settings['#medium_image_width'] : null,
                    'large_image' => !isset($file_settings['#large_image']) || false !== $file_settings['#large_image'],
                    'large_image_width' => !empty($file_settings['#large_image_width']) ? $file_settings['#large_image_width'] : null,
                ),
                'current_file_ids' => array_keys($current_file_options),
                'sortable' => !empty($file_settings['#sortable']),
            );
        }

        return $form->createFieldset($name, $data);
    }

    public function formFieldOnSubmitForm($name, &$value, array &$data, Sabai_Addon_Form_Form $form)
    {
        $_values = array();

        // File uploading enabled?
        if (!empty($data['#_file_settings'])) {
            if (!empty($value['current'])) $data['#_file_settings']['#required'] = false;
            // Validate uploaded file
            $this->_addon->getApplication()->Form_FieldImpl('file_file')->formFieldOnSubmitForm(
                $name . '[upload]',
                $value['upload'],
                $data['#_file_settings'],
                $form
            );
            if ($form->hasError($name . '[upload]')) {
                foreach ($form->getError($name . '[upload]') as $upload_error) {
                    $form->setError($upload_error, $name);
                }
                return;
            }

            // Save any newly uploaded file
            if (!empty($value['upload'])) {
                $data['#_saved_file_ids'] = array();
                if (!$data['#multiple']) {
                    $value['upload'] = array($value['upload']);
                }
                foreach ($value['upload'] as $file_uploaded) {
                    $file = $this->_addon->saveFile($file_uploaded);
                    $data['#_saved_file_ids'][$file->id] = $file->title;
                }
            }
        }

        if ($data['#multiple'] || empty($data['#_saved_file_ids'])) {
            // Any current file selected?
            if (!empty($value['current'])) {
                $data['#_new_file_ids'] = $new_titles = array();
                foreach ($value['current'] as $file_id => $file_info) {
                    if (empty($file_info['check'][0])) {
                        continue;
                    }
                    $_values[$file_id] = $file_info['name'];
                    if (!isset($data['#_current_files'][$file_id])) {
                        // File uploaded via Ajax
                        $data['#_new_file_ids'][] = $file_id;
                        $new_titles[$file_id] = $file_info['name'];
                    } else {
                        if ($data['#_current_files'][$file_id]->title !== $file_info['name']) {
                            // Update file title
                            $new_titles[$file_id] = $file_info['name'];
                        }
                    }

                    if (!$data['#multiple']) break;
                }
                
                if (!empty($new_titles)) {
                    $new_title_files = $this->_addon->getModel('File')
                        ->id_in(array_keys($new_titles))
                        ->fetch();
                        
                    foreach ($new_title_files as $_file) {
                        $_file->title = $new_titles[$_file->id];
                    }
                    $this->_addon->getModel()->commit();
                }
            }
        }
        
        if (!empty($data['#_saved_file_ids'])) {
            foreach ($data['#_saved_file_ids'] as $file_id => $file_title) {
                $_values[$file_id] = $file_title;
            }
        }

        $value = array();
        if (!empty($_values)) {
            if (empty($data['#multiple'])) {
                $_values = array_slice($_values, 0, 1, true);
            }
            foreach ($_values as $file_id => $file_title) {
                $value[] = array('id' => $file_id, 'title' => $file_title);
            }
        }
        
        if (!empty($data['#max_num_files']) && count($value) > $data['#max_num_files']) {
            $form->setError(sprintf(__('You may not upload more than %d files.', 'sabai'), $data['#max_num_files']), $name);
        }
    }

    public function formFieldOnCleanupForm($name, array $data, Sabai_Addon_Form_Form $form)
    {
        $model = $this->_addon->getModel();

        if ($form->isSubmitSuccess()) {
            // Form was successfully submitted

            // Remove association between the current upload token and files uploaded via ajax
            if (!empty($data['#_new_file_ids'])) {
                $model->getGateway('File')->updateByCriteria(
                    $model->createCriteria('File')->id_in($data['#_new_file_ids']),
                    array('file_token_id' => 0)
                );
            }

        } else {
            // Form submit failed, we need to remove files that have been uploaded during the upload process

            // Delete file data that have been created during upload
            if (!empty($data['#_saved_file_ids'])) {
                foreach ($model->File->fetchByIds(array_keys($data['#_saved_file_ids'])) as $file) {
                    $file->markRemoved();
                    $file->unlink();
                }
                $model->commit();
            }
        }

        // Remove the current upload token and files associated with the token (files uploaded via Ajax)
        $tokens = $model->Token->userId_is($this->_addon->getApplication()->getUser()->id)
            ->formBuildId_is($form->settings['#build_id'])
            ->formFieldName_is($name . '[upload]')
            ->fetch()
            ->with('Files');
        foreach ($tokens as $token) {
            $token->markRemoved();
            foreach ($token->Files as $file) {
                $file->unlink();
                $file->markRemoved();
            }
        }

        $model->commit();
    }

    public function formFieldOnRenderForm($name, array $data, Sabai_Addon_Form_Form $form)
    {
        $form->renderElement($data);
        $form->renderChildElements($name, $data);
    }

    public function preRenderCallback($form)
    {
        if (empty(self::$_uploadFields)) return;

        $js = array();
        $model = $this->_addon->getModel();
        foreach (self::$_uploadFields as $upload_id => $upload) {
            $token = $model->create('Token');
            $token->hash = md5(uniqid(mt_rand(), true));
            $token->form_build_id = $form->settings['#build_id'];
            $token->form_field_name = $upload['name'] . '[upload]';
            $token->expires = time() + 1800;
            $token->user_id = $this->_addon->getApplication()->getUser()->id;
            $token->settings = $upload['uploader_settings'];
            $token->markNew();
            $js[] = sprintf('(function($){
    SABAI.File.fineUploader({
        tableSelector: "#%1$s-current",
        maxNumFiles: %11$d,
        uploaderSelector: "#%1$s-uploader",
        formSelector: "#%12$s",
        maxNumFileExceededError: "%13$s",
        inputType: "%6$s",
        inputName: "%3$s",
        sortable: %14$s,
        fineUploaderOptions: {
            request: {
                endpoint: "%2$s",
                params: {"sabai_file_form_build_id": "%4$s", "sabai_file_upload_token": "%5$s"}
            },
            inputName: "qqfile",
            validation: {
                allowedExtensions: %9$s,
                sizeLimit: %10$d
            },
            failedUploadTextDisplay: {
                mode: "custom",
                maxChars: 60,
                responseProperty: "error",
                enableTooltip: true
            },
            text: {
                uploadButton: "<span class=\"sabai-btn sabai-btn-mini\"><i class=\"sabai-icon-upload-alt\"></i> %8$s</span>",
                cancelButton: "%15$s",
                retryButton: "%16$s",
                failUpload: "%17$s",
                dragZone: "%18$s",
                formatProgress: "{percent}% / {total_size}",
                waitingForResponse: "%19$s"
            }
        }
    });
})(jQuery);',
                Sabai::h($upload_id),
                $this->_addon->getApplication()->MainUrl('/sabai/file/upload'),
                Sabai::h($upload['name']),
                Sabai::h($form->settings['#build_id']),
                $token->hash,
                $upload['multiple'] ? 'checkbox' : 'radio',
                $upload['multiple'] ? '[current][0][check][]' : '[current][0]',
                __('Select File', 'sabai'),
                json_encode($upload['uploader_settings']['image_only'] ? array('png', 'jpg', 'jpeg', 'gif') : $upload['uploader_settings']['allowed_extensions']),
                $upload['uploader_settings']['max_file_size'],
                $upload['uploader_settings']['max_num_files'],
                $form->settings['#id'],
                sprintf(_n('You may not upload more than %d file.', 'You may not upload more than %d files', $upload['uploader_settings']['max_num_files'], 'sabai'), $upload['uploader_settings']['max_num_files']),
                $upload['sortable'] ? 'true' : 'false',
                __('Cancel', 'sabai'),
                __('Retry', 'sabai'),
                __('Upload failed', 'sabai'),
                __('Drop files here to upload', 'sabai'),
                __('Processing...', 'sabai')                    
            );
        }

        if (empty($js)) return;
        
        try {
            $model->commit();
        } catch (Exception $e) {
            $this->_addon->getApplication()->LogError($e);

            return;
        }

        $form->addJs(sprintf('$LAB.script("%s").wait().script("%s").wait(function(){
  %s
});',
            $this->_addon->getApplication()->getPlatform()->getAssetsUrl() . '/js/jquery.fineuploader-3.0.min.js',
            $this->_addon->getApplication()->getPlatform()->getAssetsUrl() . '/js/fineuploader.js',
            implode(PHP_EOL, $js)
        ));
    }
}