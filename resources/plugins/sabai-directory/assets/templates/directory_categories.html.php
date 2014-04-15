<?php if ($count = count($entities)): $categories = $this->SliceArray($entities, $column_count);?>
<div class="sabai-directory-categories" style="clear:both;">
<?php   foreach ($categories as $row => $columns):?>
    <div class="sabai-row-fluid">
<?php     foreach ($columns as $entity):?>
        <div class="sabai-directory-category sabai-span<?php echo intval(12 / $column_count);?>">
            <div class="sabai-directory-category-title">
<?php       if (!empty($entity['entity']->data['content_count']['directory_listing'])):?>
                <?php printf(__('%s (%d)', 'sabai-directory'), $this->Entity_Permalink($entity['entity'], array('bullet-icon' => 'folder-open')), $entity['entity']->data['content_count']['directory_listing']);?>
<?php       else:?>
                <?php echo $this->Entity_Permalink($entity['entity'], array('bullet-icon' => 'folder-open'));?>
<?php       endif;?>
            </div>
<?php       if (!empty($entity['entity']->data['child_terms'])):?>
            <ul class="sabai-directory-category-children">
<?php         foreach ($entity['entity']->data['child_terms'] as $child_term):?>
                <li>
<?php           if (!empty($child_term->data['content_count']['directory_listing'])):?>
                    <?php printf(__('%s (%d)', 'sabai-directory'), $this->Entity_Permalink($child_term), $child_term->data['content_count']['directory_listing']);?>
<?php           else:?>
                    <?php echo $this->Entity_Permalink($child_term);?>
<?php           endif;?>               
                </li>
<?php         endforeach;?>
            </ul>
<?php       endif;?>
        </div>
<?php     endforeach;?>
    </div>
<?php   endforeach;?>
</div>
<?php endif;?>