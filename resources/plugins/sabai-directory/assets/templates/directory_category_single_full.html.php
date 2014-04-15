<div id="<?php echo $id;?>" class="<?php echo $class;?> sabai-clearfix">
<?php if ($body = $this->Taxonomy_RenderBody($entity)):?>
    <div class="sabai-directory-body">
        <?php echo $body;?>
    </div>
<?php endif;?>
    <div class="sabai-directory-custom-fields">
        <?php $this->renderTemplate(array('directory_category_custom_fields', 'directory_custom_fields'), array('entity' => $entity));?>
    </div>
<?php if ($count = count((array)@$entity->data['child_terms'])): $categories = $this->SliceArray($entity->data['child_terms'], $column_count = $this->Config($this->Entity_Addon($entity), 'display', 'category_columns'));?>
    <div class="sabai-directory-categories">
<?php   foreach ($categories as $row => $columns):?>
        <div class="sabai-row-fluid">
<?php     foreach ($columns as $category):?>
            <div class="sabai-span<?php echo intval(12 / $column_count);?>">
<?php       if ($listing_count = (int)@$category->data['content_count']['directory_listing']):?>
                <?php printf(__('%s (%d)', 'sabai-directory'), $this->Entity_Permalink($category, array('bullet-icon' => 'folder-open')), $listing_count);?>
<?php       else:?>
                <?php echo $this->Entity_Permalink($category, array('bullet-icon' => 'folder-open'));?>
<?php       endif;?>
            </div>
<?php     endforeach;?>
        </div>
<?php   endforeach;?>
    </div>
<?php endif;?>
    <div class="sabai-entity-links sabai-btn-group"><?php echo $this->ButtonLinks($links);?></div>
</div>