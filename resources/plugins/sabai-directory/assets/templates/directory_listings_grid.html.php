<?php if (empty($settings['hide_nav'])):?>
<div class="sabai-directory-nav sabai-clearfix">
    <div class="sabai-pull-left"><?php echo $this->DropdownButtonLinks($sorts, 'small', __('Sort by: <b>%s</b>', 'sabai-directory'));?><?php if (!empty($distances)):?><?php echo $this->DropdownButtonLinks($distances, 'small', __('Radius: <b>%s</b>', 'sabai-directory'));?><?php endif;?></div>
<?php   if (empty($settings['hide_nav_views'])):?>
    <div class="sabai-btn-group sabai-pull-right"><?php echo $this->ButtonLinks($views, 'small', true, !$IS_MOBILE);?></div>
<?php   endif;?>
</div>
<?php endif;?>
<div class="sabai-directory-search-results">        
<?php $_entities = $this->SliceArray($entities, $settings['grid_columns'], false);?>
    <div class="sabai-entity-entities">
<?php   foreach ($_entities as $row => $columns):?>
        <div class="sabai-row-fluid">
<?php     foreach ($columns as $entity): $template = $entity['entity']->getBundleType() . '_single_column';?>
            <?php $this->renderTemplate($entity['entity']->isFeatured() ? array($template . '_featured', $template) : $template, array('span' => intval(12 / $settings['grid_columns'])) + $entity);?>
<?php     endforeach;?>
        </div>
<?php   endforeach;?>
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
