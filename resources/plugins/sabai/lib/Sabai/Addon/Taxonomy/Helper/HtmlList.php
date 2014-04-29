<?php
class Sabai_Addon_Taxonomy_Helper_HtmlList extends Sabai_Helper
{
    public function help(Sabai $application, $bundleName, array $options = array())
    {
        $bundle = $application->Entity_Bundle($bundleName);
        if (!$bundle) {
            return '';
        }
        $options += array(
            'parent' => 0,
            'format' => null,
            'link' => true,
            'depth' => 0,
            'content_bundle' => null,
            'content_empty_skip' => false,
            'class' => '',
            'dropdown' => false,
        );
        if (!empty($options['dropdown'])) {
            $options['class'] .= ' sabai-dropdown-menu';
        }
        $terms = $term_ids = $html = array();
        $query = $application->Entity_Query('taxonomy')->propertyIs('term_entity_bundle_name', $bundle->name)->sortByProperty('term_title');
        if ($options['depth'] === 1) {
            // may limit to direct child terms only
            $query->propertyIs('term_parent', $options['parent']);
        }
        foreach ($query->fetch(0, 0, false) as $term) {
            $terms[$term->getParentId()][] = $term;
            $term_ids[] = $term->getId();
        }
        if (empty($terms[$options['parent']])) {
            return '';
        }
        if (!empty($term_ids) && !empty($options['content_bundle'])) {
            $content_count = $this->_getContentCount($application, $bundleName, $options['parent'], $term_ids, $options['depth']);
        } else {
            $content_count = array();
        }
        
        $this->_makeTermHtml($application, $terms, $content_count, $html, $options, $options['parent']);
        
        return implode(PHP_EOL, $html);
    }
    
    private function _getContentCount($application, $bundleName, $parentId, $termIds, $depth)
    {
        if ($depth === 1) {
            $cache_id = 'taxonomy_term_content_count_' . $bundleName . $parentId;
            if (false === $content_count = $application->getPlatform()->getCache($cache_id)) {
                $content_count = $application->getModel(null, 'Taxonomy')
                    ->getGateway('Term')
                    ->getContentCount($termIds);
                $application->getPlatform()->setCache($content_count, $cache_id, 3600); // cache 1 hour
            }
        } else {
            $content_count = $application->getModel(null, 'Taxonomy')
                ->getGateway('Term')
                ->getContentCount($termIds);
        }
        return $content_count;
    }

    private function _makeTermHtml(Sabai $application, array $terms, array $counts, array &$html, array $options, $parentId = 0, $depth = 1)
    {
        $html[] = isset($options['class']) ? '<ul class="'. $options['class'] .'">' : '<ul>';
        foreach ($terms[$parentId] as $term) {
            $formatted_term = $options['link'] ? $application->Entity_Permalink($term) : Sabai::h($term->getTitle());
            if (!isset($counts[$term->getId()][$options['content_bundle']])) {
                if ($options['content_empty_skip']) {
                    continue;
                }
                $content_count = 0;
            } else {
                $content_count = $counts[$term->getId()][$options['content_bundle']];
            }
            if (isset($options['format'])) {
                if (is_array($options['format'])) {
                    // Check if format for the current term is set
                    if (isset($options['format'][$term->getId()])) {
                        // Use the format specifically set for the current term
                        $formatted_term = sprintf($options['format'][$term->getId()], $formatted_term, $content_count);
                    } else {
                        $formatted_term = sprintf($options['format'][0], $formatted_term, $content_count);
                    }
                } else {
                    $formatted_term = sprintf($options['format'], $formatted_term, $content_count);
                }
            } else {
                $formatted_term = sprintf(__('%s (%d)', 'sabai'), $formatted_term, $content_count);
            }
            // Add sub-lists if any child terms
            if (!empty($terms[$term->getId()])) {
                $next_depth = $depth + 1;
                if (empty($options['depth']) || $next_depth <= $options['depth']) {
                    if (!empty($options['dropdown'])) {
                        $html[] = '<li data-sabai-taxonomy-term="'. $term->getId() .'" class="sabai-dropdown-submenu">' . $formatted_term . '</li>';
                    } else {
                        $html[] = '<li data-sabai-taxonomy-term="'. $term->getId() .'">' . $formatted_term . '</li>';
                    }
                    $this->_makeTermHtml($application, $terms, $counts, $html, $options, $term->getId(), $next_depth);
                } else {
                    $html[] = '<li data-sabai-taxonomy-term="'. $term->getId() .'">' . $formatted_term . '</li>';
                }
            } else {
                $html[] = '<li data-sabai-taxonomy-term="'. $term->getId() .'">' . $formatted_term . '</li>';
            }
        }
        $html[] = '</ul>';
    }
}