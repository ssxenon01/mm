<?php if (!empty($entities)):?>
<?php   if (empty($settings['hide_nav'])):?>
<div class="sabai-directory-nav sabai-clearfix">
    <div class="sabai-pull-left"><?php echo $this->DropdownButtonLinks($sorts, 'small', __('Sort by: <b>%s</b>', 'sabai-directory'));?><?php if (!empty($distances)):?><?php echo $this->DropdownButtonLinks($distances, 'small', __('Radius: <b>%s</b>', 'sabai-directory'));?><?php endif;?></div>
</div>
<?php   endif;?>
<div class="sabai-directory-slider">
    <div class="sabai-entity-entities">
<?php   foreach ($entities as $entity):?>
    <?php $this->renderTemplate($entity['entity']->getBundleType() . '_single_' . $entity['display_mode'], $entity + array('show_summary' => true));?>
<?php   endforeach;?>
    </div>
</div>
<script type="text/javascript">
jQuery(document).ready(function($){
    $('.sabai-directory-slider .sabai-entity-entities').bxSlider(<?php if (isset($settings['bx_slider'])):?><?php echo json_encode($settings['bx_slider']);?><?php endif;?>);
});
</script>
<?php endif;?>
