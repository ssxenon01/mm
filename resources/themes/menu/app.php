<?php
/*
	Template Name: App
*/

get_header('app');


?>


<div class="main-content">

    <div class="container">

        <div class="row">

            <div class="col-md-12" style=" padding: 0; padding-top: 20px;">

                <?php while (have_posts()) : the_post(); ?>

                    <?php Bunyad::posts()->the_content(); ?>

                <?php endwhile; // end of the loop. ?>
            </div>
        </div>
    </div>

    <?php get_footer(); ?>


