<div id="<?php echo $id;?>" class="<?php echo $class;?> sabai-clearfix sabai-row-fluid">
    <div class="sabai-row-fluid">
        <div class="sabai-span3 sabai-directory-images">
<?php if (!empty($entity->data['directory_photos'][0])): $photo = $entity->data['directory_photos'][0];?>
            <?php echo $this->File_ThumbnailLink($entity, $photo->file_image[0], array('link_entity' => true));?>
<?php else:?>
            <img src="<?php echo $this->ImageUrl('no_image_small.png');?>" alt="" />
<?php endif;?>
        </div>
        <div class="sabai-span9 sabai-directory-main">
            <div class="sabai-directory-title">
                <?php echo $this->Content_RenderTitle($entity);?>
            </div>
<?php if (!empty($entity->voting_rating['']['count'])):?>
            <div class="sabai-directory-rating">
                <?php echo $this->Voting_RenderRating($entity);?>
                <span class="sabai-directory-rating-average"><?php echo number_format($entity->voting_rating['']['average'], 2);?></span>
                <span class="sabai-directory-rating-count"><?php printf(_n('(%d review)', '(%d reviews)', $entity->voting_rating['']['count'], 'sabai-directory'), $entity->voting_rating['']['count']);?></span>
            </div>
<?php endif;?>
<?php if ($entity->directory_category):?>
            <div class="sabai-directory-taxonomy">
<?php   foreach ($entity->directory_category as $category):?>
                <?php echo $this->Entity_Permalink($category, array('bullet-icon' => 'folder-open'));?>
<?php   endforeach;?>
            </div>
<?php endif;?>
            <div class="sabai-directory-info sabai-clearfix">
<?php if (!empty($entity->directory_location[0]['address'])):?>
                <div class="sabai-directory-address">
                    <?php Sabai::_h($entity->directory_location[0]['address']);?>
                </div>
<?php endif?>
<?php if (!empty($entity->directory_contact[0]['phone'])):?>
                <div class="sabai-directory-phone">
                    <span class="sabai-directory-tel"><?php Sabai::_h($entity->directory_contact[0]['phone']);?></span>
<?php   if (!empty($entity->directory_contact[0]['mobile'])):?>
                    <span> / </span>
                    <span class="sabai-directory-mobile"><?php printf(__('%s (Mobile)', 'sabai-directory'), Sabai::_h($entity->directory_contact[0]['mobile']));?></span>
<?php   endif;?>
<?php   if (!empty($entity->directory_contact[0]['fax'])):?>
                    <span> / </span>
                    <span class="sabai-directory-fax"><?php printf(__('%s (Fax)', 'sabai-directory'), Sabai::_h($entity->directory_contact[0]['fax']));?></span>
<?php   endif;?>
                </div>
<?php endif;?>
            </div>
<?php if (!empty($show_summary) && ($listing_body = $this->Content_RenderSummary($entity, 100))):?>
            <div class="sabai-directory-body">
                <?php echo $listing_body;?>
            </div>
<?php endif;?>
        </div>
<?php if (!empty($links)):?>
        <div class="sabai-entity-links sabai-btn-group"><?php echo $this->ButtonLinks($links);?></div>
<?php endif;?>
    </div>
</div>