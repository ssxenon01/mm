<?php if ($geocode_error):?>
<div class="sabai-error" style="margin-bottom:15px;"><?php Sabai::_h($geocode_error);?></div>
<?php endif;?>
<?php if ($settings['map']['list_show']):?>
    <?php $this->renderTemplate('directory_listings_list_with_map', array('entities' => $entities, 'paginator' => $paginator, 'url_params' => $url_params, 'sorts' => $sorts, 'views' => $views, 'distances' => $distances, 'center' => $center, 'settings' => $settings, 'is_drag' => $is_drag, 'is_geolocate' => $is_geolocate));?>
<?php   else:?>
    <?php $this->renderTemplate('directory_listings_list_no_map', array('entities' => $entities, 'paginator' => $paginator, 'url_params' => $url_params, 'sorts' => $sorts, 'views' => $views, 'distances' => $distances, 'settings' => $settings));?>
<?php endif;?>
