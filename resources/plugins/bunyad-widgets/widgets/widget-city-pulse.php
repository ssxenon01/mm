<?php

class Bunyad_CityPulse_Widget extends WP_Widget
{
	public function __construct()
	{
		parent::__construct(
			'bunyad-city-pulse-widget',
			'Xenon - CityPulse',
			array('description' => 'Recent gallery with thumbnail.', 'classname' => 'citypulse')
		);
		
		add_action('save_post', array($this, 'flush_widget_cache'));
		add_action('deleted_post', array($this, 'flush_widget_cache'));
		add_action('switch_theme', array($this, 'flush_widget_cache'));
	}


    public function widget($args, $instance){
        $cache = get_transient('bunyad-widget-city-pulse');

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

        $title = apply_filters('widget_title', empty($instance['title']) ? __('CityPulse', 'bunyad-widgets') : $instance['title'], $instance, $this->id_base);
        if (empty($instance['number']) || !$number = absint($instance['number'])) {
            $number = 5;
        }

        // start of args
        $query_args = array(
            'posts_per_page' => $number, 'post_status' => 'publish', 'ignore_sticky_posts' => 1
        );

        // limit by category
        if (!empty($instance['category'])) {
            $query_args['cat'] = $instance['category'];
        }

        $r = new WP_Query($query_args);

        // do custom loop if available
        if (has_action('bunyad-widget-city-pulse_loop')):

            do_action('bunyad-widget-city-pulse_loop', $args, $r);
        elseif ($r->have_posts()):
            ?>

            <div class="menu-event">
                <div class="event-title">
                    <?php echo $title; ?>
                    <div class="pagination pagination-event"></div>
                </div>
                <div class="event-swiper">
                    <a class="event-arrow-left" href="#"></a>
                    <a class="event-arrow-right" href="#"></a>
                    <div class="swiper-container e-swiper">
                        <div class="swiper-wrapper">

                        <?php  while ($r->have_posts()) : $r->the_post(); global $post;  ?>

                                <div class="swiper-slide">
                                    <div class="datebg"></div>
                                    <div class="published">
                                        <div class="text">MAR</div>
                                        <div class="count">22</div>
                                    </div>

                                    <?php
                                    if(has_post_thumbnail($post->ID))
                                        echo get_the_post_thumbnail($post->ID,array(230,360,true));
                                    ?>
                                    <a href="<?php the_permalink(); ?>"><div class="title"><?php echo $post->post_title; ?></div></a>
                                </div>

                        <?php endwhile; ?>
                        </div>
                    </div>
                </div>
            </div>


        <?php
        endif;

        // reset the global $the_post as this query will have stomped on it
        wp_reset_postdata();

        $cache[$args['widget_id']] = ob_get_flush();

        set_transient('bunyad-widget-city-pulse', $cache);
    }
	
	// code below is modified from default
	public function widget_old($args, $instance)
	{
		$cache = get_transient('bunyad-widget-city-pulse');

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

		$title = apply_filters('widget_title', empty($instance['title']) ? __('CityPulse', 'bunyad-widgets') : $instance['title'], $instance, $this->id_base);
		if (empty($instance['number']) || !$number = absint($instance['number'])) {
 			$number = 5;
		}

		// start of args
		$query_args = array(
			'posts_per_page' => $number, 'post_status' => 'publish', 'ignore_sticky_posts' => 1
		);

		// limit by category
		if (!empty($instance['category'])) {
			$query_args['cat'] = $instance['category'];
		}

		$r = new WP_Query($query_args);

		// do custom loop if available
		if (has_action('bunyad-widget-city-pulse_loop')):

			do_action('bunyad-widget-city-pulse_loop', $args, $r);

		elseif ($r->have_posts()):
?>

			<?php echo $before_widget; ?>

			<?php if ($title): ?>
				<?php echo $before_title . $title . $after_title; ?>
			<?php endif;?>


            <div class="flickr-widget">

                <?php  while ($r->have_posts()) : $r->the_post(); global $post;  ?>

                    <div class="flickr_badge_image">
                        <a href="<?php the_permalink(); ?>">
                            <?php echo get_the_post_thumbnail($post->ID,'thumbnail') ?>
                        </a>
                    </div>

                <?php endwhile; ?>

            </div>

			<?php echo $after_widget; ?>
<?php
		endif;

		// reset the global $the_post as this query will have stomped on it
		wp_reset_postdata();

		$cache[$args['widget_id']] = ob_get_flush();

		set_transient('bunyad-widget-city-pulse', $cache);
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
		delete_transient('bunyad-widget-city-pulse');
	}

	public function form($instance)
	{	
		$instance = array_merge(array('title' => '', 'number' => 5, 'category' => ''), $instance);
		extract($instance);
?>
		<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:', 'bunyad-widgets'); ?></label>
		<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" /></p>

		<p><label for="<?php echo $this->get_field_id('category'); ?>"><?php _e('Limit to Category:', 'bunyad-widgets'); ?></label>
		<?php wp_dropdown_categories(array(
				'show_option_all' => __('-- Not Limited --', 'bunyad-widgets'), 
				'hierarchical' => 1, 
				'order_by' => 'name', 
				'class' => 'widefat', 
				'name' => $this->get_field_name('category'), 
				'selected' => $category
		)); ?>
		</p>

		<p><label for="<?php echo $this->get_field_id('number'); ?>"><?php _e('Number of posts to show:', 'bunyad-widgets'); ?></label>
		<input id="<?php echo $this->get_field_id('number'); ?>" name="<?php echo $this->get_field_name('number'); ?>" type="text" value="<?php echo $number; ?>" size="3" /></p>
<?php
	}
}