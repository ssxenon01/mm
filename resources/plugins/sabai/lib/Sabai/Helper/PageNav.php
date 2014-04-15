<?php
class Sabai_Helper_PageNav extends Sabai_Helper
{
    public static $offset = 2;

    public function help(Sabai $application, $update, SabaiFramework_Paginator $pages, $linkUrl, array $options = array(), array $attributes = array(), $offset = null)
    {
        if (1 >= $page_count = $pages->count()) return '';

        $current_page = $pages->getCurrentPage();
        $current_html = sprintf('<li class="sabai-active"><span>%d</span></li>', $current_page);
        $html = array();
        if (!isset($offset)) $offset = self::$offset;
        $link_url = $application->Url($linkUrl); // convert to SabaiFramework_Application_Url
        $ajax_url = isset($options['url']) ? $application->Url($options['url']) : clone $link_url; // convert to SabaiFramework_Application_Url
        $min = max(1, $current_page - $offset);
        $max = $current_page + $offset;
        if ($max > $page_count) $max = $page_count;
        if ($current_page != 1) {
            $html[] = sprintf('<li>%s</li>', $this->_getPageLink($application, __('Previous', 'sabai'), $current_page - 1, $update, $link_url, $ajax_url, $options, $attributes));
        } else {
            $html[] = '<li class="sabai-disabled"><span>' . __('Previous', 'sabai') . '</span></li>';
        }
        if ($min > 1) {
            $html[] = sprintf('<li>%s</li>', $this->_getPageLink($application, 1, 1, $update, $link_url, $ajax_url, $options, $attributes));
            if ($min > 2) $html[] = '<li class="sabai-disabled"><span>...</span></li>';
        }
        for ($i = $min; $i <= $max; $i++) {
            $html[] = ($i == $current_page) ? $current_html : sprintf('<li>%s</li>', $this->_getPageLink($application, $i, $i, $update, $link_url, $ajax_url, $options, $attributes));
        }
        if ($max < $page_count) {
            if ($page_count - $max > 1) $html[] = '<li class="sabai-disabled"><span>...</span></li>';
            $html[] = sprintf('<li>%s</li>', $this->_getPageLink($application, $page_count, $page_count, $update, $link_url, $ajax_url, $options, $attributes));
        }
        if ($current_page != $page_count) {
            $html[] = sprintf('<li>%s</li>', $this->_getPageLink($application, __('Next', 'sabai'), $current_page + 1, $update, $link_url, $ajax_url, $options, $attributes));
        } else {
            $html[] = '<li class="sabai-disabled"><span>' . __('Next', 'sabai') . '</span></li>';
        }

        return sprintf('<ul>%s</ul>', implode('', $html));
    }

    private function _getPageLink(Sabai $application, $text, $page, $update, $linkUrl, $ajaxUrl, array $options = array(), array $attributes = array())
    {
        $linkUrl['params'] = array(Sabai::$p => $page) + $linkUrl['params'];
        $ajaxUrl['params'] = array(Sabai::$p => $page) + $ajaxUrl['params'];
        $options['url'] = $ajaxUrl;
        if (!isset($options['scroll'])) {
            $options['scroll'] = $update;
        }

        return $application->LinkToRemote($text, $update, $linkUrl, $options, $attributes);
    }
}