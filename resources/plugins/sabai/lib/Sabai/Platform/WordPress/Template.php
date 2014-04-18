<?php
class Sabai_Platform_WordPress_Template
{
    private $_title, $_pageSummary, $_pageUrl, $_pageBreadcrumbs, $_css = '', $_js = '', $_htmlHead = '', $_htmlHeadTitle, $_siteName = '',
        $_isFilteringSabaiPage;

    // The following is required which is to be set by the add_filter method
    public $wp_filter_id;

    public function set($name, $value)
    {
        $property = '_' . $name;
        $this->$property = $value;
        return $this;
    }

    public function render()
    {
        add_filter('wp_title', array($this, 'onWpTitleFilter'), 1, 3);
        add_filter('the_title', array($this, 'onTheTitleFilter'), 99, 2);
        add_action('wp_head', array($this, 'onWpHeadAction'), 99);
        add_filter('page_link', array($this, 'onPageLinkFilter'), 99, 3);  
        add_filter('the_permalink', array($this, 'onThePermaLinkFilter'), 99, 3);        
        add_filter('wpseo_title', array($this, 'onWpSeoTitleFilter'), 99);
        add_filter('wpseo_metadesc', array($this, 'onWpSeoMetaDescFilter'), 99);
        add_filter('wpseo_breadcrumb_links', array($this, 'onWpSeoBreadcrumbLinksFilter'), 99, 1);
        add_filter('wpseo_canonical', array($this, 'onCanonicalFilter'), 99);
        add_filter('aioseop_title_page', array($this, 'onAioSeoPTitlePageFilter'), 99);
        add_filter('aioseop_description', array($this, 'onAioSeoPDescFilter'), 99);
        add_filter('aioseop_canonical_url', array($this, 'onCanonicalFilter'), 99);
    }

    public function onWpHeadAction()
    {
        echo implode(PHP_EOL, array($this->_css, $this->_js, $this->_htmlHead));
    }

    public function onWpTitleFilter($title, $sep, $seplocation)
    {
        if (!isset($this->_htmlHeadTitle) || false === $this->_htmlHeadTitle) {
            return $title;
        }

        $sep = trim($sep);
        
        // Some themes call wp_title() without any arguments passed when WP Seo is installed.
        if (defined('WPSEO_VERSION'))  {
            // This is how WP Seo initializes $sep and $seplocation:
            if ('' === $seplocation) {
                if (!strlen($sep)) {      
                    $sep = '-';
                    $seplocation = 'right';
                } else {
                    $seplocation = is_rtl() ? 'left' : 'right';
                }
            }
        }
        return $seplocation === 'right' ? $this->_htmlHeadTitle . ' ' . $sep . ' ' : ' ' . $sep . ' ' . $this->_htmlHeadTitle;
    }

    public function onTheTitleFilter($title, $pageId = null)
    {
        return isset($this->_title) && false !== $this->_title && $this->_isFilteringSabaiPage($pageId) ? $this->_title : $title;
    }
    
    public function onPageLinkFilter($link, $pageId, $sample)
    {
        // The following flag is used to determine if the_permalink filter is being applied to a Sabai page 
        $this->_isFilteringSabaiPage = $this->_isFilteringSabaiPage($pageId);
        
        return $link;
    }
    
    public function onThePermaLinkFilter($link)
    {
        // $this->_isFilteringSabaiPage may be null if not filtering the permalink of a page
        if (!$this->_isFilteringSabaiPage || !isset($this->_pageUrl)) {
            return $link;
        }
        $this->_isFilteringSabaiPage = null;
        return $this->_pageUrl;
    }
    
    private function _isFilteringSabaiPage($pageId)
    {
        if (empty($pageId)
            || !isset($GLOBALS['post'])
            || $GLOBALS['post']->ID != $pageId // Not filtering current page title?
            || (defined('SABAI_WORDPRESS_FIX_OLD_MENU') && !in_the_loop())
        ) {
            return false;
        }
        
        $page_slugs = get_option('sabai_sabai_page_slugs');
        if (!in_array($pageId, $page_slugs[2])) {
            return false;
        }
        return true;
    }
        
    public function onWpSeoTitleFilter($title)
    {
        if (!isset($this->_htmlHeadTitle) || false === $this->_htmlHeadTitle) {
            return $title;
        }
        global $wp_query;
        $page = $wp_query->get_queried_object();
        $page->post_title = $this->_htmlHeadTitle;
        $options = get_option('wpseo_titles');
        return wpseo_replace_vars($options['title-page'], $page);
    }
    
    public function onWpSeoBreadcrumbLinksFilter($links)
    {
        $links = array($links[0]);
        foreach ($this->_pageBreadcrumbs as $breadcrumb) {
            $links[] = array('url' => $breadcrumb['url'], 'text' => $breadcrumb['title']);
        }
        return $links;
    }
        
    public function onWpSeoMetaDescFilter($desc)
    {
        return isset($this->_pageSummary) && strlen($this->_pageSummary)
            ? $this->_pageSummary // for taxonomy pages
            : $desc;
    }
    
    public function onCanonicalFilter($url)
    {
        return isset($this->_pageUrl) ? (string)$this->_pageUrl : $url;
    }
    
    public function onAioSeoPTitlePageFilter($title)
    {
        return isset($this->_htmlHeadTitle) && false !== $this->_htmlHeadTitle ? $this->_htmlHeadTitle : $title;
    }
    
    public function onAioSeoPDescFilter($desc)
    {
        return isset($this->_pageSummary) && strlen($this->_pageSummary)
            ? $this->_pageSummary // for taxonomy pages
            : $desc;
    }
}
