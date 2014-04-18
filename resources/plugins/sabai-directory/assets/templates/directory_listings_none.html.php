<?php if (empty($settings['hide_nav'])):?>
<div class="sabai-directory-nav sabai-clearfix">
    <div class="sabai-pull-left"><?php echo $this->DropdownButtonLinks($sorts, 'small', __('Sort by: <b>%s</b>', 'sabai-directory'));?><?php if (!empty($distances)):?><?php echo $this->DropdownButtonLinks($distances, 'small', __('Radius: <b>%s</b>', 'sabai-directory'));?><?php endif;?></div>
<?php   if (empty($settings['hide_nav_views'])):?>
    <div class="sabai-btn-group sabai-pull-right"><?php echo $this->ButtonLinks($views, 'small', true, !$IS_MOBILE);?></div>
<?php   endif;?>
</div>
<?php endif;?>
<p><?php echo __('No results were found', 'sabai-directory');?></p>
