<?php if ($PAGE_MENU):?>
<ul class="sabai-page-menu">
<?php   foreach ($PAGE_MENU as $_PAGE_MENU):?>
<?php     $attr = empty($_PAGE_MENU['class']) ? array() : array('class' => $_PAGE_MENU['class']);?>
<?php     if (!empty($_PAGE_MENU['ajax'])):?>
  <li><?php echo $this->LinkToRemote($_PAGE_MENU['title'], $_PAGE_MENU['ajax'] == 2 ? '#sabai-modal' : '#sabai-content', $_PAGE_MENU['url'], $_PAGE_MENU['options'], $attr);?></li>
<?php     else:?>
  <li><?php echo $this->LinkTo($_PAGE_MENU['title'], $_PAGE_MENU['url'], $_PAGE_MENU['options'], $attr);?></li>
<?php     endif;?>
<?php   endforeach;?>
</ul>
<?php endif;?>
<?php if (!empty($TAB_CURRENT)):?>
<div id="sabai-nav" class="sabai-clearfix">
<?php   foreach (array_keys($TAB_CURRENT) as $_TAB_SET): $_TAB_CURRENT = $TAB_CURRENT[$_TAB_SET];?>
  <ul class="sabai-nav sabai-nav-tabs">
<?php     foreach ($TABS[$_TAB_SET] as $_TAB_NAME => $_TAB): $attr = empty($_TAB['class']) ? array() : array('class' => $_TAB['class']);?>
	<li class="<?php if (!empty($_TAB['featured'])):?>sabai-pull-right<?php endif;?><?php if (!empty($_TAB['disabled'])):?> sabai-disabled<?php endif;?><?php if ($_TAB_NAME == $_TAB_CURRENT):?> sabai-active<?php endif;?>">
<?php       if (empty($_TAB['ajax'])):?>
      <?php echo $this->LinkTo($_TAB['title'], $_TAB['url'], $_TAB['options'], $attr);?>
<?php       else:?>
      <?php echo $this->LinkToRemote($_TAB['title'], '#sabai-content', $_TAB['url'], $_TAB['options'], $attr);?>
<?php       endif;?>
    </li>
<?php     endforeach;?>
  </ul>
<?php   endforeach;?>
<?php   if (!empty($TAB_BREADCRUMBS[$_TAB_SET]) && count($TAB_BREADCRUMBS[$_TAB_SET]) > 1): $_TAB_BREADCRUMB_LAST = array_pop($TAB_BREADCRUMBS[$_TAB_SET]);?>
  <div class="sabai-breadcrumbs sabai-tab-breadcrumbs">
<?php     foreach ($TAB_BREADCRUMBS[$_TAB_SET] as $_TAB_BREADCRUMB):?>
    <span><?php echo $this->LinkTo($_TAB_BREADCRUMB['title'], $_TAB_BREADCRUMB['url']);?></span>
    <span> &raquo; </span>
<?php     endforeach;?>
<?php Sabai::_h($_TAB_BREADCRUMB_LAST['title']);?>
  </div>
<?php   endif;?>
<?php   if (!empty($TAB_MENU[$_TAB_SET])):?>
  <ul class="sabai-tab-menu">
<?php     foreach ($TAB_MENU[$_TAB_SET] as $_TAB_MENU): $attr = empty($_TAB_MENU['class']) ? array() : array('class' => $_TAB_MENU['class']);?>
<?php       if (!empty($_TAB_MENU['ajax'])):?>
    <li><?php echo $this->LinkToRemote($_TAB_MENU['title'], $_TAB_MENU['ajax'] == 2 ? '#sabai-modal' : '#sabai-content', $_TAB_MENU['url'], $_TAB_MENU['options'], $attr);?></li>
<?php       else:?>
    <li><?php echo $this->LinkTo($_TAB_MENU['title'], $_TAB_MENU['url'], $_TAB_MENU['options'], $attr);?></li>
<?php       endif;?>
<?php     endforeach;?>
  </ul>
<?php   endif;?>
</div>
<?php endif;?>
<div id="sabai-body">
<?php echo $CONTENT;?>
</div>
<?php if (!empty($INLINE_TABS)):?>
<div id="sabai-inline" class="col-md-10">
  <div id="sabai-inline-nav">
    <ul class="sabai-nav sabai-nav-tabs">
<?php   foreach ($INLINE_TABS as $_INLINE_TAB_NAME => $_INLINE_TAB): $attr = empty($_INLINE_TAB['class']) ? array() : array('class' => $_INLINE_TAB['class']);?>
      <li class="<?php if (!empty($_INLINE_TAB['featured'])):?>sabai-pull-right<?php endif;?><?php if (!empty($_INLINE_TAB['disabled'])):?> sabai-disabled<?php endif;?><?php if ($_INLINE_TAB_NAME == $INLINE_TAB_CURRENT):?> sabai-active<?php endif;?>">
<?php     if (empty($_INLINE_TAB['ajax'])):?>
        <?php echo $this->LinkTo($_INLINE_TAB['title'], $_INLINE_TAB['url'], $_INLINE_TAB['options'], $attr);?>
<?php     else:?>
        <?php echo $this->LinkToRemote($_INLINE_TAB['title'], '#sabai-inline-content', $_INLINE_TAB['url'], array('url' => (string)$_INLINE_TAB['route'], 'content' => 'trigger.closest("ul").find("li.sabai-active").removeClass("sabai-active"); trigger.closest("li").addClass("sabai-active");') + $_INLINE_TAB['options'], $attr);?>
<?php     endif;?>
      </li>
<?php   endforeach;?>
    </ul>
  </div>
  <div id="sabai-inline-content">
    <?php echo $this->ImportRoute('#sabai-inline-content', $INLINE_TABS[$INLINE_TAB_CURRENT]['route'], $CONTEXT);?>
  </div>
</div>
    <?php if (is_active_sidebar('app-banner-sidebar')): ?>
        <div class="mid-banner">
            <?php dynamic_sidebar('app-banner-sidebar'); ?>
        </div>
    <?php endif; ?>
<?php endif;?>