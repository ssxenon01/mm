<?php
class Sabai_Addon_Content_GuestAuthorFieldWidget extends Sabai_Addon_Entity_GuestAuthorFieldWidget
{
    public function __construct(Sabai_Addon_Content $addon)
    {
        parent::__construct($addon, 'content_guest_author');
    }
}