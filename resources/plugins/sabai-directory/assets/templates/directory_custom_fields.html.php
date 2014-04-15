<?php foreach ($this->Entity_CustomFields($entity) as $field):?>
    <div class="sabai-directory-field sabai-field-type-<?php echo str_replace('_', '-', $field['type']);?> sabai-field-name-<?php echo str_replace('_', '-', $field['name']);?> sabai-clearfix">
        <div class="sabai-field-label"><?php Sabai::_h($field['title']);?></div>
        <div class="sabai-field-value"><?php echo $this->Entity_RenderField($entity, $field['type'], $field['settings'], $field['values']);?></div>
    </div>
<?php endforeach;?>
