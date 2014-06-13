<?php
/**
 * Archives Page!
 *
 * This page is used for all kind of archives from custom post types to blog to 'by date' archives.
 *
 * Bunyad framework recommends this template to be used as generic template wherever any sort of listing
 * needs to be done.
 *
 * @link http://codex.wordpress.org/images/1/18/Template_Hierarchy.png
 */

global $bunyad_loop_template;

get_header();

if (empty($bunyad_loop_template) && Bunyad::options()->archive_loop_template == 'alt') {
    $bunyad_loop_template = 'loop-alt';
}


?>


<div class="main-content">

    <div class="container">

        <div class="row">

            <div class="col-md-8">

                <?php

                if (Bunyad::posts()->meta('featured_slider')):
                    get_template_part('partial-sliders');
                endif;

                ?>

                <div class="top-news">
                    <div class="feature-title">
                        <div class="inner">


                <?php if (is_tag()): ?>

                    <h3 class="title"><?php printf(__('Browsing: %s', 'bunyad'), '<strong>' . single_tag_title( '', false ) . '</strong>'); ?></h3>

                <?php elseif (is_category()): // category page ?>

                    <h3 class="title"><?php printf(__('Browsing: %s', 'bunyad'), '<strong>' . single_cat_title('', false) . '</strong>'); ?></h3>

                    <?php if (category_description()): ?>
                        <p class="post-content"><?php echo do_shortcode(category_description()); ?></p>
                    <?php endif; ?>

                <?php elseif (is_tax()): // custom taxonomies ?>

                    <h3 class="title"><?php printf(__('Browsing: %s', 'bunyad'), '<strong>' . single_term_title('', false) . '</strong>'); ?></h3>

                    <?php if (term_description()): ?>
                        <p class="post-content"><?php echo do_shortcode(term_description()); ?></p>
                    <?php endif; ?>

                <?php elseif (is_search()): // search page ?>
                    <?php $results = $wp_query->found_posts; ?>
                    <h3 class="title"><?php printf(__('Search Results: %s (%d)', 'bunyad'),  get_search_query(), $results); ?></h3>

                <?php elseif (is_archive()): ?>
                    <h3 class="title"><?php

                        if (is_day()):
                            printf(__('Daily Archives: %s', 'bunyad'), '<strong>' . get_the_date() . '</strong>');
                        elseif (is_month()):
                            printf(__('Monthly Archives: %s', 'bunyad'), '<strong>' . get_the_date('F, Y') . '</strong>');
                        elseif (is_year()):
                            printf(__('Yearly Archives: %s', 'bunyad'), '<strong>' . get_the_date('Y') . '</strong>');
                        endif;

                        ?></h3>
                <?php endif; ?>

                            <div class="leaf_line"></div>
                        </div>
                    </div>
                    <?php get_template_part(($bunyad_loop_template ? $bunyad_loop_template : 'loop')); ?>
                </div>


            </div>
            <div class="col-md-4">
                <div class="right-content">
                    <div class="row">
                        <div class="col-md-4">
                            <?php if (is_active_sidebar('banner-sidebar')): ?>
                                <div class="mid-banner">
                                    <?php dynamic_sidebar('banner-sidebar'); ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="col-md-8">
                            <?php if (is_active_sidebar('primary-sidebar')): ?>

                                <?php dynamic_sidebar('primary-sidebar'); ?>

                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php get_footer(); ?>
