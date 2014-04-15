<?php
foreach ($filters as $key => $_filter) {
    if (!is_array($_filter)) {
        $_filter = array('label' => $_filter);
        $attr = array();
    } else {
        $attr = isset($_filter['title']) ? array('title' => $_filter['title']) : array();
    }
    if ($key === $filter) {
        $attr['class'] = 'sabai-btn sabai-btn-small sabai-active';
    } else {
        $attr['class'] = 'sabai-btn sabai-btn-small';
    }     
    $filters[$key] = $this->LinkToRemote($_filter['label'], $CURRENT_CONTAINER, $this->Url($CURRENT_ROUTE, array('filter' => $key) + $url_params), array(), $attr);
}
?>
<div class="sabai-btn-toolbar">
  <div class="sabai-btn-group" ><?php echo implode(PHP_EOL, $filters);?></div>
<?php if (!empty($links)):?>
  <div class="sabai-btn-group" ><?php echo implode(PHP_EOL, $links);?></div>
<?php endif;?>
</div>
<?php echo $this->Form_Render($form, $form_js);?>
<?php if ($pager && $pager->count()):?>
<div class="sabai-pagination sabai-pagination-centered">
<?php   echo $this->PageNav($CURRENT_CONTAINER, $pager, $this->Url($CURRENT_ROUTE, $url_params));?>
</div>
<?php endif;?>