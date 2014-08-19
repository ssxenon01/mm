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

?>


<div class="main-content">

    <div class="container">

        <div class="row">

            <div class="col-md-8">

                <div class="col-8 main-content page">

                    <div class="post-content error-page row">

                        <div class="col-9">
                            <h1><?php _e('Page Not Found!', 'bunyad'); ?></h1>
                            <p>
                                <?php _e("We're sorry, but we can't find the page you were looking for. It's probably some thing we've done wrong but now we know about it and we'll try to fix it. In the meantime, try one of these options:", 'bunyad'); ?>
                            </p>
                            <ul class="links fa-ul">
                                <li><i class="fa fa-angle-double-right"></i> <a href="javascript: history.go(-1);"><?php _e('Go to Previous Page', 'bunyad'); ?></a></li>
                                <li><i class="fa fa-angle-double-right"></i> <a href="<?php echo site_url(); ?>"><?php _e('Go to Homepage', 'bunyad'); ?></a></li>
                            </ul>
                        </div>

                    </div>

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
                                <div class="clearfix"></div>

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
