<?php
class Sabai_Addon_File_Helper_ThumbnailLink extends Sabai_Helper
{
    public function help(Sabai $application, Sabai_Addon_Entity_Entity $entity, $file, array $options = array())
    {
        if (!is_array($file)) return '';
        
        $options += array(
            'class' => '',
            'rel' => !empty($options['link_entity']) ? '' : 'prettyPhoto',
            'link_image_size' => null,
            'link_entity' => false,
            'title' => null,
        );
        return sprintf(
            '<a href="%s" title="%s" class="sabai-file sabai-file-image sabai-file-type-%s %s" rel="%s"><img src="%s" alt="" /></a>',
            $options['link_entity'] ? $application->Entity_Url($entity) : $application->File_Url($entity, $file, $options['link_image_size']),
            Sabai::h($options['title'] ? $options['title'] : $file['title']),
            Sabai::h($file['extension']),
            $options['class'] ? Sabai::h($options['class']) : '',
            Sabai::h($options['rel']),
            $application->File_ThumbnailUrl($file['name'])
        );
    }
}