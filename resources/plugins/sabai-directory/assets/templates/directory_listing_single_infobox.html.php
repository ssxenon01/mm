<?php if (!empty($entity->data['directory_photos'][0]) && ($photo = $entity->data['directory_photos'][0]) && $photo->file_image[0]):?>
<div class="sabai-directory-listing-infobox sabai-clearfix">
    <div class="sabai-directory-title">
        <?php echo $this->Content_RenderTitle($entity);?>
    </div>
    <div class="sabai-directory-images">
        <?php echo $this->File_ThumbnailLink($entity, $photo->file_image[0], array('link_entity' => true, 'title' => $entity->getTitle()));?>
    </div>
<?php else:?>
<div class="sabai-directory-listing-infobox sabai-directory-listing-infobox-noimage sabai-clearfix">
    <div class="sabai-directory-title">
        <?php echo $this->Content_RenderTitle($entity);?>
    </div>
<?php endif;?>
    <div class="sabai-directory-main">
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
<?php endif;?>
<?php if (!empty($entity->directory_contact[0]['has_phone'])):?>
            <div class="sabai-directory-phone">
<?php   if (!empty($entity->directory_contact[0]['phone'])):?>
                <span class="sabai-directory-tel"><?php Sabai::_h($entity->directory_contact[0]['phone']);?></span>
<?php   endif;?>
<?php   if (!empty($entity->directory_contact[0]['mobile'])):?>
                <span class="sabai-directory-mobile"><span class="sabai-directory-separator"> / </span><?php printf(__('%s (Mobile)', 'sabai-directory'), Sabai::_h($entity->directory_contact[0]['mobile']));?></span>
<?php   endif;?>
<?php   if (!empty($entity->directory_contact[0]['fax'])):?>
                <span class="sabai-directory-fax"><span class="sabai-directory-separator"> / </span><?php printf(__('%s (Fax)', 'sabai-directory'), Sabai::_h($entity->directory_contact[0]['fax']));?></span>
<?php   endif;?>
            </div>
<?php endif;?>
        </div>
    </div>
</div>
