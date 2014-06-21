<?php

/**
 * Content Template is used for every post format and used on single posts
 */

// post has review?
$review = Bunyad::posts()->meta('reviews');

?>
<div class="content">
    <article id="post-<?php the_ID(); ?>" <?php post_class(($review ? 'hreview' : '')); ?> itemscope itemtype="http://schema.org/Article">
        <div class="news-read">
            <div class="news-title">
                <h1><?php if (is_singular()): the_title(); else: ?>

                    <a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>" rel="bookmark">
                        <?php the_title(); ?></a>

                <?php endif;?>
                </h1>
                <div class="news-top-info">
                    <span class="date" itemprop="datePublished" datetime="<?php echo esc_attr(get_the_time('c')); ?>"><strong>Огноо:</strong> <?php echo esc_html(get_the_date()); ?></span>
                    <span class="news-tag"><strong>Төрөл:</strong> <?php echo get_the_category_list(__(', ', 'bunyad')); ?></span>
                </div>
                <div class="news-desc clearfix">
                    <div class="pull-right">
                        <span class="news-comment"><a href="<?php comments_link(); ?>"><span class="glyphicon glyphicon-comment"></span><?php echo get_comments_number(); ?></span></a>
                        <span class="news-view"><a href="#"><span class="glyphicon glyphicon-eye-open"></span><?php echo (int) get_post_meta(get_the_ID(), '_count-views_all', true) ;?></span></a>
                    </div>
                    <div class="pull-left">

                        <?php if (Bunyad::options()->social_share): ?>

                            <div class="fblike">
                                <div class="fb-like" data-href="<?php echo urlencode(get_permalink()); ?>" data-layout="button_count" data-action="like" data-show-faces="true" data-share="false"></div>
                            </div>
                            <div class="twitter-share">
                                <a href="https://twitter.com/share" class="twitter-share-button" data-via="menumagazine">Tweet</a>
                            </div>

                        <?php endif; ?>

                    </div>
                </div>
            </div>
            <div class="news-body" itemprop="articleBody">

                <?php if (get_post_format() == 'gallery'): // get gallery template ?>

                    <?php get_template_part('partial-single-coverflow'); ?>


                <?php endif; // normal featured image ?>

                <?php Bunyad::posts()->the_content(); ?>
            </div>
        </div>
    </article>
    <?php if (is_single() && Bunyad::options()->author_box) : // author box? ?>

        <h3 class="section-head"><?php _e('About Author', 'bunyad'); ?></h3>

        <?php get_template_part('partial-author'); ?>

    <?php endif; ?>

    <?php $posts = Bunyad::posts()->get_related(6,get_the_ID()) // && Bunyad::options()->related_posts != false): ?>

        <div class="news-foot-tab">
            <ul id="myTab" class="nav nav-tabs">
                <li class="active"><a href="#comment" data-toggle="tab">Сэтгэгдэл</a></li>
                <li><a href="#home" data-toggle="tab">Санал болгох</a></li>
            </ul>
            <div id="myTabContent" class="tab-content">
                <div class="tab-pane fade active in" id="comment">
                    <div class="comments">
                        <?php comments_template('', true); ?>
                    </div>
                </div>
                <div class="tab-pane fade in" id="home">
                    <div class="news-swiper">
                        <a class="arrow-left" href="#"></a>
                        <a class="arrow-right" href="#"></a>
                        <div class="swiper-container">
                            <div class="swiper-wrapper">
                                <?php foreach ($posts as $post): setup_postdata($post); ?>
                                    <div class="swiper-slide">
                                        <?php the_post_thumbnail(
                                            (Bunyad::core()->get_sidebar() == 'none' ? 'main-block' : 'gallery-block'),
                                            array('class' => 'image', 'title' => strip_tags(get_the_title()))); ?>
                                        <a href="<?php the_permalink(); ?>"><div class="title"><?php get_the_title();?> <span class="published"><?php echo esc_html(get_the_date()); ?></span></div></a>
                                    </div>

                                <?php endforeach; wp_reset_postdata(); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

</div>









