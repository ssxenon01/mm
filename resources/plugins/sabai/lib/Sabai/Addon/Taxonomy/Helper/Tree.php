<?php
class Sabai_Addon_Taxonomy_Helper_Tree extends Sabai_Helper
{
    public function help(Sabai $application, $bundleName, $prefix = '--', array $tree = array(), $maxDepth = 0, $initialDepth = 1)
    {
        $terms = array();
        if (is_object($bundleName)) {
            $bundleName = $bundleName->name;
        }
        foreach ($application->getAddon('Taxonomy')->getModel('Term')->entityBundleName_is($bundleName)->fetch(0, 0, 'title', 'ASC') as $term) {
            $terms[$term->parent][] = $term;
        }
        $this->_makeTermTree($terms, $tree, $prefix, $maxDepth, $initialDepth);
        
        return $tree; 
    }
    
    private function _makeTermTree($terms, &$tree, $prefix, $maxDepth, $depth, $parentId = 0)
    {
        if (!isset($terms[$parentId])) return;

        $_prefix = str_repeat($prefix, $depth - 1);
        foreach ($terms[$parentId] as $term) {
            $tree[$term->id] = $_prefix . $term->title;
            $next_depth = $depth + 1;
            if (!$maxDepth || $next_depth <= $maxDepth) {
                $this->_makeTermTree($terms, $tree, $prefix, $maxDepth, $next_depth, $term->id);
            }
        }
    }
}
