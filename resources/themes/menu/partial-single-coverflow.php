<?php

/**
 * Partial Template - Display the gallery slider for gallery post formats
 */

$image_ids = Bunyad::posts()->get_first_gallery_ids();

if (!$image_ids) {
    return;
}

$images = get_posts(array(
    'post_type' => 'attachment',
    'post_status' => 'inherit',
    'post__in' => $image_ids,
    'orderby' => 'post__in',
    'posts_per_page' => -1
));

?>




<div class="col-md-12">
    <div id="preview">
        <div id="preview-coverflow">

            <?php foreach ($images as $attachment): ?>

                <?php echo wp_get_attachment_image($attachment->ID, 'cover-flow',false, array('alt' => esc_attr(get_the_title()), 'title' => '', 'class' => 'cover','name'=>get_the_title())); ?>

            <?php endforeach; // no reset query needed; get_posts() uses a new instance ?>

        </div>
        <h1></h1>
    </div>
</div>

<?php wp_reset_query(); ?>