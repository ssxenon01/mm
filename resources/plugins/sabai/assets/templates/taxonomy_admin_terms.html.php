<div class="sabai-btn-toolbar sabai-clearfix">
<?php if (!empty($links)):?>
  <div class="sabai-btn-group sabai-pull-right"><?php echo implode(PHP_EOL, $links);?></div>
<?php endif;?>
</div>
<?php echo $this->Form_Render($form, $form_js);?>
<?php if ($pager && $pager->count()):?>
<div class="sabai-pagination sabai-pagination-centered">
<?php   echo $this->PageNav($CURRENT_CONTAINER, $pager, $this->Url($CURRENT_ROUTE, $url_params));?>
</div>
<?php endif;?>