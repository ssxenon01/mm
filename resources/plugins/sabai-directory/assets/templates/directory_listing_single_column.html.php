<div id="<?php echo $id;?>" class="<?php echo $class;?> sabai-directory-listing-column sabai-span<?php echo $span;?> sabai-clearfix">
    <div class="sabai-directory-images">
<?php if (!empty($entity->data['directory_photos'][0])): $photo = $entity->data['directory_photos'][0];?>
        <?php echo $this->File_ThumbnailLink($entity, $photo->file_image[0], array('link_entity' => true, 'title' => $entity->getTitle()));?>
<?php else:?>
        <img src="<?php echo $this->NoImageUrl(true);?>" alt="" />
<?php endif;?>
    </div>
    <div class="sabai-directory-title">
        <?php echo $this->Content_RenderTitle($entity);?>
    </div>
<?php if (!empty($entity->voting_rating['']['count'])):?>
    <div class="sabai-directory-rating">
        <?php echo $this->Voting_RenderRating($entity);?>
        <span class="sabai-directory-rating-average"><?php echo number_format($entity->voting_rating['']['average'], 2);?></span>
        <span class="sabai-directory-rating-count"><?php printf(__('(%d)', 'sabai-directory'), $entity->voting_rating['']['count']);?></span>
    </div>
<?php endif;?>
<?php if (!empty($links)):?>
    <div class="sabai-entity-links sabai-btn-group"><?php echo $this->ButtonLinks($links);?></div>
<?php endif;?>
</div>
