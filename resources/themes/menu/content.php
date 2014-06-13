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
                    <span class="date"><strong>Огноо:</strong> <?php echo esc_html(get_the_date()); ?></span>
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



                                <!--<span class="share-links">

                                    <a href="http://twitter.com/home?status=<?php /*echo urlencode(get_permalink()); */?>" class="fa fa-twitter" title="<?php /*_e('Tweet It', 'bunyad'); */?>">
                                        <span class="visuallyhidden"><?php /*_e('Twitter', 'bunyad'); */?></span></a>

                                    <a href="http://www.facebook.com/sharer.php?u=<?php /*echo urlencode(get_permalink()); */?>" class="fa fa-facebook" title="<?php /*_e('Share at Facebook', 'bunyad'); */?>">
                                        <span class="visuallyhidden"><?php /*_e('Facebook', 'bunyad'); */?></span></a>

                                    <a href="http://plus.google.com/share?url=<?php /*echo urlencode(get_permalink()); */?>" class="fa fa-google-plus" title="<?php /*_e('Share at Google+', 'bunyad'); */?>">
                                        <span class="visuallyhidden"><?php /*_e('Google+', 'bunyad'); */?></span></a>

                                    <a href="http://pinterest.com/pin/create/button/?url=<?php
/*                                    echo urlencode(get_permalink()); */?>&amp;media=<?php /*echo urlencode(wp_get_attachment_url(get_post_thumbnail_id($post->ID))); */?>" class="fa fa-pinterest"
                                       title="<?php /*_e('Share at Pinterest', 'bunyad'); */?>">
                                        <span class="visuallyhidden"><?php /*_e('Pinterest', 'bunyad'); */?></span></a>

                                    <a href="http://plus.google.com/share?url=<?php /*echo urlencode(get_permalink()); */?>" class="fa fa-linkedin" title="<?php /*_e('Share at LinkedIn', 'bunyad'); */?>">
                                        <span class="visuallyhidden"><?php /*_e('LinkedIn', 'bunyad'); */?></span></a>

                                    <a href="http://www.tumblr.com/share/link?url=<?php /*echo urlencode(get_permalink()) */?>&amp;name=<?php /*echo urlencode(get_the_title()) */?>" class="fa fa-tumblr"
                                       title="<?php /*_e('Share at Tumblr', 'bunyad'); */?>">
                                        <span class="visuallyhidden"><?php /*_e('Tumblr', 'bunyad'); */?></span></a>

                                    <a href="mailto:?subject=<?php /*echo urlencode(get_the_title()); */?>&amp;body=<?php /*echo urlencode(get_permalink()); */?>" class="fa fa-envelope-o"
                                       title="<?php /*_e('Share via Email', 'bunyad'); */?>">
                                        <span class="visuallyhidden"><?php /*_e('Email', 'bunyad'); */?></span></a>

                                </span>-->

                        <?php endif; ?>



                    </div>
                </div>
            </div>
            <div class="news-body">
                <?php Bunyad::posts()->the_content(); ?>
            </div>
        </div>
    </article>
    <?php if (is_single() && Bunyad::options()->author_box) : // author box? ?>

        <h3 class="section-head"><?php _e('About Author', 'bunyad'); ?></h3>

        <?php get_template_part('partial-author'); ?>

    <?php endif; ?>

    <?php if (is_single() && Bunyad::options()->related_posts && ($posts = Bunyad::posts()->get_related(Bunyad::core()->get_sidebar() == 'none' ? 3 : 3))): // && Bunyad::options()->related_posts != false): ?>

        <div class="news-foot-tab">
            <ul id="myTab" class="nav nav-tabs">
                <li class="active"><a href="#home" data-toggle="tab">Холбоотой мэдээлэл</a></li>
                <li class=""><a href="#profile" data-toggle="tab">Их уншсан</a></li>
            </ul>
            <div id="myTabContent" class="tab-content">
                <div class="tab-pane fade active in" id="home">
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
                                        <a href="<?php the_permalink(); ?>"><div class="title"><?php get_the_title();?> <span class="published"><?php echo esc_html(get_the_date()); ?></span></div>
                                    </div>

                                <?php endforeach; wp_reset_postdata(); ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="tab-pane fade" id="profile">
                    <div class="news-swiper">
                        <a class="arrow-left" href="#"></a>
                        <a class="arrow-right" href="#"></a>
                        <div class="swiper-container most-news">
                            <div class="swiper-wrapper">
                                <div class="swiper-slide">
                                    <img src="sample/news/coffee.jpg" >
                                    <div class="title">Дэлхийн хамгийн үнэтэй кофе <span class="published">22 цаг 44 минут</span></div>
                                </div>
                                <div class="swiper-slide">
                                    <img src="sample/news/cocktail.jpg">
                                    <div class="title">Коктейль яагаад өндөр үнэтэй байдаг вэ?<span class="published">22 цаг 44 минут</span></div>
                                </div>
                                <div class="swiper-slide">
                                    <img src="sample/news/restaurant.jpg">
                                    <div class="title">Монголын Saint Germain de Pres <span class="published">22 цаг 44 минут</span></div>
                                </div>
                                <div class="swiper-slide">
                                    <img src="sample/news/restaurant.jpg">
                                    <div class="title">Монголын Saint Germain de Pres <span class="published">22 цаг 44 минут</span></div>
                                </div>
                                <div class="swiper-slide">
                                    <img src="sample/news/restaurant.jpg">
                                    <div class="title">Монголын Saint Germain de Pres <span class="published">22 цаг 44 минут</span></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    <?php endif; ?>

</div>









