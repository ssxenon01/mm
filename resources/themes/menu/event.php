<?php

/**
 * Content Template is used for every post format and used on single posts
 */
global $EM_Event;
?>
<div class="event-calendar">
    <div class="category">Event calendar</div>
    <div class="single-event">
        <div class="preview">
            <h1><?php the_title(); ?></h1><a href="http://www.facebook.com/sharer/sharer.php?u=<?php esc_url(get_permalink());?>"><img class="fb" src="http://mymenu.mn/resources/themes/menu/images/facebook.png"></a><a href="https://twitter.com/share" data-via="menumagazine"><img  class="tw" src="http://mymenu.mn/resources/themes/menu/images//twitter.png"></a>
            <?php the_post_thumbnail('main-slider')?>
            <div class="detail">
                <?php echo mysql2date( 'm', $EM_Event->event_end_time );?> - <?php echo mysql2date( 'd', $EM_Event->event_end_time );?> - <?php echo mysql2date( 'Y', $EM_Event->event_end_time );?><br>
                <span><?php echo get_the_title($EM_Event->get_location())?></span>
            </div>
        </div>

        <div class="content">
            <div class="pull-right" style="margin:0 0 10px 20px;">
                <?php
                $EM_Location = $EM_Event->get_location();

                if ( get_option('dbem_gmap_is_active') && ( is_object($EM_Location) && $EM_Location->location_latitude != 0 && $EM_Location->location_longitude != 0 ) ) {
                    //get dimensions with px or % added in
                    $width = (!empty($args['width'])) ? $args['width']:get_option('dbem_map_default_width','400px');
                    $width = preg_match('/(px)|%/', $width) ? $width:$width.'px';
                    $height = (!empty($args['height'])) ? $args['height']:get_option('dbem_map_default_height','300px');
                    $height = preg_match('/(px)|%/', $height) ? $height:$height.'px';
                    //assign random number for element id reference
                    $rand = substr(md5(rand().rand()),0,5);
                    ?>
                    <div class="em-location-map-container"  style='position:relative; background: #CDCDCD; width: <?php echo $width ?>; height: <?php echo $height ?>;'>
                        <div class='em-location-map' id='em-location-map-<?php echo $rand ?>' style="width: 100%; height: 100%;">
                            <?php _e('Loading Map....', 'dbem'); ?>
                        </div>
                    </div>
                    <div class='em-location-map-info' id='em-location-map-info-<?php echo $rand ?>' style="display:none; visibility:hidden;">
                        <div class="em-map-balloon" style="font-size:12px;">
                            <div class="em-map-balloon-content" ><?php echo $EM_Location->output(get_option('dbem_location_baloon_format')); ?></div>
                        </div>
                    </div>
                    <div class='em-location-map-coords' id='em-location-map-coords-<?php echo $rand ?>' style="display:none; visibility:hidden;">
                        <span class="lat"><?php echo $EM_Location->location_latitude; ?></span>
                        <span class="lng"><?php echo $EM_Location->location_longitude; ?></span>
                    </div>
                <?php
                }
                /* @var $EM_Event EM_Event */
    //            echo $EM_Event->output_single();

                ?>
                </div>
                <div class="">
                    <?php echo $EM_Event->post_content;?>
                </div>

        </div>
    </div>




</div>









