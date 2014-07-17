<!DOCTYPE html>

<!--[if IE 8]> <html class="ie ie8" <?php language_attributes(); ?>> <![endif]-->
<!--[if IE 9]> <html class="ie ie9" <?php language_attributes(); ?>> <![endif]-->
<!--[if gt IE 9]><!--> <html <?php language_attributes(); ?>> <!--<![endif]-->

<head>

<?php 
/*
 * Match wp_head() indent level
 */
?>

<meta charset="<?php bloginfo('charset'); ?>" />
<title><?php wp_title(''); // stay compatible with SEO plugins ?></title>

<?php if (!Bunyad::options()->no_responsive): // don't add if responsiveness disabled ?> 
<meta name="viewport" content="width=device-width, initial-scale=1" />
<?php endif; ?>
<link rel="pingback" href="<?php bloginfo('pingback_url'); ?>" />
	
<?php if (Bunyad::options()->favicon): ?>
<link rel="shortcut icon" href="<?php echo esc_attr(Bunyad::options()->favicon); ?>" />	
<?php endif; ?>

<?php if (Bunyad::options()->apple_icon): ?>
<link rel="apple-touch-icon-precomposed" href="<?php echo esc_attr(Bunyad::options()->apple_icon); ?>" />
<?php endif; ?>
	
<?php wp_head(); ?>
	
<!--[if lt IE 9]>
<script src="<?php echo get_template_directory_uri(); ?>/js/html5.js" type="text/javascript"></script>
<![endif]-->
<!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
<!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
<!--[if lt IE 9]>
<script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
<script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
<![endif]-->

</head>

<body <?php body_class(); ?>>

<?php if (!Bunyad::options()->disable_topbar): ?>
    <!--Top Nav-->
    <section class="topnav">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <div class="pull-left">
                        <ul class="topmenu">
                            <li>
                                <a href="/contact">Холбоо барих</a>
                            </li>
                            <li>
                                <a href="/about">Бидний тухай</a>
                            </li>
                            <li>
                                <a href="/collaborate">Хамтран ажиллах</a>
                            </li>
                        </ul>
                    </div>
                    <?php dynamic_sidebar('top-bar'); ?>
                </div>
            </div>
        </div>
    </section>
    <!--End top nav-->
<?php endif;
$category = get_category( get_query_var( 'cat' ) );
$cat_id = $category->cat_ID;
?>
<section class="wrapper <?php echo('cat-'.$cat_id)?>">
    <!-- menu -->
    <div class="navbar navbar-default" role="navigation" style="margin-right: auto;">
        <div class="container force-reset">
            <div class="row">
                <div class="col-md-12">
                    <div class="navbar-brand col-md-2" style="padding-right: 0;">
                        <a href="<?php echo esc_url(home_url('/')); ?>" title="<?php echo esc_attr(get_bloginfo('name', 'display')); ?>" rel="home">
                            <?php if (Bunyad::options()->image_logo): // custom logo ?>

                                <img style="width: 110%;" src="<?php echo home_url('/').'resources/themes/menu/images/app-logo.png' ?>" class="hidden-sm" alt="<?php
                                echo esc_attr(get_bloginfo('name', 'display')); ?>" <?php
                                echo (Bunyad::options()->image_logo_retina ? 'data-at2x="'. Bunyad::options()->image_logo_retina .'"' : '');
                                ?> />

                            <?php else: ?>
                                <?php echo do_shortcode(Bunyad::options()->text_logo); ?>
                            <?php endif; ?>
                        </a>
                    </div>


                    <div class="navbar-header">
                        <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
                            <span class="sr-only">Toggle navigation</span>
                            <span class="icon-bar"></span>
                            <span class="icon-bar"></span>
                            <span class="icon-bar"></span>
                        </button>
                    </div>

                    <img class="hidden-xs col-md-10" src="http://mymenu.mn/beta/sample/banner.jpg" style="
    float: right;
    padding: 15px;
">
                    <?php wp_nav_menu(array('theme_location' => 'main', 'fallback_cb' => '', 'walker' =>  'Bunyad_Menu_Walker','container_class' => 'collapse navbar-collapse','menu_class'=>'nav navbar-nav')); ?>
                    <?php if (!Bunyad::options()->disable_breadcrumbs): ?>
                        <?php Bunyad::core()->breadcrumbs(); ?>
                    <?php endif; ?>
                </div>

            </div>
        </div>
    </div>
    <!-- Menu end -->
    <div class="m-container app-header">

