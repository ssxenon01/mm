<?php
foreach ($buttons as $key => $_button) {
    $class = 'sabai-btn sabai-btn-mini';
    if ($key === $status) {
        $class .= ' sabai-active';
    }    
    $buttons[$key] = $this->LinkToRemote($_button, $CURRENT_CONTAINER, $this->Url($CURRENT_ROUTE, array('status' => $key) + $url_params), array(), array('class' => $class));
}
?>
<div class="sabai-btn-toolbar sabai-clearfix">
    <div class="sabai-btn-group sabai-pull-left"><?php echo implode(PHP_EOL, $buttons);?></div>
<?php if (!empty($links)):?>
    <div class="sabai-btn-group sabai-pull-right"><?php echo implode('&nbsp;', $links);?></div>
<?php endif;?>
</div>
<?php if (!empty($form->settings['#filters'])): uasort($form->settings['#filters'], create_function('$a,$b','return $a["order"] < $b["order"] ? -1 : 1;'));?>
<div class="sabai-entity-filters sabai-clearfix">
    <?php $this->FormTag('get', $CURRENT_ROUTE, $url_params, array(), true);?>
<?php   foreach ($form->settings['#filters'] as $filter_name => $filter):?>
        <select name="<?php Sabai::_h($filter_name);?>">
            <option value="0"><?php Sabai::_h($filter['default_option_label']);?></option>
<?php     foreach ($filter['options'] as $filter_option_value => $filter_option_label):?>
            <option value="<?php Sabai::_h($filter_option_value);?>"<?php if (isset($url_params[$filter_name]) && $url_params[$filter_name] == $filter_option_value):?> selected="selected"<?php endif;?>><?php Sabai::_h($filter_option_label);?></option>
<?php     endforeach;?>
        </select>
<?php   endforeach;?>
        <select name="limit">
<?php   foreach (array(20, 30, 50, 100) as $limit):?>
            <option value="<?php echo $limit;?>"<?php if ($limit == $url_params['limit']):?> selected="selected"<?php endif;?>><?php echo $limit;?></option>
<?php   endforeach;?>
        </select>
        <input type="text" name="content_keywords" value="<?php Sabai::_h($url_params['content_keywords']);?>" size="10" />
<?php   foreach (array_diff_key($url_params, $form->settings['#filters']) as $url_param_k => $url_param_v): if (in_array($url_param_k, array('limit', 'content_keywords'))) continue;?>
        <input type="hidden" name="<?php Sabai::_h($url_param_k);?>" value="<?php Sabai::_h($url_param_v);?>" />
<?php   endforeach;?>
        <button type="submit" class="sabai-btn sabai-btn-small"><?php echo __('Filter', 'sabai');?></button>
    </form>
</div><?php endif;?>
<?php echo $this->Form_Render($form, $form_js);?>
<?php if ($pager && $pager->count()):?>
<div class="sabai-pagination sabai-pagination-centered">
    <?php echo $this->PageNav($CURRENT_CONTAINER, $pager, $this->Url($CURRENT_ROUTE, $url_params));?>
</div>
<?php endif;?>