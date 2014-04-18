<?php $this->renderTemplate('directory_searchbox', array('action_url' => $action_url, 'search' => array('no_loc' => !empty($no_loc)), 'address' => '', 'keywords' => '', 'current_category' => null, 'category' => 0, 'category_bundle' => $category_bundle, 'button' => $button));?>
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
    }); 
});
</script>