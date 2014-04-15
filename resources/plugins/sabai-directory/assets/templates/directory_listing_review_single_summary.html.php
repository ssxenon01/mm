<div id="<?php echo $id;?>" class="<?php echo $class;?>">
    <div class="sabai-row-fluid">
        <div class="sabai-span2 sabai-directory-listing">
<?php if ($listing = $this->Content_ParentPost($entity)):?>
<?php   if (!empty($entity->data['directory_listing_photos'])): $photo = $entity->data['directory_listing_photos'][0];?>
            <?php echo $this->File_ThumbnailLink($listing, $photo->file_image[0], array('link_entity' => true));?>
<?php   else:?>
            <img src="<?php echo $this->ImageUrl('no_image_small.png');?>" alt="" />
<?php   endif;?>
            <span><?php echo $this->Entity_Permalink($listing);?></span>
<?php   if (!empty($listing->voting_rating['']['count'])):?>
            <div class="sabai-directory-rating">
                <?php echo $this->Voting_RenderRating($listing);?>
                <span class="sabai-directory-rating-count">(<?php echo $listing->voting_rating['']['count'];?>)</span>
            </div>
<?php   endif;?>
<?php endif;?>
        </div>
        <div class="sabai-span10 sabai-directory-main">
            <div class="sabai-directory-info">
<?php if (!empty($entity->voting_helpful[0])):?>
                <div class="sabai-directory-review-helpful-count"><?php printf(__('%d of %d people found the following review helpful', 'sabai-directory'), $entity->voting_helpful[0]['sum'], $entity->voting_helpful[0]['count']);?></div>
<?php endif;?>
                <div class="sabai-directory-review-title"><?php echo $this->Content_RenderTitle($entity);?></div>
                <div class="sabai-directory-review-rating">
                    <span class="sabai-rating sabai-rating-<?php echo $entity->directory_rating[0] * 10;?>" title="<?php printf(__('%.1f out of 5 stars', 'sabai-directory'), $entity->directory_rating[0]);?>"></span>
                    <span class="sabai-directory-rating-average"><?php echo number_format($entity->directory_rating[0], 1);?></span>
                </div>
                <div class="sabai-directory-activity sabai-directory-activity-inline">
                    <?php echo $this->Content_RenderActivity($entity, array('action_label' => __('%s reviewed %s', 'sabai-directory'), 'permalink' => false, 'show_last_active' => false, 'show_last_edited' => true));?>
                </div>
            </div>
            <div class="sabai-directory-body">
                <?php echo $this->Content_RenderSummary($entity, 300);?>
            </div>
        </div>
        <div class="sabai-entity-links sabai-btn-group">
            <?php echo $this->ButtonLinks($links);?>
        </div>
    </div>
</div>