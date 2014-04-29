<?php
class Sabai_Addon_Taxonomy_Controller_ListHierarchicalTerms extends Sabai_Addon_Taxonomy_Controller_ListTerms
{        
    protected function _createQuery(Sabai_Context $context, $sort, Sabai_Addon_Entity_Model_Bundle $bundle = null)
    {
        return parent::_createQuery($context, $sort, $bundle)->propertyIs('term_parent', 0);
    }
}