<?php
/*
	Template Name: Homepage & Blocks (Advanced)
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

                <?php while (have_posts()) : the_post(); ?>

                    <?php

                    $panels = get_post_meta(get_the_ID(), 'panels_data', true);

                    if (!empty($panels) && !empty($panels['grid'])):

                        get_template_part('content', 'builder');

                    else:
                        if(get_post_type()=="event"){
                            get_template_part('event', 'single');
                        }else if(get_post_type()=="location")
                            get_template_part('page', 'single');
                        else
                            get_template_part('content', 'single');

                    endif;
                    ?>

                <?php endwhile; // end of the loop. ?>
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


