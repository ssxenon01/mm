<?php
/**
 * @author marcus
 * Standard events list widget
 */
class EM_Widget extends WP_Widget {
	
	var $defaults;
	
    /** constructor */
    function EM_Widget() {
    	$this->defaults = array(
    		'title' => __('Events','dbem'),
    		'scope' => 'future',
    		'order' => 'ASC',
    		'limit' => 5,
    		'category' => 0,
    		'format' => '#_EVENTLINK<ul><li>#_EVENTDATES</li><li>#_LOCATIONTOWN</li></ul>',
    		'nolistwrap' => false,
    		'orderby' => 'event_start_date,event_start_time,event_name',
			'all_events' => 0,
			'all_events_text' => __('all events', 'dbem'),
			'no_events_text' => __('No events', 'dbem')
    	);
		$this->em_orderby_options = apply_filters('em_settings_events_default_orderby_ddm', array(
			'event_start_date,event_start_time,event_name' => __('start date, start time, event name','dbem'),
			'event_name,event_start_date,event_start_time' => __('name, start date, start time','dbem'),
			'event_name,event_end_date,event_end_time' => __('name, end date, end time','dbem'),
			'event_end_date,event_end_time,event_name' => __('end date, end time, event name','dbem'),
		)); 
    	$widget_ops = array('description' => __( "Display a list of events on Events Manager.", 'dbem') );
        parent::WP_Widget(false, $name = 'Events', $widget_ops);	
    }

    /** @see WP_Widget::widget */
    function widget($args, $instance) {
    	$instance = array_merge($this->defaults, $instance);
    	$instance = $this->fix_scope($instance); // depcreciate	

        $instance['owner'] = false;
        //orderby fix for previous versions with old orderby values
        if( !array_key_exists($instance['orderby'], $this->em_orderby_options) ){
            //replace old values
            $old_vals = array(
                'name' => 'event_name',
                'end_date' => 'event_end_date',
                'start_date' => 'event_start_date',
                'end_time' => 'event_end_time',
                'start_time' => 'event_start_time'
            );
            foreach($old_vals as $old_val => $new_val){
                $instance['orderby'] = str_replace($old_val, $new_val, $instance['orderby']);
            }
        }

        $events = EM_Events::get(apply_filters('em_widget_events_get_args',$instance));
        ?>

        <div class="event-list">
            <div class="event-title">
                <span><?php echo apply_filters('widget_title',$instance['title'], $instance, $this->id_base);?></span>
            </div>
            <div class="eventlist">
                <?php
                $i = 0;
                foreach($events as $event):
                    $i++;
                    ?>
                    <div class="event">
                        <div class="img-over">
                            <a href="<?php echo get_permalink($event->ID);?>">
                                <?php if(has_post_thumbnail($event->ID))
                                    echo get_the_post_thumbnail($event->ID,'thumbnail', array('title' => strip_tags(get_the_title()), 'itemprop' => 'image' , 'class' => 'media-object'));
                                    else
                                        echo '<div class="empty"></div>'
                                ?>
                                <div class="datebg"></div>
                                <div class="event-date">
                                    <div class="text"><?php echo mysql2date( 'M', $event->start );?></div>
                                    <div class="count"><?php echo mysql2date( 'd', $event->start );?></div>
                                </div>
                                <?php if(count($events) != $i):?><div class="line"></div><?php endif;?>
                            </a>
                        </div>
                        <div class="text-intro">
                            <div class="event-description">
                                <?php echo preg_replace("/<a.*<\/a>/", "\n", $event->post_content); ?>
                            </div>
                            <!--<h3 class="event-t"><?php /*echo get_the_title($event->ID)*/?></h3>
                            <h4><span class="glyphicon glyphicon-map-marker"></span> <?php /*echo get_the_title($event->get_location())*/?></h4>
                            <div class="date"> <span class="glyphicon glyphicon-time"></span>  <?php /**/?><?php /*echo mysql2date( 'H:i', $event->event_start_time );*/?></div>-->
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php
    }

    /** @see WP_Widget::update */
    function update($new_instance, $old_instance) {
    	foreach($this->defaults as $key => $value){
    		if( !isset($new_instance[$key]) ){
    			$new_instance[$key] = $value;
    		}
    	}
    	return $new_instance;
    }

