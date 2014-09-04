<?php
$this->WordPressTemplate()
    ->set('title', $CONTENT_TITLE)
    ->set('css', $CSS)
    ->set('js', $JS)
    ->set('htmlHead', $HTML_HEAD)
    ->set('htmlHeadTitle', $HTML_HEAD_TITLE)
    ->set('pageBreadcrumbs', $PAGE_BREADCRUMBS)
    ->set('siteName', $this->_application->getPlatform()->getSiteName())
    ->set('pageUrl', $CONTENT_URL)
    ->set('pageSummary', $CONTENT_SUMMARY)
    ->set('pageContent', $CONTENT)
    ->render();
?>
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
<div id="sabai-content" class="sabai <?php echo $CONTENT_CLASSES;?>">
<?php echo $CONTENT;?>
</div>