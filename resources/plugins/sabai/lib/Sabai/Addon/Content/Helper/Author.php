<?php
class Sabai_Addon_Content_Helper_Author extends Sabai_Helper
{
    public function help(Sabai $application, Sabai_Addon_Content_Entity $entity)
    {
        $author = $entity->getAuthor();
        if ($author->isAnonymous() && !$author->email) {
            if ($guest_author_info = $entity->getFieldValue('content_guest_author')) {
                // Because anonymous identity object is shared, we need to clone it to give a specific identity
                $author = clone $author;
                $author->name = $guest_author_info[0]['name'];
                $author->email = $guest_author_info[0]['email'];
                $author->url = $guest_author_info[0]['url'];
                $entity->setProperty('author', $author); // set the new identtity object
            }
        }
        
        return $author;
    }
}