<?php

/**
 * Alternate "loop" to display posts in blog style.
 */

global $bunyad_loop;

if (!is_object($bunyad_loop)) {
    $bunyad_loop = $wp_query;
}

if ($bunyad_loop->have_posts()):

?>



<div class="city-pulse" >
    <div class="row">
        <?php get_template_part('health-sliders');?>
        <div class="clearfix"></div>
        <div>
            <div class="col-md-12">
                <div class="health-loop">
                    <?php
                    while ($bunyad_loop->have_posts()) : $bunyad_loop->the_post(); global $post;
                        ?>

                        <div class="col-md-6">
                            <div class="trans-box">
                                <div class="thumb pull-left">
                                    <?php the_post_thumbnail('recent-posts', array('title' => strip_tags(get_the_title()))); ?>
                                    <a class="btn btn-default" href="<?php the_permalink() ?>">Дэлгэрэнгүй</a>
                                </div>
                                <h1><?php the_title();?></h1>
                                <div class="description"><?php echo preg_replace("/<a.*<\/a>/", "\n", wp_trim_excerpt()); ?></div>
                            </div>
                        </div>

                    <?php endwhile; ?>

                    <div class="clearfix"></div>
                </div>
            </div>


        </div>
    </div>
</div>
<?php else: ?>

    <article id="post-0" class="page no-results not-found">
        <div class="post-content">
            <h1><?php _e( 'Nothing Found!', 'bunyad' ); ?></h1>
            <p><?php _e('Apologies, but no results were found for the requested archive. Perhaps searching will help find a related post.', 'bunyad'); ?></p>
        </div><!-- .entry-content -->
    </article><!-- #post-0 -->

<?php endif; ?>
