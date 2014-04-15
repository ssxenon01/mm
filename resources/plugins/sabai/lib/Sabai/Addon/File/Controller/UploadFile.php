<?php
class Sabai_Addon_File_Controller_UploadFile extends Sabai_Controller
{
    protected function _doExecute(Sabai_Context $context)
    {
        if (!$context->getRequest()->isPostMethod()) {
            $context->error = 'Invalid request method';
            return;
        }
        
        if (!$form_build_id = $context->getRequest()->asStr('sabai_file_form_build_id', false)) {
            $context->error = 'Bad request';
            return;
        }

        if (!$token_value = $context->getRequest()->asStr('sabai_file_upload_token', false)) {
            $context->error = 'Bad request';
            return;
        }

        $token = $this->getModel('Token')->userId_is($this->getUser()->id)
            ->formBuildId_is($form_build_id)
            ->hash_is($token_value)
            ->fetchOne();

        if (!$token || $token->expires < time()) {
            $context->error = 'Forbidden';
            return;
        }

        $this->_uploadFile($token, $context);
        $context->addTemplate('file_uploadfile')
            ->setContainer(false); // workaround to disable the layout
    }

    private function _uploadFile(Sabai_Addon_File_Model_Token $token, Sabai_Context $context)
    {
        if ($token->settings['max_num_files'] && $token->file_count > $token->settings['max_num_files'] * 2) {
            $context->error = __('You have already uploaded enough files!', 'sabai');
            return;
        }
        
        $tmp_dir = $this->getAddon('File')->getTmpDir();
        try {
            $this->ValidateDirectory($tmp_dir, true);
        } catch (Sabai_IException $e) {
            $context->error = $e->getMessage();
            return;
        }
        
        if (!empty($_FILES['qqfile'])) {
            // Upload from IE
            if (!empty($_FILES['qqfile']['error'])) {
                $context->error = sprintf(__('Failed uploading file. Error code: %d', 'sabai'), $_FILES['qqfile']['error']);
                return;
            }
            $tmp_name = $tmp_dir . '/' . basename($_FILES['qqfile']['tmp_name']);
            if (!move_uploaded_file($_FILES['qqfile']['tmp_name'], $tmp_name)) {
                $context->error = __('Failed creating temporary file', 'sabai');
                return;
            }
            $size = $_FILES['qqfile']['size'];
        } else {         
            if (!$tmp_name = tempnam($tmp_dir, 'sabai_file_')) {
                $context->error = __('Failed creating temporary file', 'sabai');
                return;
            }
            if (!$tmp_file = fopen($tmp_name, 'w')) {
                $context->error = __('Failed opening temporary file with write permission', 'sabai');
                return;
            }
            if (!$input = fopen('php://input', 'r')) {
                $context->error = __('Failed reading php input stream', 'sabai');
                return;
            }
            $size = stream_copy_to_stream($input, $tmp_file);
            fclose($input);
            fclose($tmp_file);
        }

        try {
            $mime = $this->_getFileType($tmp_name, $token->settings);
        } catch (Sabai_IException $e) {
            $context->error = $e->getMessage();
            @unlink($tmp_name);
            return;
        }

        $file = array(
            'name' => $_GET['qqfile'],
            'type' => $mime,
            'size' => $size,
            'tmp_name' => $tmp_name
        );

        try {
            $uploader = $this->getAddon('File')->getUploader($token->settings);
            $context->files = array($this->getAddon()->saveFile($uploader->uploadFile($file, false), $token));
            // Increment uploaded file count for this token
            $token->file_count = $token->file_count + 1;
            $token->commit();
        } catch (Exception $e){
            $context->error = $e->getMessage();
        }
        @unlink($tmp_name);
    }
    
    private function _getFileType($file, array $uploadSettings)
    {
        if (!empty($uploadSettings['image_only'])) {
            if ($size = @getimagesize($file)) {
                return $size['mime'];
            }
        }
        if (function_exists('finfo_file')) {
            if (($finfo = @finfo_open(FILEINFO_MIME))
                && ($mime = finfo_file($finfo, $file))
            ) {
                return $mime;
            }
            @finfo_close($finfo);
        }
        if (!function_exists('mime_content_type')) {
            throw new Sabai_RuntimeException('Could not find finfo_file or mime_content_type function');
        }
        
        return mime_content_type($file);
    }
}