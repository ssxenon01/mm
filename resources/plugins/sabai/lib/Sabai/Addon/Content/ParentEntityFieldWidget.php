<?php
class Sabai_Addon_Content_ParentEntityFieldWidget extends Sabai_Addon_Entity_ParentEntityFieldWidget
{
    public function __construct(Sabai_Addon_Content $addon)
    {
        parent::__construct($addon, 'content', 'content_parent');
    }
}