<?php
/**
 * Category meta template.
 * 
 * WARNING: Include it in an action callback only. 
 */

// editing?
$meta = array('template' => '', 'sidebar' => '', 'color' => '', 'bg_image' => '', 'slider' => '', 'main_color' => '');

if (is_object($term)) {
	$meta = array_merge($meta, (array) Bunyad::options()->get('cat_meta_' . $term->term_id));
}

$render = Bunyad::factory('admin/option-renderer'); /* @var $render Bunyad_Admin_OptionRenderer */

?>

<tr class="form-field">
	<th scope="row" valign="top"><label for="meta[template]"><?php _e('Category Listing Style', 'bunyad'); ?></label></th>
	<td>
		<?php 
			echo $render->render_select(array(
				'name' => 'meta[template]',
				'options' => array(
					0 => __('Default Style (In Theme Settings)', 'bunyad'),
					'modern' => __('Masonry Style', 'bunyad'),
					'alt' => __('City Pulse Style', 'bunyad'),
					'timeline' => __('Timeline Style', 'bunyad'),
					'fun-freetime' => __('Fun Freetime Style', 'bunyad'),
					'fun-holiday' => __('Fun holiday Style', 'bunyad'),
					'health' => __('Health Style', 'bunyad')
				),
				'value' => $meta['template'],
			));
		?>
		<p class="description custom-meta"><?php _e('Select a template to use for this category', 'bunyad'); ?></p>
	</td>
</tr>

<tr class="form-field">
	<th scope="row" valign="top"><label for="meta[template]"><?php _e('Show Sidebar?', 'bunyad'); ?></label></th>
	<td>
		<?php 
			echo $render->render_select(array(
				'name' => 'meta[sidebar]',
				'options' => array('' => __('Default', 'bunyad'), 'none' => __('No Sidebar', 'bunyad'), 'right' => __('Right Sidebar', 'bunyad')),
				'value' => $meta['sidebar'],
			));
		?>
		<p class="description custom-meta"><?php _e('Select layout sidebar preference for this category\'s listing.', 'bunyad'); ?></p>
	</td>
</tr>

<tr class="form-field">
	<th scope="row" valign="top"><label for="meta[template]"><?php _e('Featured Slider?', 'bunyad'); ?></label></th>
	<td>
		<?php 
			echo $render->render_select(array(
				'name' => 'meta[slider]',
				'options' => array(
					'' => __('Disabled', 'bunyad'),
					'default' => __('Show Posts Marked for Featured Slider', 'bunyad'),
					'latest' => __('Show Latest Posts', 'bunyad'),
				),
				'value' => $meta['slider'],
			));
		?>
		<p class="description custom-meta"><?php _e('Featured slider will display on category listing.', 'bunyad'); ?></p>
	</td>
</tr>


<tr class="form-field">
	<th scope="row" valign="top"><label for="meta[color]"><?php _e('Category Color', 'bunyad'); ?></label></th>
	<td>
		<input type="text" name="meta[color]" class="colorpicker" value="<?php echo esc_html($meta['color']); ?>" data-default-color="#e54e53" />
		<p class="description custom-meta"><?php _e('SmartMag uses this in several areas such as navigation and homepage blocks.', 'bunyad'); ?></p>
	</td>
</tr>

<tr class="form-field">
	<th scope="row" valign="top"><label for="meta[main_color]"><?php _e('Main Site Color', 'bunyad'); ?></label></th>
	<td>
		<input type="text" name="meta[main_color]" class="colorpicker" value="<?php echo esc_html($meta['main_color']); ?>" data-default-color="#e54e53" />
		<p class="description custom-meta"><?php _e('Setting this color will change the entire site\'s main color when viewing this category or posts that belong to this category.', 'bunyad'); ?></p>
	</td>
</tr>

<tr class="form-field">
	<th scope="row" valign="top"><label for="meta[bg_image]"><?php _e('Category Background', 'bunyad'); ?></label></th>
	<td>
		<?php 
		echo $render->render_upload(array(
			'name'  => 'meta[bg_image]',
			'value' => $meta['bg_image'],
			'options' => array(
				'type'  => 'image',
				'title' => __('Upload This Picture', 'bunyad'), 
				'button_label' => __('Upload',  'bunyad'),
				'insert_label' => __('Use as Background',  'bunyad')
			),
		));
		?>
		<p class="description custom-meta"><?php 
			_e('SmartMag can use an image as body background in boxed layout. Note: It is not a repeating pattern. A large photo is to be used as background.', 'bunyad'); ?></p>
	</td>
</tr>

