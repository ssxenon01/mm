<div class="sabai-row-fluid sabai-directory-search">
    <form method="get" action="<?php echo $action_url;?>">
<?php if ($category_select = $this->Taxonomy_SelectDropdown($category_bundle, array('name' => 'category', 'class' => 'sabai-pull-right', 'parent' => $category, 'current' => $current_category, 'default_text' => __('Select category', 'sabai-directory')))):?>
<?php   if (empty($search['no_loc'])):?>
        <div class="sabai-span4 sabai-directory-search-keyword"><input name="keywords" type="text" value="<?php Sabai::_h($keywords);?>" placeholder="<?php Sabai::_h(__('Search for', 'sabai-directory'));?>" /></div>
        <div class="sabai-span4 sabai-directory-search-location"><input name="address" type="text" value="<?php Sabai::_h($address);?>" placeholder="<?php echo __('Enter a location', 'sabai-directory');?>" /></div>
<?php   else:?>
        <div class="sabai-span8 sabai-directory-search-keyword"><input name="keywords" type="text" value="<?php Sabai::_h($keywords);?>" placeholder="<?php Sabai::_h(__('Search for', 'sabai-directory'));?>" /></div>
<?php   endif;?>
        <div class="sabai-span3 sabai-directory-search-category"><?php echo $category_select;?></div>
        <div class="sabai-span1 sabai-directory-search-btn"><button class="sabai-btn sabai-btn-small <?php Sabai::_h($button);?> sabai-directory-search-submit"><i class="sabai-icon-search"></i></button></div>
<?php else:?>
<?php   if (empty($search['no_loc'])):?>
        <div class="sabai-span5 sabai-directory-search-keyword"><input name="keywords" type="text" value="<?php Sabai::_h($keywords);?>" placeholder="<?php Sabai::_h(__('Search for', 'sabai-directory'));?>" /></div>
        <div class="sabai-span5 sabai-directory-search-location"><input name="address" type="text" value="<?php Sabai::_h($address);?>" placeholder="<?php echo __('Enter a location', 'sabai-directory');?>" /></div>
<?php   else:?>
        <div class="sabai-span10 sabai-directory-search-keyword"><input name="keywords" type="text" value="<?php Sabai::_h($keywords);?>" placeholder="<?php Sabai::_h(__('Search for', 'sabai-directory'));?>" /></div>
<?php   endif;?>
        <div class="sabai-span2 sabai-directory-search-btn"><button class="sabai-btn sabai-btn-small <?php Sabai::_h($button);?> sabai-directory-search-submit"><i class="sabai-icon-search"></i></button></div>
<?php endif;?>
    </form>
</div>
