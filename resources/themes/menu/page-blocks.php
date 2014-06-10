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
                    <!--<div class="col-md-8">
                        <div class="menu-event">
                            <div class="event-title">
                                City Pulse
                                <div class="pagination pagination-event"></div>
                            </div>
                            <div class="event-swiper">
                                <a class="event-arrow-left" href="#"></a>
                                <a class="event-arrow-right" href="#"></a>
                                <div class="swiper-container e-swiper">
                                    <div class="swiper-wrapper">
                                        <div class="swiper-slide">
                                            <div class="datebg"></div>
                                            <div class="published">
                                                <div class="text">MAR</div>
                                                <div class="count">22</div>
                                            </div>
                                            <img src="/mymenu/resources/themes/menu/images/sample/event.jpg">
                                            <div class="title">Загварын агентлагуудын дунд сагсанбөмбөгийн тэмцээн</div>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                        <div class="event-list">
                            <div class="event-title">
                                Event calendar
                            </div>
                            <div class="eventlist">
                                <div class="event">
                                    <div class="img-over">
                                        <a href="#">
                                            <img class="media-object" alt="" src="/mymenu/resources/themes/menu/images/sample/event-right.jpg">
                                            <div class="datebg"></div>
                                            <div class="event-date">
                                                <div class="text">MAR</div>
                                                <div class="count">22</div>
                                            </div>
                                            <div class="line"></div>
                                        </a>
                                    </div>
                                    <div class="text-intro">
                                        <h3 class="event-t">Vintage party - VLVT</h3>
                                        <h4><span class="glyphicon glyphicon-map-marker"></span> VLVT Night Club</h4>
                                        <div class="date"> <span class="glyphicon glyphicon-time"></span>  17:00pm</div>
                                    </div>
                                </div>
                                <div class="event">
                                    <div class="img-over">
                                        <a href="#">
                                            <img class="media-object" alt="" src="/mymenu/resources/themes/menu/images/sample/event-right.jpg">
                                            <div class="datebg"></div>
                                            <div class="event-date">
                                                <div class="text">MAR</div>
                                                <div class="count">22</div>
                                            </div>
                                            <div class="line"></div>
                                        </a>
                                    </div>
                                    <div class="text-intro">
                                        <h3 class="event-t">Vintage party - VLVT</h3>
                                        <h4><span class="glyphicon glyphicon-map-marker"></span> VLVT Night Club</h4>
                                        <div class="date"> <span class="glyphicon glyphicon-time"></span>  17:00pm</div>
                                    </div>
                                </div>
                                <div class="event">
                                    <div class="img-over">
                                        <a href="#">
                                            <img class="media-object" alt="" src="/mymenu/resources/themes/menu/images/sample/event-right.jpg">
                                            <div class="datebg"></div>
                                            <div class="event-date">
                                                <div class="text">MAR</div>
                                                <div class="count">22</div>
                                            </div>
                                            <div class="line"></div>
                                        </a>
                                    </div>
                                    <div class="text-intro">
                                        <h3 class="event-t">Vintage party - VLVT</h3>
                                        <h4><span class="glyphicon glyphicon-map-marker"></span> VLVT Night Club</h4>
                                        <div class="date"> <span class="glyphicon glyphicon-time"></span>  17:00pm</div>
                                    </div>
                                </div>
                                <div class="event">
                                    <div class="img-over">
                                        <a href="#">
                                            <img class="media-object" alt="" src="/mymenu/resources/themes/menu/images/sample/event-right.jpg">
                                            <div class="datebg"></div>
                                            <div class="event-date">
                                                <div class="text">MAR</div>
                                                <div class="count">22</div>
                                            </div>
                                            <div class="line"></div>
                                        </a>
                                    </div>
                                    <div class="text-intro">
                                        <h3 class="event-t">Vintage party - VLVT</h3>
                                        <h4><span class="glyphicon glyphicon-map-marker"></span> VLVT Night Club</h4>
                                        <div class="date"> <span class="glyphicon glyphicon-time"></span>  17:00pm</div>
                                    </div>
                                </div>
                                <div class="event">
                                    <div class="img-over">
                                        <a href="#">
                                            <img class="media-object" alt="" src="/mymenu/resources/themes/menu/images/sample/event-right.jpg">
                                            <div class="datebg"></div>
                                            <div class="event-date">
                                                <div class="text">MAR</div>
                                                <div class="count">22</div>
                                            </div>
                                        </a>
                                    </div>
                                    <div class="text-intro">
                                        <h3 class="event-t">fashion party</h3>
                                        <h4><span class="glyphicon glyphicon-map-marker"></span> Marquee 27 entertainment club</h4>
                                        <div class="date">17:00pm</div>
                                    </div>
                                </div>

                            </div>
                        </div>
                        <div class="right-box">
                            <div class="box-title">
                                Facebook Like
                            </div>
                            <div class="box-body">
                                <iframe src="//www.facebook.com/plugins/likebox.php?href=https%3A%2F%2Fwww.facebook.com%2FFacebookDevelopers&amp;width=210&amp;height=258&amp;colorscheme=light&amp;show_faces=true&amp;header=false&amp;stream=false&amp;show_border=false&amp;appId=660742257292404" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:210px; height:258px;" allowTransparency="true"></iframe>
                            </div>
                        </div>
                    </div>-->
                </div>
            </div>
        </div>
    </div>
        <!--tsbar-->
		<?php Bunyad::core()->theme_sidebar(); ?>
        <!--tsbar end-->
	</div>

<?php get_footer(); ?>

