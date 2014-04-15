<?php
class Sabai_Addon_Taxonomy_Controller_Sitemap extends Sabai_Controller
{
    protected $_cacheLifetime = 259200; // 3 days
    
    protected function _doExecute(Sabai_Context $context)
    {
        $cache_id = 'taxonomy_sitemap_' . $context->taxonomy_bundle->name;
        if (false === $urls = $this->getPlatform()->getCache($cache_id)) {
            $urls = array();
            $terms = $this->Entity_Query('taxonomy')
                ->propertyIs('term_entity_bundle_name', $context->taxonomy_bundle->name)
                ->fetch(); 
            foreach ($terms as $term) {
                $urls[] = array(
                    'loc' => $this->Entity_Url($term),
                    'lastmod' => $term->getTimestamp(),
                    'changefreq' => 'weekly',
                    'priority' => '0.2',
                );
            }
            $this->getPlatform()->setCache($urls, $cache_id, $this->_cacheLifetime);
        }
        
        $context->setContentType('xml')
            ->addTemplate('system_sitemap')
            ->setAttributes(array(
                'urls' => $urls,
            ));
    }
}