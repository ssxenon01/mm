<script type="text/javascript">
jQuery(document).ready(function ($) {
    var loadDirectory = function (center) {
        SABAI.ajax({
            type: <?php if (defined('SABAI_FIX_URI_TOO_LONG') && SABAI_FIX_URI_TOO_LONG):?>"post"<?php else:?>"get"<?php endif;?>,
            target: "#sabai-directory-listings-geolocate",
            url: "<?php echo $this->Url('/sabai/directory', $url_params, '', '&');?>&is_geolocate=1&center=" + center,
            onError: function (error) {SABAI.flash(error.message, "error");},
            onContent: function () {SABAI.GoogleMaps.autocomplete("#sabai-directory-listings-geolocate .sabai-directory-search-location input", {componentRestrictions: {<?php if ($country):?>country: "<?php echo $country;?>"<?php endif;?>}});}
        });
    };
    if (navigator.geolocation) {
        var timeout = setTimeout(function () {clearTimeout(timeout); loadDirectory('');}, 10000);
        navigator.geolocation.getCurrentPosition(
            function (pos) {clearTimeout(timeout); loadDirectory(pos.coords.latitude + "," + pos.coords.longitude);},
            function () {clearTimeout(timeout); loadDirectory('');},
            {timeout:5000}
        );
    } else {
        loadDirectory('');
    }
});
</script>
<div id="sabai-directory-listings-geolocate"></div>
