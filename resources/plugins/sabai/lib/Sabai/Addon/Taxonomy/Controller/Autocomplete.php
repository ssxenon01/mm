<?php
class Sabai_Addon_Taxonomy_Controller_Autocomplete extends Sabai_Controller
{
    protected function _doExecute(Sabai_Context $context)
    {
        if (!$context->getRequest()->isAjax()) {
            $context->setBadRequestError();
            return;
        }

        $term = trim($context->getRequest()->asStr('term'));
        if (strlen($term) <= 1) {
            $context->setBadRequestError();
            return;
        }

        $limit = 10;
        $offset = ($context->getRequest()->asInt(Sabai::$p, 1) - 1) * $limit;
        $context->entities = $this->Entity_TypeImpl('taxonomy')
            ->entityTypeSearchEntitiesByBundle($term, $context->taxonomy_bundle, $limit, $offset);
        $context->addTemplate('taxonomy_autocomplete');
    }
}