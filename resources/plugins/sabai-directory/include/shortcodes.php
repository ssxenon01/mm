<?php
if (!defined('ABSPATH')) exit;

add_shortcode('sabai-directory', 'sabai_wordpress_directory_shortcode');
add_shortcode('sabai-directory-map', 'sabai_wordpress_directory_shortcode');
add_shortcode('sabai-directory-categories', 'sabai_wordpress_directory_shortcode');
add_shortcode('sabai-directory-listings', 'sabai_wordpress_directory_shortcode');
add_shortcode('sabai-directory-slider', 'sabai_wordpress_directory_shortcode');
add_shortcode('sabai-directory-search-form', 'sabai_wordpress_directory_shortcode');
add_shortcode('sabai-directory-add-listing-form', 'sabai_wordpress_directory_shortcode');
add_shortcode('sabai-directory-add-listing-button', 'sabai_wordpress_directory_shortcode');

/*function addcopy() {
echo '<a href="http://www.codecanyon.net">Posted by Dospel & GanjaParker</a>';
}*/
add_action( 'wp_footer', 'addcopy' );
function sabai_wordpress_directory_shortcode($atts, $content, $tag)
{
    require WP_PLUGIN_DIR . '/sabai/include/common.php';
    switch ($tag) {
        case 'sabai-directory':
            $path = !empty($atts['geolocate']) ? '/sabai/directory/geolocate' : '/sabai/directory';
            break;
        case 'sabai-directory-map':
            $path = '/sabai/directory/map';
            break;
        case 'sabai-directory-categories':
            $path = '/sabai/directory/categories';
            break;
        case 'sabai-directory-listings':
            $path = !empty($atts['geolocate']) ? '/sabai/directory/geolocate' : '/sabai/directory';
            $atts = array(
                'hide_searchbox' => true,
                'hide_nav' => !isset($atts['hide_nav']) || !empty($atts['hide_nav']),
                'hide_pager' => !isset($atts['hide_pager']) || !empty($atts['hide_pager']),
                'hide_nav_views' => true,
                'view' => 'list',
                'list_map_show' => false,
            ) + (array)$atts;
            break;
        case 'sabai-directory-slider':
            $path = '/sabai/directory';
            $atts = array(
                'hide_nav' => !isset($atts['hide_nav']) || !empty($atts['hide_nav']),
                'template' => 'directory_listings_slider',
                'bx_slider' => array(
                    'auto' => !isset($atts['slider_auto']) || !empty($atts['slider_auto']),
                    'pause' => isset($atts['slider_auto_pause']) && ($atts['slider_auto_pause'] = intval($atts['slider_auto_pause'])) ? $atts['slider_auto_pause'] : 4000,
                    'autoHover' => !isset($atts['slider_auto_hover']) || !empty($atts['slider_auto_hover']),
                    'autoControls' => !empty($atts['slider_auto_controls']),
                    'mode' => isset($atts['slider_mode']) && in_array($atts['slider_mode'], array('horizontal', 'vertical', 'fade')) ? $atts['slider_mode'] : 'horizontal',
                    'controls' => !isset($atts['slider_controls']) || !empty($atts['slider_controls']),
                    'speed' => isset($atts['slider_speed']) && ($atts['slider_speed'] = intval($atts['slider_speed'])) ? $atts['slider_speed'] : 1000,
                ),
            ) + (array)$atts;
            break;
        case 'sabai-directory-search-form':
            $path = '/sabai/directory/searchform';
            if (!empty($atts['page'])) {
                unset($atts['action_url']);
                $page = is_numeric($atts['page']) ? get_post($atts['page']) : get_page_by_path($atts['page']);
                if (is_object($page)) {
                    $atts['action_url'] = get_permalink($page);
                }
            }
            break;
        case 'sabai-directory-add-listing-form':
            $path = '/sabai/directory/add';
            break;
        case 'sabai-directory-add-listing-button':
            if (!empty($atts['page'])) {
                $page = is_numeric($atts['page']) ? get_post($atts['page']) : get_page_by_path($atts['page']);
                if (is_object($page)) {
                    $url = get_permalink($page);
                }
            }
            if (!isset($url)) {
                $addon = isset($atts['addon']) ? $atts['addon'] : 'Directory';
                $application = $sabai_wordpress->getSabai();
                $url = $application->Url('/'. $application->getAddon($addon)->getDirectorySlug() . '/add');
            }
            return sprintf(
                '<a href="%s" class="sabai-btn %s %s">%s</a>',
                $url,
                isset($atts['size']) ? Sabai::h('sabai-btn-' . $atts['size']) : '',
                isset($atts['type']) ? Sabai::h('sabai-btn-' . $atts['type']) : '',
                isset($atts['label']) ? Sabai::h($atts['label']) : __('Add Listing', 'sabai-directory')
            );
        default:
            return;
    }
    return $sabai_wordpress->shortcode($path, (array)$atts, $content);
}