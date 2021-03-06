<?php

class Bunyad_FunFreetime_Widget extends WP_Widget
{
	public function __construct()
	{
		parent::__construct(
			'bunyad-fun-freetime-widget',
			'Bunyad - Fun FreeTime',
			array('description' => 'Recent posts with thumbnail.', 'classname' => 'fun-freetime')
		);
		
		add_action('save_post', array($this, 'flush_widget_cache'));
		add_action('deleted_post', array($this, 'flush_widget_cache'));
		add_action('switch_theme', array($this, 'flush_widget_cache'));
	}
	
	// code below is modified from default
	public function widget($args, $instance) 
	{
		$cache = get_transient('bunyad_widget_fun_freetime');
		
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
		if (has_action('bunyad_widget_latest_posts_loop')):

			do_action('bunyad_widget_latest_posts_loop', $args, $r);
		
		elseif ($r->have_posts()):
?>

            <div class="news-break">
                <div class="title"><?php echo $title;?></div>
                <div class="row">
                    <?php
                    $i= 0;
                    while ($r->have_posts()) : $r->the_post(); global $post;
                    $i++;
                        ?>

                        <div class="col-md-6">
                            <a href="<?php the_permalink() ?>"><div class="<?php echo $i%2==1?'green-box':'yellow-box'?>">
                                <div class="thumb pull-left">
                                    <div class="c-title">
                                        <?php $string = get_the_title();

                                        $data = explode(' ', $string);
                                        $part = ceil(count($data) / 2);

                                        $string1 = implode(' ', array_slice($data, 0, $part));
                                        $string2 = implode(' ', array_slice($data, $part, $part*2));

                                        ?>

                                        <p class="tp"><?php echo $string1; ?></p>
                                        <p><?php echo $string2; ?></p>
                                    </div>
                                    <?php the_post_thumbnail('recent-posts', array('title' => strip_tags(get_the_title()))); ?>
                                </div>
                                <div class="description"><span><?php echo preg_replace("/<a.*<\/a>/", "\n", wp_trim_excerpt()); ?></span></div>
                            </div></a>
                        </div>

                    <?php endwhile; ?>

                </div>
            </div>

<?php
		endif;
		
		// reset the global $the_post as this query will have stomped on it
		wp_reset_postdata();

		$cache[$args['widget_id']] = ob_get_flush();
		
		set_transient('bunyad_widget_fun_freetime', $cache);
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
		delete_transient('bunyad_widget_fun_freetime');
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