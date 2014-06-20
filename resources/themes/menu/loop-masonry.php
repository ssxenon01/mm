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
        <div>
            <div class="masonry-loop">

                <?php
                $counter=0;
                    while ($bunyad_loop->have_posts()): $bunyad_loop->the_post(); ?>

                        <?php if (Bunyad::posts()->meta('featured_video')): // featured video available? ?>

                            <div class="col-md-6 m-item" >
                                <div class="video-box">
                                    <iframe src="<?php echo Bunyad::posts()->meta('featured_video');?>" width="100%" height="200" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>
                                </div>
                            </div>
                        <?php else: // normal featured image ?>


                            <?php
                            $counter++;
                            if($counter%5==1):?>
                                <div class="col-md-3 m-item" >
                                    <a href="<?php the_permalink() ?>"><div class="red-box">
                                            <div class="thumb"><?php the_post_thumbnail('thumbnail', array('title' => strip_tags(get_the_title()),'class'=>'photo', 'itemprop' => 'image')); ?></div>
                                            <div class="title"><?php the_title();?></div>
                                            <div class="description" ><?php echo preg_replace("/<a.*<\/a>/", "\n", wp_trim_excerpt()); ?></div>
                                        </div></a>
                                </div>
                            <?php elseif($counter%5==2): ?>
                                <div class="col-md-3 m-item" >
                                    <a href="<?php the_permalink() ?>"><div class="thumb-box">
                                            <div class="thumb"><?php the_post_thumbnail('thumbnail', array('title' => strip_tags(get_the_title()),'class'=>'photo', 'itemprop' => 'image')); ?></div>
                                            <div class="ribbon"><div class="ribbon-stitches-top"></div><strong class="ribbon-content"><h1><?php the_title();?></h1></strong><div class="ribbon-stitches-bottom"></div></div>
                                        </div></a>
                                </div>
                            <?php elseif($counter%5==3):?>
                                <div class="col-md-6 m-item" >
                                    <a href="<?php the_permalink() ?>"><div class="yellow-box">
                                            <div class="title"><?php the_title();?></div>
                                            <div class="thumb pull-left"><?php the_post_thumbnail('thumbnail', array('title' => strip_tags(get_the_title()),'class'=>'photo', 'itemprop' => 'image')); ?></div>
                                            <div class="description" ><?php echo preg_replace("/<a.*<\/a>/", "\n", wp_trim_excerpt()); ?></div>
                                            <div class="clearfix"></div>
                                        </div></a>
                                </div>
                            <?php elseif($counter%5==4):?>
                                <div class="col-md-6 m-item" >
                                    <a href="<?php the_permalink() ?>"><div class="trans-box">
                                            <div class="thumb pull-left col-md-4"><?php the_post_thumbnail('thumbnail', array('title' => strip_tags(get_the_title()),'class'=>'photo', 'itemprop' => 'image')); ?></div>
                                            <div class="title col-md-8"><?php the_title();?></div>
                                            <div class="description col-md-8" ><?php echo preg_replace("/<a.*<\/a>/", "\n", wp_trim_excerpt()); ?></div>
                                            <div class="clearfix"></div>
                                        </div></a>
                                </div>
                            <?php else:?>
                                <div class="col-md-3 m-item" >
                                    <a href="<?php the_permalink() ?>"><div class="red-box different">
                                            <div class="thumb"><?php the_post_thumbnail('thumbnail', array('title' => strip_tags(get_the_title()),'class'=>'photo', 'itemprop' => 'image')); ?></div>
                                            <div class="title"><?php the_title();?></div>
                                            <div class="description" ><?php echo preg_replace("/<a.*<\/a>/", "\n", wp_trim_excerpt()); ?></div>
                                        </div></a>
                                </div>
                            <?php endif;?>
                    <?php endif;?>
                <?php endwhile; ?>




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
