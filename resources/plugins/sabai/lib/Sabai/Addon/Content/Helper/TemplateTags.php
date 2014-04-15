<?php
class Sabai_Addon_Content_Helper_TemplateTags extends Sabai_Helper
{
    public function help(Sabai $application, Sabai_Addon_Content_Entity $entity, $prefix = '')
    {
        $author = $application->Content_Author($entity);
        return array(
            '{' . $prefix . 'id}' => $entity->getId(),
            '{' . $prefix . 'title}' => $entity->getTitle(),
            '{' . $prefix . 'author_name}' => $author->name,
            '{' . $prefix . 'author_email}' => $author->email,
            '{' . $prefix . 'url}' => $application->Entity_Url($entity, '', array(), '', '&'),
            '{' . $prefix . 'date}' => $application->Date($entity->getTimestamp()),
            '{' . $prefix . 'summary}' => $entity->getSummary(100),
            '{' . $prefix . 'type}' => $application->Entity_BundleLabel($application->Entity_Bundle($entity), true),
        );
    }
}