<?php global $title, $menu, $submenu, $pagenow, $typenow, $self, $parent_file, $submenu_file, $plugin_page, $user_identity;?>
<?php require_once ABSPATH . 'wp-admin/admin-header.php';?>
<?php echo $CSS;?>
<?php echo $JS;?>
<?php echo $HTML_HEAD;?>
<?php if (!empty($FLASH)):?>
<div class="sabai" id="sabai-flash">
<?php   foreach ($FLASH as $_flash):
          switch ($_flash['level']):
            case Sabai_Context::FLASH_ERROR:?>
    <div class="sabai-error">
<?php         break;
            case Sabai_Context::FLASH_WARNING:?>
    <div class="sabai-warning sabai-fadeout">
<?php         break;
            default:?>
    <div class="sabai-success sabai-fadeout">
<?php     endswitch;?>
        <span class="sabai-close"><i class="sabai-icon-remove"></i></span>
        <?php Sabai::_h($_flash['msg']);?>
    </div>
<?php   endforeach;?>
</div>
<?php endif;?>
<div class="wrap">
    <div id="sabai-content" class="sabai <?php echo $CONTENT_CLASSES;?>">
<?php echo $CONTENT;?>
    </div>
</div>
<?php require_once ABSPATH . 'wp-admin/admin-footer.php';?>