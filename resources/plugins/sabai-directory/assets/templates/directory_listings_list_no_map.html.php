<?php if (empty($settings['hide_nav'])):?>
<div class="sabai-directory-nav sabai-clearfix">
    <div class="sabai-pull-left"><?php echo $this->DropdownButtonLinks($sorts, 'small', __('Sort by: <b>%s</b>', 'sabai-directory'));?><?php if (!empty($distances)):?><?php echo $this->DropdownButtonLinks($distances, 'small', __('Radius: <b>%s</b>', 'sabai-directory'));?><?php endif;?></div>
<?php   if (empty($settings['hide_nav_views'])):?>
    <div class="sabai-btn-group sabai-pull-right"><?php echo $this->ButtonLinks($views, 'small', true, true);?></div>
<?php   endif;?>
</div>
<?php endif;?>
<div class="sabai-directory-search-results">
    <div class="sabai-entity-entities">
<?php foreach ($entities as $entity): $template = $entity['entity']->getBundleType() . '_single_' . $entity['display_mode'];?>
        <?php $this->renderTemplate($entity['entity']->isFeatured() ? array($template . '_featured', $template) : $template, $entity + array('show_summary' => true));?>
<?php endforeach;?>
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