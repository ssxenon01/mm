<?php
class Sabai_Addon_Directory_Controller_Map extends Sabai_Controller
{ 
    protected function _doExecute(Sabai_Context $context)
    {   
        // Init query
        $query = $this->Entity_Query('content')->propertyIs('post_status', Sabai_Addon_Content::POST_STATUS_PUBLISHED);   
        $category_id = null;
        // Addons specified?
        if (isset($context->addons)) {
            $_addons = $context->addons; // we need this to explode the actual value in the context
            $addons = is_array($_addons) ? $_addons : array_map('trim', explode(',', $_addons));
            if (count($addons) === 1) {
                $addon = $this->getAddon($addons[0]);
                $query->propertyIs('post_entity_bundle_name', $addon->getListingBundleName());
            } else {
                $bundles = array();
                foreach ($addons as $addon_name) {
                    $bundles[] = $this->getAddon($addon_name)->getListingBundleName();
                }
                $query->propertyIsIn('post_entity_bundle_name', $bundles);
            }
        } else {
            $addons = array('Directory');
            $query->propertyIs('post_entity_bundle_type', 'directory_listing');
        }
        // Any category specified?
        if (isset($context->category)) {
            if (count($addons) === 1) {
                $category_bundle = $this->getAddon($addons[0])->getCategoryBundleName();
            } else {
                $category_bundle = 'directory_listing_category';
            }
            if ($category = $this->getModel('Term', 'Taxonomy')->entityBundleName_is($category_bundle)->name_is($context->category)->fetchOne()) {
                $category_id = $category->id;
            }
        }
        $defaults = array(
            'address' => '',
            'sort' => 'newest', // newest, rating, reviews, distance
            'distance' => 0,
            'num' => 20,
            'zoom' => 15,
            'width' => null,
            'height' => 400,
            'style' => '',
            'is_mile' => false,
            'featured_only' => false,
            'scrollwheel' => false,
            'marker_clusters' => true,
        );
        $attr = array_intersect_key($context->getAttributes(), $defaults) + $defaults;
        $entities = $this->Directory_ListingsQuery($query, $attr['address'], array(), $category_id, $attr['sort'], $attr['distance'], $attr['is_mile'], $attr['featured_only'])
            ->fetch($attr['num'], 0);
        $attr['entities'] = $this->Entity_Render('content', $entities, null, 'summary');
        $context->addTemplate('directory_map')->setAttributes($attr);
    }
}