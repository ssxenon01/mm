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

// use latest posts?
if (Bunyad::posts()->meta('featured_slider') == 'default-latest') {
	unset($args['meta_key'], $args['meta_value']);
}

$query = new WP_Query($args);

if (!$query->have_posts()) {
	return;
}


$i = $z = 0; // loop counters

?>

<div class="mymenu-swiper">
    <a class="arrow-left" href="#"></a>
    <a class="arrow-right" href="#"></a>
    <div class="swiper-container">
        <div class="swiper-wrapper">
            <?php while ($query->have_posts()): $query->the_post(); ?>
            <div class="swiper-slide">
                <?php $cat = current(get_the_category()); ?>
                <a href="<?php echo get_category_link($cat->term_id); ?>"><div class="type"><?php echo esc_html($cat->cat_name); ?></div></a>
                <?php the_post_thumbnail('main-slider', array('alt' => esc_attr(get_the_title()), 'title' => '')); ?>
                <a href="<?php the_permalink(); ?>"><div class="title"><?php echo esc_attr(get_the_title());?> <span class="published"><?php echo the_date();?></span></div></a>
            </div>

            <?php endwhile; ?>
        </div>
        <div class="pagination"></div>
    </div>
</div>
<?php wp_reset_query(); ?>