    /** @see WP_Widget::form */
    function form($instance) {
    	$instance = array_merge($this->defaults, $instance);
    	$instance = $this->fix_scope($instance); // depcreciate
        ?>
		<p>
			<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title', 'dbem'); ?>: </label>
			<input type="text" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" value="<?php echo esc_attr($instance['title']); ?>" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('limit'); ?>"><?php _e('Number of events','dbem'); ?>: </label>
			<input type="text" id="<?php echo $this->get_field_id('limit'); ?>" name="<?php echo $this->get_field_name('limit'); ?>" size="3" value="<?php echo esc_attr($instance['limit']); ?>" />
		</p>
		<p>
			
			<label for="<?php echo $this->get_field_id('scope'); ?>"><?php _e('Scope','dbem'); ?>: </label><br/>
			<select id="<?php echo $this->get_field_id('scope'); ?>" name="<?php echo $this->get_field_name('scope'); ?>" >
				<?php foreach( em_get_scopes() as $key => $value) : ?>   
				<option value='<?php echo $key ?>' <?php echo ($key == $instance['scope']) ? "selected='selected'" : ''; ?>>
					<?php echo $value; ?>
				</option>
				<?php endforeach; ?>
			</select>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('order'); ?>"><?php _e('Order By','dbem'); ?>: </label>
			<select  id="<?php echo $this->get_field_id('orderby'); ?>" name="<?php echo $this->get_field_name('orderby'); ?>">
				<?php  
					echo $this->em_orderby_options;
				?>
				<?php foreach($this->em_orderby_options as $key => $value) : ?>   
	 			<option value='<?php echo $key ?>' <?php echo ( !empty($instance['orderby']) && $key == $instance['orderby']) ? "selected='selected'" : ''; ?>>
	 				<?php echo $value; ?>
	 			</option>
				<?php endforeach; ?>
			</select> 
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('order'); ?>"><?php _e('Order','dbem'); ?>: </label>
			<select id="<?php echo $this->get_field_id('order'); ?>" name="<?php echo $this->get_field_name('order'); ?>">
				<?php 
				$order_options = apply_filters('em_widget_order_ddm', array(
					'ASC' => __('Ascending','dbem'),
					'DESC' => __('Descending','dbem')
				)); 
				?>
				<?php foreach( $order_options as $key => $value) : ?>   
	 			<option value='<?php echo $key ?>' <?php echo ($key == $instance['order']) ? "selected='selected'" : ''; ?>>
	 				<?php echo $value; ?>
	 			</option>
				<?php endforeach; ?>
			</select>
		</p>
		<p>
            <label for="<?php echo $this->get_field_id('category'); ?>"><?php _e('Category IDs','dbem'); ?>: </label>
            <input type="text" id="<?php echo $this->get_field_id('category'); ?>" name="<?php echo $this->get_field_name('category'); ?>" size="3" value="<?php echo esc_attr($instance['category']); ?>" /><br />
            <em><?php _e('1,2,3 or 2 (0 = all)','dbem'); ?> </em>
        </p>
		<p>
			<label for="<?php echo $this->get_field_id('format'); ?>"><?php _e('List item format','dbem'); ?>: </label>
			<textarea rows="5" cols="24" id="<?php echo $this->get_field_id('format'); ?>" name="<?php echo $this->get_field_name('format'); ?>"><?php echo $instance['format']; ?></textarea>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('all_events'); ?>"><?php _e('Show all events link at bottom?','dbem'); ?>: </label>
			<input type="checkbox" id="<?php echo $this->get_field_id('all_events'); ?>" name="<?php echo $this->get_field_name('all_events'); ?>" <?php echo (!empty($instance['all_events']) && $instance['all_events']) ? 'checked':''; ?> >
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('all_events'); ?>"><?php _e('All events link text?','dbem'); ?>: </label>
			<input type="text" id="<?php echo $this->get_field_id('all_events_text'); ?>" name="<?php echo $this->get_field_name('all_events_text'); ?>" value="<?php echo (!empty($instance['all_events_text'])) ? $instance['all_events_text']:__('all events','dbem'); ?>" >
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('no_events_text'); ?>"><?php _e('No events text','dbem'); ?>: </label>
			<input type="text" id="<?php echo $this->get_field_id('no_events_text'); ?>" name="<?php echo $this->get_field_name('no_events_text'); ?>" value="<?php echo (!empty($instance['no_events_text'])) ? $instance['no_events_text']:__('No events', 'dbem'); ?>" >
		</p>
        <?php 
    }
    
    /**
     * Backwards compatability for an old setting which is now just another scope.
     * @param unknown_type $instance
     * @return string
     */
    function fix_scope($instance){
    	if( !empty($instance['time_limit']) && is_numeric($instance['time_limit']) && $instance['time_limit'] > 1 ){
    		$instance['scope'] = $instance['time_limit'].'-months';
    	}elseif( !empty($instance['time_limit']) && $instance['time_limit'] == 1){
    		$instance['scope'] = 'month';
    	}elseif( !empty($instance['time_limit']) && $instance['time_limit'] == 'no-limit'){
    		$instance['scope'] = 'all';
    	}
    	return $instance;
    }
}
add_action('widgets_init', create_function('', 'return register_widget("EM_Widget");'));
?>