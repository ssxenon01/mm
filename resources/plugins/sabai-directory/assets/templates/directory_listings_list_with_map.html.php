<?php
$markers = array();
foreach ($entities as $entity) {
    $location = $entity['entity']->getFieldValue('directory_location');
    if (empty($location[0]['lat']) || empty($location[0]['lng'])) continue;
    ob_start();
    $template = $entity['entity']->getBundleType() . '_single_infobox';
    $this->renderTemplate($entity['entity']->isFeatured() ? array($template . '_featured', $template) : $template, $entity);
    $markers[] = array(
        'lat' => $location[0]['lat'],
        'lng' => $location[0]['lng'],
        'trigger' => '#sabai-entity-content-' . $entity['entity']->getId() . ' a.sabai-entity-bundle-type-directory-listing',
        'content' => ob_get_clean(),
        'icon' => $this->Directory_ListingMapMarkerUrl($entity['entity']),
    );
}
?>
<script type="text/javascript">
<?php if ($CURRENT_CONTAINER === '#sabai-content' || $CURRENT_CONTAINER === '#sabai-inline-content' || strpos($CURRENT_CONTAINER, '#sabai-embed') === 0):?>
google.setOnLoadCallback(function() {
<?php else:?>
jQuery(document).ready(function($) {
<?php endif;?>
    SABAI.Directory.googleMap(
        "<?php echo $CURRENT_CONTAINER;?>-directory-map",
        <?php echo json_encode($markers);?>,
        function (center, bounds, zoom) {
            SABAI.ajax({
                type: <?php if (defined('SABAI_FIX_URI_TOO_LONG') && SABAI_FIX_URI_TOO_LONG):?>"post"<?php else:?>"get"<?php endif;?>,
                target: "#sabai-directory-listings",
                url: "<?php echo $this->Url($CURRENT_ROUTE);?>",
                data: "<?php echo http_build_query($url_params);?>&is_drag=1&center=" + center.lat() + "," + center.lng() + "&sw=" + bounds.getSouthWest().lat() + "," + bounds.getSouthWest().lng() + "&ne=" + bounds.getNorthEast().lat() + "," + bounds.getNorthEast().lng() + "&zoom=" + zoom,
                onError: function(error) {SABAI.flash(error.message, "error");}
            });
        },
        <?php if (!empty($center) && ($is_drag || $is_geolocate || empty($markers))):?><?php echo json_encode($center);?><?php else:?>null<?php endif;?>,
        <?php echo isset($settings['map']['listing_default_zoom']) ? intval($settings['map']['listing_default_zoom']) : 15;?>,
        <?php if ($settings['map']['style']):?><?php echo json_encode($this->GoogleMaps_Style($settings['map']['style']));?><?php else:?>null<?php endif;?>,
        <?php echo json_encode($settings['map']['options']);?>
    );
    <?php if (empty($settings['scroll_list'])):?>jQuery("<?php echo $CURRENT_CONTAINER;?>-directory-map-container").stickyScroll({topSpacing: 40, stopper: ".sabai-directory-pagination"});<?php endif;?>
});
</script>
<?php if (empty($settings['hide_nav'])):?>
<div class="sabai-directory-nav sabai-clearfix">
    <div class="sabai-pull-left"><?php echo $this->DropdownButtonLinks($sorts, 'small', __('Sort by: <b>%s</b>', 'sabai-directory'));?><?php if (!empty($distances)):?><?php echo $this->DropdownButtonLinks($distances, 'small', __('Radius: <b>%s</b>', 'sabai-directory'));?><?php endif;?></div>
<?php   if (empty($settings['hide_nav_views'])):?>
    <div class="sabai-btn-group sabai-pull-right"><?php echo $this->ButtonLinks($views, 'small', true, !$IS_MOBILE);?></div>
<?php   endif;?>
</div>
<?php endif;?>
<div class="sabai-directory-search-results sabai-row-fluid">
    <div class="sabai-span<?php echo 12 - $settings['map']['span'];?>">
        <div class="sabai-entity-entities"<?php if (!empty($settings['scroll_list'])):?> style="overflow-y:scroll; overflow-x:hidden; height:<?php echo $settings['map']['list_height'] + 25;?>px;"<?php endif;?>>
<?php foreach ($entities as $entity): $template = $entity['entity']->getBundleType() . '_single_' . $entity['display_mode'];?>
            <?php $this->renderTemplate($entity['entity']->isFeatured() ? array($template . '_featured', $template) : $template, $entity);?>
<?php endforeach;?>
        </div>
    </div>
    <div class="sabai-span<?php echo intval($settings['map']['span']);?>">
        <div id="<?php echo substr($CURRENT_CONTAINER, 1);?>-directory-map-container">
            <div class="sabai-directory-map-header"><input id="<?php echo substr($CURRENT_CONTAINER, 1);?>-directory-map-update" type="checkbox" /><label for="<?php echo substr($CURRENT_CONTAINER, 1);?>-directory-map-update"><?php echo __('Redo search when map moved', 'sabai-directory');?></label></div>
            <div id="<?php echo substr($CURRENT_CONTAINER, 1);?>-directory-map" class="sabai-directory-listings-map sabai-googlemaps-map" style="height:<?php echo intval($settings['map']['list_height']);?>px;"></div>
        </div>
    </div>
</div>
<?php if (empty($settings['hide_pager'])):?>
<div class="sabai-directory-pagination sabai-clearfix">
<?php   if ($paginator->count() > 1):?>
    <div class="sabai-pull-left">
        <?php printf(__('Showing %d - %d of %s results', 'sabai-directory'), $paginator->getElementOffset() + 1, $paginator->getElementOffset() + $paginator->getElementLimit(), $this->NumberFormat($paginator->getElementCount()));?>
    </div>
    <div class="sabai-pull-right sabai-pagination">
        <?php echo $this->PageNav('#sabai-directory-listings', $paginator, $this->Url($CURRENT_ROUTE, $url_params));?>
    </div>
<?php   else:?>
    <div class="sabai-pull-left">
        <?php printf(_n('Showing %s result', 'Showing %s results', $paginator->getElementCount(), 'sabai-directory'), $this->NumberFormat($paginator->getElementCount()));?>
    </div>
<?php   endif;?>
</div>
<?php endif;?>
