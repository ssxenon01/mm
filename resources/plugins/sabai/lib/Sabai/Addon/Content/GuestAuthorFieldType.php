<?php
class Sabai_Addon_Content_GuestAuthorFieldType extends Sabai_Addon_Entity_GuestAuthorFieldType
{
    public function __construct(Sabai_Addon_Content $addon)
    {
        parent::__construct($addon, 'content', 'content_guest_author');
    }
}