<?php

class Bunyad_FunEventcalendar_Widget extends WP_Widget
{
	public function __construct()
	{
		parent::__construct(
			'bunyad-fun-eventcalendar-widget',
			'Bunyad - Fun Event Calendar',
			array('description' => 'Recent events with thumbnail.', 'classname' => 'fun-eventcalendar')
		);
		
		add_action('save_post', array($this, 'flush_widget_cache'));
		add_action('deleted_post', array($this, 'flush_widget_cache'));
		add_action('switch_theme', array($this, 'flush_widget_cache'));
	}
	
	// code below is modified from default
	public function widget($args, $instance) 
	{
		$cache = get_transient('bunyad_widget_fun_event_calendar');
		
		if (!is_array($cache)) {
			$cache = array();
		}

		if (!isset($args['widget_id'])) {
			$args['widget_id'] = $this->id;
		}
		
		// cache available
		if (isset($cache[ $args['widget_id'] ])) {
			echo $cache[ $args['widget_id'] ];
			return;
		}

		ob_start();
		extract($args);

		$title = apply_filters('widget_title', empty($instance['title']) ? __('Recent Posts', 'bunyad-widgets') : $instance['title'], $instance, $this->id_base);

        $args['scope']= 'today';
        $EM_Events = EM_Events::get($args);
        $event_count = count($EM_Events);
        ?>
        <?php if($event_count>0):?>
                <div class="news-break">
                    <div class="calendar-title"><?php echo $title;?></div>
                    <div class="event-top row">
                        <div class="col-md-4 pr0">
                            <div class="event-left">
                                <div class="today">
                                    <h1><?php echo date('d');?></h1>
                                    <h2><?php echo mysql2date('M',date('M'));?></h2>
                                    <h4><?php echo mysql2date('l',date('l'));?></h4>
                                </div>
                                <div class="prev"><a href="#"><span></span></a></div>
                                <ol class="carousel-linked-nav eventpagination">
                                    <?php for ($x=1; $x<=$event_count; $x++){
                                        echo "<li class=\"". (($x == 1)?'active':'') ."\"><a href=\"#$x\">$x</a></li>";
                                    } ?>
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
                                        <?php endforeach;?>
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
                </div>
            <?php endif;


		$cache[$args['widget_id']] = ob_get_flush();
		
		set_transient('bunyad_widget_fun_event_calendar', $cache);
	}

	public function update($new, $old) 
	{
		foreach ($new as $key => $val) {
			$new[$key] = wp_filter_kses($val);
		}
		
		$this->flush_widget_cache();

		return $new;
	}

	public function flush_widget_cache() 
	{
		delete_transient('bunyad_widget_fun_event_calendar');
	}

	public function form($instance)
	{	
		$instance = array_merge(array('title' => ''), $instance);
		extract($instance);
?>
		<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:', 'bunyad-widgets'); ?></label>
		<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" /></p>
<?php
	}
}