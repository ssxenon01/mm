<?php if (empty($entities)):?>
<div class="sabai-directory-nav sabai-clearfix">
    <div class="sabai-pull-left"><?php echo __('No results were found', 'sabai-directory');?></div>
    <div class="sabai-btn-group sabai-pull-right"><?php echo implode(PHP_EOL, $links);?></div>
</div>
<?php return; endif;?>
<div class="sabai-directory-nav sabai-clearfix">
    <div class="sabai-btn-group sabai-pull-left"><?php echo $this->ButtonLinks($sorts, 'small', true, true);?></div>
    <div class="sabai-btn-group sabai-pull-right"><?php echo implode(PHP_EOL, $links);?></div>
</div>

<div class="sabai-entity-entities" style="clear:both;">
<?php foreach ($entities as $entity):?>
    <?php $this->renderTemplate($entity['entity']->getBundleType() . '_single_favorited', $entity);?>
<?php endforeach;?>
</div>

<div class="sabai-directory-pagination sabai-clearfix">
<?php if ($paginator->count() > 1):?>
    <div class="sabai-pull-left">
        <?php printf(__('Showing %d - %d of %s results', 'sabai-directory'), $paginator->getElementOffset() + 1, $paginator->getElementOffset() + $paginator->getElementLimit(), $this->NumberFormat($paginator->getElementCount()));?>
    </div>
    <div class="sabai-pull-right sabai-pagination">
        <?php echo $this->PageNav($CURRENT_CONTAINER, $paginator, $this->Url($CURRENT_ROUTE, $url_params));?>
    </div>
<?php else:?>
    <div class="sabai-pull-left">
        <?php printf(_n('Showing %s result', 'Showing %s results', $paginator->getElementCount(), 'sabai-directory'), $this->NumberFormat($paginator->getElementCount()));?>
    </div>
<?php endif;?>
</div>