<?php

/**
 * Partial Template - Display the featured slider and the blocks
 */

$data_vars = array(
	'data-animation-speed="'. intval(Bunyad::options()->slider_animation_speed) . '"',
	'data-animation="' . esc_attr(Bunyad::options()->slider_animation) . '"',
	'data-slide-delay="' . esc_attr(Bunyad::options()->slider_slide_delay) . '"',
);

$data_vars = implode(' ', $data_vars);

// get latest featured posts
$args = array(
	'meta_key' => '_bunyad_featured_post', 'meta_value' => 1, 'order' => 'date', 'posts_per_page' => 8, 'ignore_sticky_posts' => 1
);

/*
 * Posts grid generated from a category or a tag?
 */
$limit_cat = Bunyad::options()->featured_right_cat;
$limit_tag = Bunyad::options()->featured_right_tag;

if (!empty($limit_cat)) {
	
	$args['posts_per_page'] = 5;
	$grid_query = new WP_Query(array('cat' => $limit_cat, 'posts_per_page' => 3));
}
else if (!empty($limit_tag)) {
	
	$args['posts_per_page'] = 5;
	$grid_query = new WP_Query(array('tag' => $limit_tag, 'posts_per_page' => 3));
}

/*
 * Category slider?
 */
if (is_category()) {
	$cat = get_query_var('cat');
	$meta = Bunyad::options()->get('cat_meta_' . $cat);
	
	// slider not enabled? quit!
	if (empty($meta['slider'])) {
		return;
	}
		
	$args['cat'] = $cat;
	
	// latest posts?
	if ($meta['slider'] == 'latest') {
		unset($args['meta_key'], $args['meta_value']);
	}
}

/*
 * Main slider posts query
 */

// use latest posts?
if (Bunyad::posts()->meta('featured_slider') == 'default-latest') {
	unset($args['meta_key'], $args['meta_value']);
}

$query = new WP_Query($args);

if (!$query->have_posts()) {
	return;
}

// Use rest of the 3 posts for grid if not post grid is not using 
// any category or tag. Create reference for to main query.
if (!$grid_query && $query->found_posts > 5) {
	$grid_query = &$query;
}


$i = $z = 0; // loop counters

?>

<div class="mymenu-swiper">
    <a class="arrow-left" href="#"></a>
    <a class="arrow-right" href="#"></a>
    <div class="swiper-container">
        <div class="swiper-wrapper">
            <div class="swiper-slide">
                <div class="type">Мэдээ, мэдээлэл</div>
                <img src="/mymenu/resources/themes/menu/images/sample/news/coffee.jpg" >
                <div class="title">Дэлхийн хамгийн үнэтэй кофе <span class="published">22 цаг 44 минут</span></div>
            </div>
            <div class="swiper-slide">
                <div class="type">Нийтлэл</div>
                <img src="/mymenu/resources/themes/menu/images/sample/news/cocktail.jpg">
                <div class="title">Коктейль яагаад өндөр үнэтэй байдаг вэ?<span class="published">22 цаг 44 минут</span></div>
            </div>
            <div class="swiper-slide">
                <div class="type">Онцлох ресторан</div>
                <img src="/mymenu/resources/themes/menu/images/sample/news/restaurant.jpg">
                <div class="title">Монголын Saint Germain de Pres <span class="published">22 цаг 44 минут</span></div>
            </div>
        </div>
        <div class="pagination"></div>
    </div>
</div>




