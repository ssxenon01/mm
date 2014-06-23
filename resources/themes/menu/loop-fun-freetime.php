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
    <div class="row">
        <div class="news-break" style="margin:15px">
            <div class="title">Чөлөөт цаг</div>
            <div class="row">

                <?php $i= 0; while ($bunyad_loop->have_posts()): $bunyad_loop->the_post(); $i++;?>
                    <article <?php post_class(); ?> itemscope itemtype="http://schema.org/Article">

                        <div class="col-md-6">
                            <a href="<?php the_permalink() ?>" itemprop="url">
                                <div class="<?php echo $i%2==1?'green-box':'yellow-box'?>">
                                <div class="thumb pull-left">
                                    <div class="c-title">
                                        <?php $string = get_the_title();

                                        $data = explode(' ', $string);
                                        $part = ceil(count($data) / 2);

                                        $string1 = implode(' ', array_slice($data, 0, $part));
                                        $string2 = implode(' ', array_slice($data, $part, $part*2));

                                        ?>
                                        <p class="tp"><?php echo $string1; ?></p>
                                        <p><?php echo $string2; ?></p>
                                    </div>
                                    <?php the_post_thumbnail('recent-posts', array('title' => strip_tags(get_the_title()))); ?>
                                </div>
                                <div class="description"><span><?php echo preg_replace("/<a.*<\/a>/", "\n", wp_trim_excerpt()); ?></span></div>
                            </div></a>
                        </div>
                    </article>
                <?php endwhile; ?>
            </div>
        </div>
    </div>
<?php endif; ?>
