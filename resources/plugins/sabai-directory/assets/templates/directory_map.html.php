<?php
$markers = array();
foreach ($entities as $entity) {
    $location = $entity['entity']->getFieldValue('directory_location');
    if (empty($location[0]['lat']) || empty($location[0]['lng'])) continue;
    ob_start();
    $this->renderTemplate($entity['entity']->getBundleType() . '_single_infobox', $entity);
    $markers[] = array(
        'lat' => $location[0]['lat'],
        'lng' => $location[0]['lng'],
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
        "<?php echo $CURRENT_CONTAINER;?>-map",
        <?php echo json_encode($markers);?>,
        null,
        null,
        <?php echo intval($zoom);?>,
        <?php if ($style):?><?php echo json_encode($this->GoogleMaps_Style($style));?><?php else:?>null<?php endif;?>,
        <?php echo json_encode(array('scrollwheel' => (bool)$scrollwheel, 'marker_clusters' => (bool)$marker_clusters));?>
    );
});
</script>
<div id="<?php echo substr($CURRENT_CONTAINER, 1);?>-map" class="sabai-googlemaps-map" style="height:<?php echo intval($height);?>px;<?php if (!empty($width)):?> width:<?php echo intval($width);?>px;<?php endif;?>"></div>