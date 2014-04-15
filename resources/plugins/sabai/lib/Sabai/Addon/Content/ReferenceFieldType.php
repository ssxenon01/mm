<?php
class Sabai_Addon_Content_ReferenceFieldType extends Sabai_Addon_Entity_ReferenceFieldType
{
    public function __construct(Sabai_Addon_Content $addon)
    {
        parent::__construct($addon, 'content');
    }
}