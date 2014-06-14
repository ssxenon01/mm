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
        <?php get_template_part('city-pulse-sliders');?>
        <div class="clearfix"></div>
        <div id="city-pulse">
            <?php while ($bunyad_loop->have_posts()): $bunyad_loop->the_post(); ?>
                <article <?php post_class(); ?> itemscope itemtype="http://schema.org/Article">
                    <div class="col-md-6 pulse-box">
                        <a href="<?php the_permalink() ?>" itemprop="url">
                            <div>
                                <div class="thumbnail-image">
                                    <?php the_post_thumbnail('slider-small', array('title' => strip_tags(get_the_title()),'class'=>'photo', 'itemprop' => 'image')); ?>
                                    <div class="photo-icon"></div>
                                    <div class="datebg"></div>
                                    <div class="published">
                                        <div class="text"><?php echo get_the_date('M'); ?></div>
                                        <div class="count"><?php echo get_the_date('d'); ?></div>
                                    </div>
                                </div>
                                <div class="pbox-body">
                                    <div class="box-title clearfix">
                                       <h3 itemprop="name"><?php the_title();?> </h3> <span class="photo-count">(<?php
                                            $attachments = get_children( array( 'post_parent' => $post->ID ) );
                                            echo(count($attachments)) + 1;
                                            ?> photos)</span>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>

                </article>
            <?php endwhile; ?>
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
