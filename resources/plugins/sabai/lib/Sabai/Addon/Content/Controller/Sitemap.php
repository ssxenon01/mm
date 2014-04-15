<?php
class Sabai_Addon_Content_Controller_Sitemap extends Sabai_Controller
{
    protected $_cacheLifetime = 86400; // 1 day
    
    protected function _doExecute(Sabai_Context $context)
    {
        $cache_id = 'content_sitemap_' . $context->bundle->name;
        if (false === $urls = $this->getPlatform()->getCache($cache_id)) {
            $urls = array();
            $posts = $this->Entity_Query('content')
                ->propertyIs('post_entity_bundle_name', $context->bundle->name)
                ->propertyIs('post_status', Sabai_Addon_Content::POST_STATUS_PUBLISHED)
                ->fetch(); 
            foreach ($posts as $post) {
                $urls[] = array(
                    'loc' => $this->Entity_Url($post),
                    'lastmod' => !empty($post->content_activity[0]['active_at']) ? $post->content_activity[0]['active_at'] : $post->getTimestamp(),
                    'changefreq' => 'weekly',
                    'priority' => '0.8',
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