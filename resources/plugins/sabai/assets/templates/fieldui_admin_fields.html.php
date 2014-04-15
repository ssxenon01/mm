<noscript>
    <div class="sabai-error"><?php echo __('This page requires JavaScript enabled in your browser.', 'sabai');?></div>
</noscript>
<?php $this->FormTag('post', $form_submit_path, array(), array('id' => 'sabai-fieldui'));?>
<div class="sabai-clearfix sabai-fieldui">
    <div id="sabai-fieldui-active-wrap">
        <div id="sabai-fieldui-active">
            <div class="sabai-fieldui-fields">
<?php foreach ($fields as $field): unset($existing_fields[$field->getFieldName()]);?>
<?php   if ((!$field_type = @$field_types[$field->getFieldType()]) || !$field->getFieldWidget()) continue;?>
<?php   if (false === $field_preview = $this->FieldUI_PreviewWidget($field)) continue;?>
                <div id="sabai-fieldui-field<?php echo $field->getFieldId();?>" class="sabai-fieldui-field sabai-fieldui-field-type-<?php echo str_replace('_', '-', $field->getFieldType());?>">
                    <div class="sabai-fieldui-field-info">
						<div class="sabai-fieldui-field-control">
                            <a href="<?php echo $form_edit_field_url;?>" class="sabai-fieldui-field-edit" data-modal-title="<?php echo Sabai::h(strlen($field->getFieldAdminTitle()) ? $field->getFieldAdminTitle() : $field_type['label']) . ' - ' . $field->getFieldName();?>" title="<?php echo __('Edit field', 'sabai');?>"><i class="sabai-icon-cog"></i>Edit</a><?php if ($field->isCustomField()):?> &middot; <a href="#" class="sabai-fieldui-field-delete" title="<?php echo __('Delete field', 'sabai');?>"><i class="sabai-icon-trash"></i>Delete</a><?php endif;?>
                        </div>
                        <div class="sabai-fieldui-field-title"><?php echo Sabai::h(strlen($field->getFieldAdminTitle()) ? $field->getFieldAdminTitle() : $field_type['label']) . ' - ' . $field->getFieldName();?></div>
                    </div>                  
                    <div class="sabai-fieldui-field-preview"><?php echo $field_preview;?></div>
                    <div id="sabai-fieldui-field-form<?php echo $field->getFieldId();?>" class="sabai-fieldui-field-form"></div>
                    <input class="sabai-fieldui-field-id" type="hidden" name="fields[]" value="<?php echo $field->getFieldId();?>" />
                </div>
<?php endforeach;?>
            </div>
            <?php echo $this->TokenHtml('entity_admin_submit_fields');?>
            <div style="clear:both;"></div>
        </div>
    </div>
    <div id="sabai-fieldui-available-wrap">
        <div class="sabai-fieldui-available">
            <div class="sabai-fieldui-title">
                <div class="sabai-fieldui-control">
                    <a href="#" class="sabai-fieldui-toggle"><i class="sabai-icon-caret-up"></i></a>
                </div>
                <div class="sabai-fieldui-label"><?php echo __('Available Fields', 'sabai');?></div>
            </div>    
            <div class="sabai-fieldui-fields sabai-clearfix">
<?php foreach ($field_types as $field_type): if (!$field_type['creatable']) continue;?>
                <a href="<?php echo $form_create_field_url;?>" data-field-type="<?php echo $field_type['type'];?>" class="sabai-btn"><?php Sabai::_h($field_type['label']);?></a>
<?php endforeach;?>
            </div>
        </div>
<?php if (!empty($existing_fields)):?>
        <div class="sabai-fieldui-available" id="sabai-fieldui-existing-fields">
            <div class="sabai-fieldui-title">
                <div class="sabai-fieldui-control">
                    <a href="#" class="sabai-fieldui-toggle"><i class="sabai-icon-caret-up"></i></a>
                </div>
                <div class="sabai-fieldui-label"><?php echo __('Existing Fields', 'sabai');?></div>
            </div>    
            <div class="sabai-fieldui-fields sabai-clearfix">
<?php foreach ($existing_fields as $existing_field_name => $existing_field):?>
                <a href="<?php echo $form_create_field_url;?>" data-field-type="<?php echo $existing_field->getFieldType();?>" data-field-name="<?php echo $existing_field_name;?>" class="sabai-btn"><?php Sabai::_h($existing_field->getFieldAdminTitle());?></a>
<?php endforeach;?>
            </div>
        </div>
<?php endif;?>
    </div>
</div>
</form>
<div class="sabai-fieldui-field" id="sabai-fieldui-field" style="display:none;">
	<div class="sabai-fieldui-field-info">
		<div class="sabai-fieldui-field-control">
			<a href="<?php echo $form_edit_field_url;?>" class="sabai-fieldui-field-edit" data-modal-title="" title="<?php echo __('Edit field', 'sabai');?>"><i class="sabai-icon-cog"></i>Edit</a> &middot; <a href="#" class="sabai-fieldui-field-delete" title="<?php echo __('Delete field', 'sabai');?>"><i class="sabai-icon-trash"></i>Delete</a>
		</div>
		<div class="sabai-fieldui-field-title"></div>
	</div>
    <div class="sabai-fieldui-field-preview"></div>
    <div class="sabai-fieldui-field-form"></div>
    <input class="sabai-fieldui-field-id" type="hidden" name="fields[]" value="" />
</div>