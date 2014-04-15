<?php
class Sabai_Addon_Content_ParentEntityFieldType extends Sabai_Addon_Entity_ParentEntityFieldType
{
    public function __construct(Sabai_Addon_Content $addon)
    {
        parent::__construct($addon, 'content');
    }
}