<?php
class Sabai_Addon_Content_FeaturedEntityFieldType extends Sabai_Addon_Entity_FeaturedEntityFieldType
{
    public function __construct(Sabai_Addon_Content $addon)
    {
        parent::__construct($addon, 'content');
    }
}