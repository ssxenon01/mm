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

<?php $this->renderTemplate('directory_listing_photo_single_'. $current_photo['display_mode'], $current_photo + array('link_to_listing' => !empty($link_to_listing)));?>

<div class="sabai-directory-photobox-nav">
<?php $i = 0; while ($photos = array_slice($entities, $i * 6, 6)):?>
    <div class="sabai-row-fluid">
<?php   foreach ($photos as $photo):?>
        <div class="sabai-span2 sabai-directory-thumbnail<?php if ($current_photo['entity']->getId() === $photo['entity']->getId()):?> sabai-active<?php endif;?>">
            <?php echo $this->LinkToRemote('<img src="' . $this->Directory_PhotoUrl($photo['entity'], 'thumbnail') . '" alt="" />', $CURRENT_CONTAINER, $this->Url($CURRENT_ROUTE, $url_params + array('photo_id' => $photo['entity']->getId(), Sabai::$p => $paginator->getCurrentPage())), array('no_escape' => true), array('title' => Sabai::h($photo['entity']->getTitle())));?>
        </div>
<?php   endforeach; ++$i;?>
    </div>
<?php endwhile;?>
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