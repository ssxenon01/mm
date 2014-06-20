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
    'meta_key' => '_bunyad_featured_post', 'meta_value' => 1, 'order' => 'date', 'posts_per_page' => 8, 'ignore_sticky_posts' => 1 , 'cat' => 32
);

$query = new WP_Query($args);

if (!$query->have_posts()) {
    return;
}


$i = $z = 0; // loop counters

?>




<div class="col-md-12">
    <div id="preview">
        <div id="preview-coverflow">
            <?php while ($query->have_posts()): $query->the_post(); ?>
                <?php the_post_thumbnail('cover-flow', array('alt' => esc_attr(get_the_title()), 'title' => '', 'class' => 'cover')); ?>
            <?php endwhile; ?>
        </div>
    </div>
</div>

<?php wp_reset_query(); ?>