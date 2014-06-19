<?php

/**
 * "loop" to display posts when using an existing query. Uses content.php template
 * to render in normal format.
 */

?>


	<?php

	global $bunyad_loop;

	if (!is_object($bunyad_loop)) {
		$bunyad_loop = $wp_query;
	}

	if ($bunyad_loop->have_posts()):

	?>


    <?php while ($bunyad_loop->have_posts()): $bunyad_loop->the_post(); ?>
        <article <?php post_class('highlights'); ?> itemscope itemtype="http://schema.org/Article">
            <div class="news-list clearfix">
                <div class="img-container before">
                    <?php the_post_thumbnail('slider-small', array('title' => strip_tags(get_the_title()))); ?>
                </div>
                <div class="news-body">
                    <a href="<?php the_permalink() ?>"><h4><?php if (get_the_title()) the_title(); else the_ID(); ?></h4></a>
                    <div class="body-text"> <?php echo preg_replace("/<a.*<\/a>/", "\n", wp_trim_excerpt()); ?> </div>
                    <a class="more" href="<?php the_permalink() ?>">Дэлгэрэнгүй <span></span></a>
                </div>
            </div>
        </article>
    <?php endwhile; ?>

	<?php if (!Bunyad::options()->blog_no_pagination): // pagination can be disabled ?>

	<div class="main-pagination">
		<?php echo Bunyad::posts()->paginate(array(), $bunyad_loop); ?>
	</div>

	<?php endif; ?>


	<?php else: ?>

		<article id="post-0" class="page no-results not-found">
			<div class="post-content">
				<h1><?php _e( 'Nothing Found!', 'bunyad' ); ?></h1>
				<p><?php _e('Apologies, but no results were found for the requested archive. Perhaps searching will help find a related post.', 'bunyad'); ?></p>
			</div><!-- .entry-content -->
		</article><!-- #post-0 -->

	<?php endif; ?>
