<?php
/*
 * Default Events List Template
 * This page displays a list of events, called during the em_content() if this is an events list page.
 * You can override the default display settings pages by copying this file to yourthemefolder/plugins/events-manager/templates/ and modifying it however you need.
 * You can display events however you wish, there are a few variables made available to you:
 * 
 * $args - the args passed onto EM_Events::output()
 * 
 */


$args['scope']= 'today';

$EM_Events = EM_Events::get($args);
$event_count = count($EM_Events);
?>

<div class="city-pulse" >
<div class="row">
<!--    --><?php //if($event_count>0):?>
<div class="col-md-12">
    <div class="feature-title">
        <div class="inner">
            <h3 class="title">Event</h3>
            <div class="leaf_line"></div>
        </div>
    </div>
</div>
<div class="event-top">
    <div class="col-md-4 pr0">
        <div class="event-left">
            <div class="today">
                <h1><?php echo date('d');?></h1>
                <h2><?php echo mysql2date('M',date('M'));?></h2>
                <h4><?php echo mysql2date('l',date('l'));?></h4>
            </div>
            <div class="prev"><a href="#"><span></span></a></div>
            <ol class="carousel-linked-nav eventpagination">
                <?php for ($x=1; $x<=$event_count; $x++)
                    echo "<li class=\"". (($x == 1)?'active':'') ."\"><a href=\"#$x\">$x</a></li>";
                ?>
            </ol>
            <div class="next"><a href="#"><span></span></a></div>
        </div>
    </div>
    <div class="col-md-8">
        <div class="eventswiper">
            <div id="myCarousel" class="carousel slide" data-ride="carousel">


                <!-- Wrapper for slides -->
                <div class="carousel-inner">
                    <?php $i = 0; foreach($EM_Events as $event): $i ++;?>
                    <div class="item <?php echo ($i==1)?'active':''; ?>">
                        <?php echo get_the_post_thumbnail($event->ID,'main-slider');?>
                        <div class="carousel-caption">
                            <div class="title">
                                <a href="<?php echo get_the_permalink($event->ID);?>">
                                    <?php echo get_the_title($event->ID);?>
                                </a>
                            </div>
                            <div class="date"><?php echo mysql2date( 'H:i', $event->event_start_time );?> - <?php echo mysql2date( 'H:i', $event->event_end_time );?></div>
                        </div>
                    </div>
                    <? endforeach;?>
                </div>

                <!-- Controls -->
                <a class="left carousel-control" href="#myCarousel" data-slide="prev">
                    <span class="glyphicon glyphicon-chevron-left"></span>
                </a>
                <a class="right carousel-control" href="#myCarousel" data-slide="next">
                    <span class="glyphicon glyphicon-chevron-right"></span>
                </a>
            </div>
        </div>
    </div>
</div>
<div class="clearfix"></div>
<!--    --><?php //endif; ?>
<div class="col-md-12">
<div class="event-container">
<div class="title">Энэ сард</div>

<?php


$args['header_format'] = !empty($args['header_format']) ? $args['header_format'] :  get_option('dbem_event_list_groupby_header_format', '<h2>#s</h2>');
$args['scope']= 'month';
$EM_Events = EM_Events::get($args);
$event_count = count($EM_Events);

$format = (!empty($args['date_format'])) ? $args['date_format']:get_option('date_format');
$events_dates = array();
foreach($EM_Events as $EM_Event):
    $start_of_week = get_option('start_of_week');
    $day_of_week = date('w',$EM_Event->start);
    $day_of_week = date('w',$EM_Event->start);
    $offset = $day_of_week - $start_of_week;
    if($offset<0)
        $offset += 7;
    $offset = $offset * 60*60*24; //days in seconds
    $start_day = strtotime($EM_Event->start_date);
    $events_dates[$start_day - $offset][] = $EM_Event;
endforeach;
foreach ($events_dates as $event_day_ts => $events): ?>
<div class="event-list">
    <div class="event-list-title"><a href="#"><?php echo str_replace('#s', date_i18n('M d',$event_day_ts). ' аас ' .date_i18n('M d',$event_day_ts+(60*60*24*6)), '#s');?> <span>(<?php echo count($events)?> эвэнт)</a></a></div>
    <div class="body">
        <div class="row">
            <?php foreach($events as $event):?>
            <div class="col-md-4">
                <div class="event">
                    <div class="top">
                        <div class="top-image">
                            <?php echo get_the_post_thumbnail($event->ID,'slider-small');?>
                        </div>
                    </div>
                    <div class="event-title">
                        <a href="<?php echo get_permalink($event->ID)?>"><?php echo get_the_title($event->ID)?></a>
                    </div>
                    <div class="description">
                        <ul>
                            <li><span class="glyphicon glyphicon-time"></span><span><?php echo mysql2date('M d,l',$event->start)?> <?php echo mysql2date('H:i',$event->event_start_time)?> </span></</li>
                            <li><span class="glyphicon glyphicon-map-marker"></span><span><?php echo get_the_title($event->get_location())?></span></</li>
                            <li><span class="glyphicon glyphicon glyphicon-tag"></span><span>Beach Party</span></</li>
                        </ul>
                    </div>
                </div>
            </div>
            <?php endforeach;?>

        </div>
    </div>
</div>
<?php endforeach;?>
</div>
</div>
</div>
</div>