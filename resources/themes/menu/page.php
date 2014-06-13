<?php
/**
 * Default Page Template
 */

get_header();


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

                <?php if (have_posts()): the_post(); endif; // load the page ?>




                <!--contents here-->
                <?php Bunyad::posts()->the_content(); ?>

                <!--contents end here-->
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
