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


<style>
    @media (max-width: 768px) {
        #magazine{
            width: 500px;
            height: 352px;
        }
    }
    @media (min-width: 768px) {
        #magazine{
            width: 698px;
            height: 492px;
        }
    }
    @media (min-width: 992px) {
        #magazine{
            width: 918px;
            height: 646px;
        }
    }
    @media (min-width: 1200px) {
        #magazine{
            width: 1118px;
            height: 790px;
        }
    }

    #magazine .turn-page{
        background-color:#ccc;
    }
</style>


    <div id="magazine" class="animate magazine">
        <?php foreach ($images as $attachment): ?>

            <div style="background-image:url(<?php echo wp_get_attachment_image_src($attachment->ID, 'magazine-slider')[0]; ?>); background-repeat: no-repeat; background-size: cover;"></div>

        <?php endforeach; // no reset query needed; get_posts() uses a new instance ?>
    </div>


<?php wp_reset_query(); ?>