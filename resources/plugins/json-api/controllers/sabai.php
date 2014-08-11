<?php
class JSON_API_Sabai_Controller {

    private  function posts_result($posts) {

        global $wp_query;
        return array(
            'count' => count($posts),
            'count_total' => (int) $wp_query->found_posts,
            'pages' => $wp_query->max_num_pages,
            'posts' => $posts
        );
    }
    public function all(){
        global $json_api;
        $url = parse_url($_SERVER['REQUEST_URI']);
        $defaults = array(
            'ignore_sticky_posts' => true,
            'post_type' => 'wpbdp_listing'
        );
        $query = wp_parse_args($url['query']);
        unset($query['json']);
        unset($query['post_status']);
        $query = array_merge($defaults, $query);
        $posts = $json_api->introspector->get_posts($query);
        $result = $this->posts_result($posts);
        $result['query'] = $query;
        return $result;
    }

    public function hello(){
        $asd = Sabai_Web::create(Sabai_Platform_WordPress::getInstance(SABAI_WORDPRESS_PLUGIN));
    }

}

?>