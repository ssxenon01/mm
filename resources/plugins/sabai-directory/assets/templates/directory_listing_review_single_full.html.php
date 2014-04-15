<div id="<?php echo $id;?>" class="<?php echo $class;?> sabai-clearfix" itemprop="review" itemscope itemtype="http://schema.org/Review">
    <span itemprop="itemReviewed" itemscope itemtype="http://schema.org/Thing">
        <meta itemprop="name" content="<?php Sabai::_h($parent_entity->getTitle());?>" />
        <meta itemprop="url" content="<?php echo $this->Entity_PermalinkUrl($parent_entity);?>" />
    </span>
    <meta itemprop="datePublished" content="<?php echo date('Y-m-d', $entity->getTimestamp());?>" />
    <meta itemprop="author" content="<?php Sabai::_h($entity->getAuthor()->name);?>" />
    <div class="sabai-directory-info">
<?php if (!empty($entity->voting_helpful[0])):?>
        <div class="sabai-directory-review-helpful-count"><?php printf(__('%d of %d people found the following review helpful', 'sabai-directory'), $entity->voting_helpful[0]['sum'], $entity->voting_helpful[0]['count']);?></div>
<?php endif;?>
        <div class="sabai-directory-review-title" itemprop="name"><?php echo $this->Content_RenderTitle($entity, false);?></div>
        <div class="sabai-directory-review-rating" itemprop="reviewRating" itemscope itemtype="http://schema.org/Rating">
            <meta itemprop="worstRating" content="0" />
            <meta itemprop="bestRating" content="5" />
            <span class="sabai-rating sabai-rating-<?php echo $entity->directory_rating[0] * 10;?>" title="<?php printf(__('%.1f out of 5 stars', 'sabai-directory'), $entity->directory_rating[0]);?>"></span>
            <span class="sabai-directory-rating-average" itemprop="ratingValue"><?php echo number_format($entity->directory_rating[0], 1);?></span>
        </div>
        <div class="sabai-directory-activity sabai-directory-activity-inline">
            <?php echo $this->Content_RenderActivity($entity, array('action_label' => __('%s reviewed %s', 'sabai-directory'), 'permalink' => true, 'show_last_active' => false, 'show_last_edited' => true));?>
        </div>
    </div>
<?php if (!empty($entity->data['directory_photos'])): $i = 0;?>
    <div class="sabai-directory-review-photos">
<?php   while ($photos = array_slice($entity->data['directory_photos'], $i * 6, 6)):?>
        <div class="sabai-row-fluid">
<?php     foreach ($photos as $photo):?>
            <div class="sabai-span2 sabai-directory-thumbnail">
                <a href="<?php echo $this->Entity_Url($parent_entity, '/photos', array('photo_id' => $photo->getId()));?>" title="<?php Sabai::_h($photo->getTitle());?>"><img src="<?php echo $this->Directory_PhotoUrl($photo, 'thumbnail');?>" alt="" /></a>
            </div>
<?php     endforeach; ++$i;?>
        </div>
<?php   endwhile;?>
    </div>
<?php endif;?>
    <div class="sabai-directory-body" itemprop="description">
        <?php echo $this->Content_RenderBody($entity);?>
    </div>
    <div class="sabai-directory-custom-fields">
        <?php $this->renderTemplate(array('directory_listing_review_custom_fields', 'directory_custom_fields'), array('entity' => $entity));?>
    </div>
    <div class="sabai-directory-review-helpful-yesno">
        <?php echo $this->Voting_RenderYesno($entity,'.sabai-directory-review-helpful-yesno', array('format' => __('<span>Was this review helpful to you?</span> %s %s', 'sabai-directory')));?>
    </div>
    <div class="sabai-entity-links sabai-btn-group">
        <?php echo $this->ButtonLinks($links);?>
    </div>
    <div class="sabai-directory-comments" id="<?php echo $id;?>-comments">
        <?php echo $this->Comment_RenderComments($entity, $id . '-comments');?>
    </div>
</div>