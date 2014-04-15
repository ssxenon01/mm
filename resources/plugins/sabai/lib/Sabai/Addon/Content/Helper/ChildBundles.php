<?php
class Sabai_Addon_Content_Helper_ChildBundles extends Sabai_Helper
{
    public function help(Sabai $application, Sabai_Addon_Entity_Model_Bundle $bundle, $sort = 'label', $order = 'ASC')
    {
        return $application->getModel('Bundle', 'Entity')
            ->entitytypeName_is('content')
            ->name_startsWith($bundle->name . '_')
            ->fetch(0, 0, $sort, $order);
    }
}