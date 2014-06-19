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

                <div class="col-md-3" >
                    <div class="red-box">
                        <div class="thumb"><img src="http://localhost/beta/sample/event-right.jpg" ></div>
                        <div class="title">Дэлхийн хамгийн үнэтэй кофе</div>
                        <div class="description" >Masonry is a JavaScript grid layout library. It works by placing elements in optimal position based on available vertical space, sort of like a mason fitting stones in ...</div>
                    </div>
                </div>

                <div class="col-md-3" >
                    <div class="thumb-box">
                        <div class="thumb"><img src="http://localhost/beta/sample/event-right.jpg" ></div>
                        <div class="ribbon"><div class="ribbon-stitches-top"></div><strong class="ribbon-content"><h1>Эрүүл хооллолт</h1></strong><div class="ribbon-stitches-bottom"></div></div>
                    </div>
                </div>

                <div class="col-md-3" >
                    <div class="yellow-box">
                        <div class="thumb"><img src="http://localhost/beta/sample/event-right.jpg" ></div>
                        <div class="title">Дэлхийн хамгийн үнэтэй кофе</div>
                        <div class="description" >Masonry is a JavaScript grid layout library. It works by placing elements in optimal position based on available vertical space, sort of like a mason fitting stones in ...</div>
                    </div>
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
