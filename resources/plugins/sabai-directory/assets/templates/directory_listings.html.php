<?php if (empty($settings['hide_searchbox'])):?>
<?php   $this->renderTemplate('directory_searchbox', array('action_url' => (string)$this->Url($CURRENT_ROUTE), 'search' => $settings['search'], 'address' => $settings['address'], 'keywords' => isset($settings['keywords'][0]) ? implode(' ', $settings['keywords'][0]) : '', 'current_category' => $settings['category'], 'category' => $settings['parent_category'], 'category_bundle' => $settings['category_bundle']));?>
<script type="text/javascript">
jQuery(document).ready(function($) {
    jQuery(".sabai-directory-search-submit").click(function(e){
        var $this = jQuery(this),
            form = $this.closest("form");
        form.find("[placeholder]").each(function() {
            var input = $(this);
            if (input.val() == input.attr("placeholder")) {
                input.val("");
            }
        });
        SABAI.ajax({
            trigger: $this,
            type: form.attr("method"),
            target: "#sabai-directory-listings",
            scrollTo: "#sabai-directory-listings",
            url: form.attr("action"),
            data: "<?php unset($url_params['center'], $url_params['is_geolocate']); echo http_build_query($url_params);?>&" + form.serialize() + "&<?php echo Sabai_Request::PARAM_AJAX;?>=" + encodeURIComponent("#sabai-directory-listings")
        });
        e.preventDefault();
    }); 
});
</script>
<?php endif;?>
<div id="sabai-directory-listings">
<?php if (empty($entities) && empty($is_geolocate)):?>
    <?php $this->renderTemplate('directory_listings_none', array('sorts' => $sorts, 'views' => $views, 'distances' => $distances, 'settings' => $settings));?>
<?php else:?>
    <?php $this->renderTemplate('directory_listings_' . $settings['view'], array('entities' => $entities, 'paginator' => $paginator, 'url_params' => $url_params, 'sorts' => $sorts, 'views' => $views, 'distances' => $distances, 'center' => $center, 'settings' => $settings, 'is_drag' => $is_drag, 'is_geolocate' => $is_geolocate));?>
<?php endif;?>
</div>