<?php
class Sabai_Addon_Content_ChildEntityCountFieldType extends Sabai_Addon_Entity_ChildEntityCountFieldType
{
    public function __construct(Sabai_Addon_Content $addon)
    {
        parent::__construct($addon, 'content');
    }
}