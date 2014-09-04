<?php
class Sabai_Addon_File extends Sabai_Addon
    implements Sabai_Addon_Form_IFields,
               Sabai_Addon_System_IMainRouter,
               Sabai_Addon_System_IAdminRouter,
               Sabai_Addon_Field_ITypes,
               Sabai_Addon_Field_IWidgets,
               Sabai_Addon_File_IStorage
{
    const VERSION = '1.2.32', PACKAGE = 'sabai';
    
    protected $_path;
        
    protected function _init()
    {
        $this->_path = $this->_application->Path(dirname(__FILE__) . '/File');
    }
            
    public function isUninstallable($currentVersion)
    {
        return false;
    }
    
    /* Start implementation of Sabai_Addon_System_IMainRouter */
    
    public function systemGetMainRoutes()
    {
        $routes = array(
            '/sabai/file/upload' => array(
                'controller' => 'UploadFile',
            ),
        );
        foreach ($this->_application->getModel('FieldConfig', 'Entity')->type_in(array('file_image', 'file_file'))->fetch()->with('Fields', 'Bundle') as $field_config) {            
            foreach ($field_config->Fields as $field) {
                if (!$field->Bundle) continue;
                $base_path = empty($field->Bundle->info['permalink_path'])
                    ? $field->Bundle->getPath() . '/:entity_id'
                    : $field->Bundle->info['permalink_path'] . '/:slug';
                if (!isset($routes[$base_path . '/file/:file_id'])) {
                    $routes[$base_path . '/file/:file_id'] = array(
                        'controller' => 'File',
                        'type' => Sabai::ROUTE_CALLBACK,
                        'callback_path' => 'file',
                        'access_callback' => true,
                        'data' => array(
                            'fields' => array(),
                        ),
                    );
                }
                $routes[$base_path . '/file/:file_id']['data']['fields'][] = $field->getFieldName();
            }
        }

        return $routes;
    }

    public function systemOnAccessMainRoute(Sabai_Context $context, $path, $accessType, array &$route)
    {
        switch ($path) {
            case 'file':
                if (!$file_id = $context->getRequest()->asInt('file_id')) {
                    return false;
                }
                $this->_application->Entity_LoadFields($context->entity);
                foreach ((array)$route['data']['fields'] as $field_name) {
                    if (!$files = $context->entity->getFieldValue($field_name)) {
                        continue;
                    }
                    foreach ($files as $file) {
                        if ($file['id'] === $file_id) {
                            $context->file = $file;
                            return true;
                        }
                    }
                }                
                return false;
        }
    }

    public function systemGetMainRouteTitle(Sabai_Context $context, $path, $title, $titleType, array $route)
    {
        
    }

    /* End implementation of Sabai_Addon_System_IMainRouter */

    /* Start implementation of Sabai_Addon_Field_ITypes */

    public function fieldGetTypeNames()
    {
        return array('file_file', 'file_image');
    }

    public function fieldGetType($typeName)
    {
        return new Sabai_Addon_File_FieldType($this, $typeName);
    }

    /* End implementation of Sabai_Addon_Field_ITypes */

    /* Start implementation of Sabai_Addon_Field_IWidgets */

    public function fieldGetWidgetNames()
    {
        return array('file_upload');
    }

    public function fieldGetWidget($widgetName)
    {
        return new Sabai_Addon_File_FieldWidget($this, $widgetName);
    }

    /* End implementation of Sabai_Addon_Field_IWidgets */

    /* Start implementation of Sabai_Addon_Form_IFields */

    public function formGetFieldTypes()
    {
        return array('file_file', 'file_upload');
    }

    public function formGetField($type)
    {
        switch ($type) {
            case 'file_file':
                return new Sabai_Addon_File_FileFormField($this);
            case 'file_upload':
                return new Sabai_Addon_File_UploadFormField($this);
        }
    }

    /* End implementation of Sabai_Addon_Form_IFields */

    public function saveFile(array $fileData, Sabai_Addon_File_Model_Token $token = null)
    {
        if (false === $file_content = file_get_contents($fileData['tmp_name'])) {
            throw new Sabai_RuntimeException(sprintf(__('Failed fetching content of file %s.', 'sabai'), $fileData['name']));
        }
        if (false === $file_hash = md5_file($fileData['tmp_name'])) {
            throw new Sabai_RuntimeException(sprintf(__('Failed generating hash value for file %s.', 'sabai'), $fileData['name']));
        }

        // Create file metadata
        $file = $this->getModel()->create('File');
        $file->title = $fileData['name'];
        $file->extension = $fileData['file_ext'];
        $file->size = $fileData['size'];
        $file->type = $fileData['type'];
        $file->is_image = $fileData['is_image'];
        $file->width = $fileData['width'];
        $file->height = $fileData['height'];
        $file->user_id = $this->_application->getUser()->id;
        $file->hash = $file_hash;
        $file->name = md5($file->hash . $file->user_id) . '.' . $file->extension;
        $file->content = $file_content;
        if (isset($token)) {
            $file->Token = $token;
        }
        $file->markNew();
        $file->commit();

        // Put file to storage
        $this->getStorage()->fileStoragePut($file->name, $file_content, array(
            'type' => $file->type,
            'is_image' => $file->is_image,
            'width' => $file->width,
            'height' => $file->height,
            'thumbnail' => $token->settings['thumbnail'],
            'thumbnail_width' => $token->settings['thumbnail_width'],
            'medium_image' => $token->settings['medium_image'],
            'medium_image_width' => $token->settings['medium_image_width'],
            'large_image' => $token->settings['large_image'],
            'large_image_width' => $token->settings['large_image_width'],
        ));

        return $file;
    }

    public function onSabaiRunCron($lastrun, $logs)
    {
        // Delete expired tokens and associated files
        $tokens = $this->getModel('Token')->expires_isSmallerThan(time())->fetch()->with('Files');
        if ($count = count($tokens)) {        
            foreach ($tokens as $token) {
                $token->markRemoved();
                foreach ($token->Files as $file) {
                    $file->unlink();
                    $file->markRemoved();
                }
            }
            $this->getModel()->commit();
        }
        $logs[] = sprintf(__('Deleted %d expired file upload token(s).', 'sabai'), $count);
    }

    public function getDefaultConfig()
    {
        if (preg_match('/^([0-9]+)([a-zA-Z]*)$/', ini_get('upload_max_filesize'), $matches)) {
            // see http://www.php.net/manual/en/faq.using.php#faq.using.shorthandbytes
            switch (strtoupper($matches['2'])) {
                case 'G':
                    $max_filesize_kb = $matches['1'] * 1048576;
                    break;
                case 'M':
                    $max_filesize_kb = $matches['1'] * 1024;
                    break;
                case 'K':
                    $max_filesize_kb = $matches['1'];
                    break;
                default:
                    if (1 > $max_filesize_kb = $matches['1'] / 1024) {
                        $max_filesize_kb = 1;
                    }
            }
        }
        
        return array(
            'tmp_dir' => '',
            'upload_dir' => '',
            'thumbnail_dir' => '',
            'thumbnail_width' => 150,
            'thumbnail_height' => 150,
            'image_medium_width' => 300,
            'image_large_width' => 1024,
            'maxSizeKB' => $max_filesize_kb,
            'no_pretty_url' => true,
            'resize_method' => 'crop',
        );
    }
    
    public function getUploadDir()
    {
        return $this->_config['upload_dir'] ? $this->_config['upload_dir'] : $this->getVarDir('files');
    }
    
    public function getThumbnailDir()
    {
        return $this->_config['thumbnail_dir'] ? $this->_config['thumbnail_dir'] : $this->getVarDir('thumbnails');
    }
    
    public function getTmpDir()
    {
        return $this->_config['tmp_dir'] ? $this->_config['tmp_dir'] : $this->getVarDir('tmp');
    }
    
    public function hasVarDir()
    {
        return array('tmp', 'files', 'thumbnails');
    }
    
    public function getStorage()
    {
        return $this;
    }
    
    public function fileStoragePut($name, $content, array $options)
    {
        $upload_dir = $this->getUploadDir();
        $this->_application->ValidateDirectory($upload_dir, true);
        
        if ($options['is_image'] && !empty($options['thumbnail'])) {
            $thumbnail_dir = $this->getThumbnailDir();
            $this->_application->ValidateDirectory($thumbnail_dir, true);
        }
        
        $upload_file = $upload_dir . '/' . $name;

        if (false === file_put_contents($upload_file, $content)) {
            throw new Sabai_RuntimeException(sprintf(__('Failed saving file %s to the upload directory.', 'sabai'), $name));
        }
 
        if ($options['is_image']) {
            // Read EXIF data and adjust orientation
            if (function_exists('exif_read_data')
                && ($exif = @exif_read_data($upload_file))
                && !empty($exif['Orientation'])
            ) {
                switch (intval($exif['Orientation'])) {
                    case 6:
                        $rotate = 90;
                        break;
                    case 3:
                        $rotate = 180;
                        break;
                    case 8:
                        $rotate = 270;
                        break;
                    default:
                        $rotate = false;
                }
                if ($rotate) {
                    if (extension_loaded('imagick') && class_exists('Imagick') && class_exists('ImagickPixel')) {
                        $imagick = new Imagick();
                        $imagick->readImage($upload_file);
                        $imagick->rotateImage(new ImagickPixel(), $rotate);
                        $imagick->setImageOrientation(defined('imagick::ORIENTATION_TOPLEFT') ? imagick::ORIENTATION_TOPLEFT : 1);
                        $imagick->writeImage();
                        $imagick->clear();
                        $imagick->destroy();
                    } elseif (extension_loaded('gd') && function_exists('gd_info')) {
                        // No Imagick, fallback to GD
                        // GD needs negative degrees
                        $rotate = -$rotate;

                        switch ($options['type']) {
                            case 'image/jpeg':
                                if (($source = imagecreatefromjpeg($upload_file))
                                    && ($rotated = imagerotate($source, $rotate, 0))
                                ) {
                                    imagejpeg($rotated, $upload_file);
                                    imagedestroy($source);
                                    imagedestroy($rotated);
                                }
                                break;
                            case 'image/png':
                                if (($source = imagecreatefrompng($upload_file))
                                    && ($rotated = imagerotate($source, $rotate, 0))
                                ) {
                                    imagepng($rotated, $upload_file);
                                    imagedestroy($source);
                                    imagedestroy($rotated);
                                }
                                break;
                            case 'image/gif':
                                if (($source = imagecreatefromgif($upload_file))
                                    && ($rotated = imagerotate($source, $rotate, 0))
                                ) {
                                    imagegif($rotated, $upload_file);
                                    imagedestroy($source);
                                    imagedestroy($rotated);
                                }
                                break;
                            default:
                                break;
                        }
                    }
                }
            } 

            if (!isset($options['thumbnail']) || $options['thumbnail'] !== false) {
                $thumbnail_width = empty($options['thumbnail_width']) ? $this->_config['thumbnail_width'] : $options['thumbnail_width'];
                $thumbnail_height = empty($options['thumbnail_height']) ? $this->_config['thumbnail_height'] : $options['thumbnail_height'];
                if ($options['width'] <= $thumbnail_width && $options['height'] <= $thumbnail_height) {
                    // Do not resize if smaller than the requested dimension
                    file_put_contents($thumbnail_dir . '/' . $name, $content);
                } else {
                    $this->_application->getPlatform()->resizeImage(
                        $upload_file,
                        $thumbnail_dir . '/' . $name,
                        $this->_config['thumbnail_width'],
                        $this->_config['thumbnail_height'],
                        @$this->_config['resize_method'] === 'crop'
                    );
                }
            }
            if (!empty($options['medium_image'])) {
                $medium_image_width = empty($options['medium_image_width']) ? $this->_config['image_medium_width'] : $options['medium_image_width'];
                $this->_application->getPlatform()->resizeImage($upload_file, $upload_dir . '/m_' . $name, $medium_image_width, null);
            }
            if (!empty($options['large_image'])) {
                $large_image_width = empty($options['large_image_width']) ? $this->_config['image_large_width'] : $options['large_image_width'];
                $this->_application->getPlatform()->resizeImage($upload_file, $upload_dir . '/l_' . $name, $large_image_width, null);
            }
        }
    }
    
    public function fileStorageGetStream($name, $size = null)
    {
        if (isset($size)) {
            if ($size === 'medium') {
                $name = 'm_' . $name;
            } elseif ($size === 'large') {
                $name = 'l_' . $name;
            }
        }
        $file = $this->getUploadDir() . '/' . $name;

        if (!file_exists($file)) {
            throw new Sabai_RuntimeException(sprintf(__('File %s does not exist.', 'sabai'), $name));
        }

        if (false === $resource = fopen($file, 'rb')) {
            throw new Sabai_RuntimeException(sprintf(__('Failed opening stream for file %s.', 'sabai'), $name));
        }

        return $resource;
    }
    
    public function fileStorageGetThumbnailUrl($name)
    {
        return $this->_application->FileUrl($this->getThumbnailDir() . '/' . $name);
    }
    
    public function fileStorageDelete($name)
    {
        @unlink($this->getUploadDir() . '/' . $name);
    }
    
    public function getUploader(array $options = array())
    {
        $default_options = array(
            'allowed_extensions' => array('gif', 'jpeg', 'jpg', 'pdf', 'png', 'txt', 'zip'),
            'max_file_size' => $this->getConfig('maxSizeKB') * 1024,
            'image_extensions' => array('gif', 'jpg', 'jpeg', 'png'),
            'image_only' => false,
            'max_image_width' => null,
            'max_image_height' => null,
            'min_image_width' => null,
            'min_image_height' => null,
            'upload_dir' => null,
            'upload_file_name_prefix' => '',
            'upload_file_name_max_length' => null,
            'upload_file_permission' => 0644,
            'hash_upload_file_name' => true,
            'skip_mime_type_check' => false,
        );
        $options += $default_options;
        require_once $this->_path . '/Uploader.php';
        $uploader = new Sabai_Addon_File_Uploader($this);

        return $uploader->setAllowedExtensions($options['allowed_extensions'])
            ->setMaxSize($options['max_file_size'])
            ->setImageExtensions($options['image_extensions'])
            ->setImageOnly($options['image_only'])
            ->setMaxImageWidth($options['max_image_width'])
            ->setMaxImageHeight($options['max_image_height'])
            ->setMinImageWidth($options['min_image_width'])
            ->setMinImageHeight($options['min_image_height'])
            ->setUploadDir($options['upload_dir'])
            ->setUploadFileNamePrefix($options['upload_file_name_prefix'])
            ->setUploadFileNameMaxLength($options['upload_file_name_max_length'])
            ->hashUploadFileName($options['hash_upload_file_name'])
            ->setUploadFilePermission($options['upload_file_permission'])
            ->skipMimeTypeCheck($options['skip_mime_type_check']);
    }

    public function getSubmittedFiles($name)
    {
        if (empty($_FILES)) return array();

        if (isset($_FILES[$name])) return $_FILES[$name];

        if (false === $pos = strpos($name, '[')) return array();

        $base = substr($name, 0, $pos);
        $key = str_replace(array(']', '['), array('', '"]["'), substr($name, $pos + 1, -1));
        $code = array(sprintf('if (!isset($_FILES["%s"]["name"]["%s"])) return array();', $base, $key));
        $code[] = '$file = array();';
        foreach (array('name', 'type', 'size', 'tmp_name', 'error') as $property) {
            $code[] = sprintf('$file["%1$s"] = $_FILES["%2$s"]["%1$s"]["%3$s"];', $property, $base, $key);
        }
        $code[] = 'return $file;';

        return eval(implode(PHP_EOL, $code));
    }
    
    public function onEntityDeleteFieldConfigsSuccess($removedFields)
    {
        foreach ($removedFields as $removed_field) {
            if (in_array($removed_field->type, array('file_file', 'file_image'))) {
                // Reload system routing tables to reflect changes
                $this->_application->getAddon('System')->reloadRoutes($this);
                return; // once is enough
            }
        }
    }
    
    public function onEntityCreateBundlesSuccess($entityType, $bundles)
    {        
        foreach ($bundles as $bundle) {
            // Add the file field
            if (!empty($bundle->info['file_file'])) {
                $field_settings = $bundle->info['file_file'];
                $this->_application->getAddon('Entity')->createEntityField(
                    $bundle,
                    'file_file',
                    array(
                        'type' => 'file_file',
                        'admin_title' => isset($field_settings['admin_title']) ? $field_settings['admin_title'] : __('File Attachments', 'sabai'),
                        'title' => isset($field_settings['title']) ? $field_settings['title'] : __('File Attachments', 'sabai'),
                        'description' => isset($field_settings['description']) ? $field_settings['description'] : '',
                        'widget' => isset($field_settings['widget']) ? $field_settings['widget'] : 'file_upload',
                        'widget_settings' => isset($field_settings['widget_settings']) ? $field_settings['widget_settings'] : array(),
                        'required' => !empty($field_settings['required']),
                        'weight' => isset($field_settings['weight']) ? $field_settings['weight'] : null,
                        'max_num_items' => isset($field_settings['max_num_items']) ? $field_settings['max_num_items'] : 0,
                    ),
                    Sabai_Addon_Entity::FIELD_REALM_ALL
                );
            }
            if (!empty($bundle->info['file_image'])) {
                $field_settings = $bundle->info['file_image'];
                $this->_application->getAddon('Entity')->createEntityField(
                    $bundle,
                    'file_image',
                    array(
                        'type' => 'file_image',
                        'admin_title' => isset($field_settings['admin_title']) ? $field_settings['admin_title'] : __('File Attachments', 'sabai'),
                        'title' => isset($field_settings['title']) ? $field_settings['title'] : __('File Attachments', 'sabai'),
                        'description' => isset($field_settings['description']) ? $field_settings['description'] : '',
                        'widget' => isset($field_settings['widget']) ? $field_settings['widget'] : 'file_upload',
                        'widget_settings' => isset($field_settings['widget_settings']) ? $field_settings['widget_settings'] : array(),
                        'required' => !empty($field_settings['required']),
                        'weight' => isset($field_settings['weight']) ? $field_settings['weight'] : null,
                        'max_num_items' => isset($field_settings['max_num_items']) ? $field_settings['max_num_items'] : 0,
                    ),
                    Sabai_Addon_Entity::FIELD_REALM_ALL
                );
            }
        }

        // Reload system routing tables to reflect changes
        $this->_application->getAddon('System')->reloadRoutes($this);
    }
    
    public function onEntityUpdateBundlesSuccess($entityType, $bundles)
    {
        $this->onEntityCreateBundlesSuccess($entityType, $bundles);
    }
    
    public function onEntityDeleteBundlesSuccess($entityType, $bundles)
    {
        // Reload system routing tables to reflect changes
        $this->_application->getAddon('System')->reloadRoutes($this);
    }
    
    public function onFieldUISubmitFieldSuccess($field, $isEdit)
    {
        if ($isEdit || !in_array($field->getFieldType(), array('file_file', 'file_image'))) {
            return;
        }
        // Reload system routing tables to reflect changes
        $this->_application->getAddon('System')->reloadRoutes($this);
    }
    
    public function onEntityRenderContentHtml(Sabai_Addon_Entity_Model_Bundle $bundle, Sabai_Addon_Entity_Entity $entity, $displayMode, $id, &$classes, &$links)
    {
        if ($displayMode === 'preview'
            || $displayMode === 'full'
            || (isset($bundle->info['file_content_icons']) && false === $bundle->info['file_content_icons'])
        ) {
            return;
        }
        
        if ($file_field_names = $entity->getFieldNamesByType('file_file')) {
            foreach ($file_field_names as $field_name) {
                if ($entity->getFieldValue($field_name)) {
                    $entity->data['content_icons']['file_file'] = array(
                        'icon' => 'paper-clip',
                        'title' => __('This post has one or more files attached.', 'sabai'),
                        'class' => 'sabai-file-file-attached',
                    );
                    break;
                }
            }
        }
        
        if ($image_field_names = $entity->getFieldNamesByType('file_image')) {
            foreach ($image_field_names as $field_name) {
                if ($entity->getFieldValue($field_name)) {
                    $entity->data['content_icons']['file_image'] = array(
                        'icon' => 'picture',
                        'title' => __('This post has one or more images attached.', 'sabai'),
                        'class' => 'sabai-file-image-attached',
                    );
                    break;
                }
            }
        }
    }
    
    public function hasSettingsPage($currentVersion)
    {
        return array('url' => '/settings/file', 'modal' => true, 'modal_width' => 600);
    }
    
    /* Start implementation of Sabai_Addon_System_IAdminRouter */

    public function systemGetAdminRoutes()
    {
        return array(
            '/settings/file' => array(
                'controller' => 'Settings',
                'title_callback' => true,
                'callback_path' => 'settings'
            ),
            '/sabai/file/upload' => array(
                'controller' => 'UploadFile',
            ),
        );
    }

    public function systemOnAccessAdminRoute(Sabai_Context $context, $path, $accessType, array &$route)
    {

    }

    public function systemGetAdminRouteTitle(Sabai_Context $context, $path, $title, $titleType, array $route)
    {
        switch ($path) {
            case 'settings':
                return __('File Settings', 'sabai');
        }
    }

    /* End implementation of Sabai_Addon_System_IAdminRouter */
}
