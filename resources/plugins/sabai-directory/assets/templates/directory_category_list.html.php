<div class="sabai-btn-toolbar sabai-clearfix">
    <div class="sabai-btn-group sabai-pull-left"><?php echo $this->ButtonLinks($sorts, 'small', true, true);?></div>
    <div class="sabai-btn-group sabai-pull-right"><?php echo $this->ButtonLinks($links, 'small', true, true);?></div>
</div>
<?php if (!empty($entities)):?>
<?php   $this->renderTemplate('directory_categories', array('entities' => $entities, 'column_count' => $this->Config(null, 'display', 'category_columns')));?>
<?php   if ($paginator->count() > 1):?>
<div class="sabai-pagination sabai-directory-pagination sabai-clearfix">
    <?php echo $this->PageNav($CURRENT_CONTAINER, $paginator, $this->Url($CURRENT_ROUTE, $url_params));?>
</div>
<?php   endif;?>
<?php endif;?>