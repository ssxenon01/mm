<?php
abstract class Sabai_Addon_System_Controller_Sitemap extends Sabai_Controller
{
    protected $_cacheLifetime = 86400, // 1 day
        $_entityChangeFreq = 'weekly', $_entityPriority = 0.8;
    
    abstract protected function _getCacheId(Sabai_Context $context);
    abstract protected function _getQuery(Sabai_Context $context);
    
    protected function _getEntityLastModified(Sabai_Addon_Entity_Entity $entity)
    {
        return $entity->getTimestamp();
    }
    
    protected function _doExecute(Sabai_Context $context)
    {
        $cache_id = $this->_getCacheId($context);
        if (false === $urls = $this->getPlatform()->getCache($cache_id)) {
            $urls = array();
            $query = $this->_getQuery($context);
            $count = $query->count();        
            for ($i = 0; $i < $count; $i = $i + 1000) {
                foreach ($query->fetch(1000, $i) as $entity) {
                    $urls[] = array(
                        'loc' => $this->Entity_Url($entity),
                        'lastmod' => $this->_getEntityLastModified($entity),
                        'changefreq' => $this->_entityChangeFreq,
                        'priority' => $this->_entityPriority,
                    );
                }
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