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
    <div class="row" >
        <div class="news-break" style="margin:15px">
            <div class="title">Амралт, баяр ёслол</div>
            <div class="row" style="margin: 0 -27px;">
                <?php
                while ($bunyad_loop->have_posts()) : $bunyad_loop->the_post(); global $post;
                    ?>
                    <div class="col-md-4">
                        <div class="trans-box">
                            <div class="thumb">
                                <?php the_post_thumbnail('recent-posts', array('title' => strip_tags(get_the_title()))); ?>
                            </div>
                            <h1><?php the_title();?></h1>
                            <div class="description"><?php echo preg_replace("/<a.*<\/a>/", "\n", wp_trim_excerpt()); ?></div>
                            <div class="more-link"><a href="<?php the_permalink() ?>">Дэлгэрэнгүй</a></div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>
    </div>
<?php endif; ?>
