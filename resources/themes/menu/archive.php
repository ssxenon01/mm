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
if (empty($bunyad_loop_template) && Bunyad::options()->archive_loop_template == 'modern'){
    $bunyad_loop_template = 'loop-masonry';
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
