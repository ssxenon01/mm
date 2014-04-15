<div class="sabai-btn-toolbar sabai-clearfix">
    <div class="sabai-pull-right"><?php if (count($links) === 1):?><?php echo $this->ButtonLinks($links, 'small', true, true);?><?php else:?><?php echo $this->DropdownButtonLinks($links);?><?php endif;?></div>
</div>
<?php echo $this->Form_Render($form, $form_js);?>
<?php if ($paginator->count()):?>
<div class="sabai-pagination sabai-pagination-centered sabai-directory-pagination sabai-clearfix">
  <?php echo $this->PageNav($CURRENT_CONTAINER, $paginator, $this->Url($CURRENT_ROUTE));?>
</div>
<?php endif;?>
