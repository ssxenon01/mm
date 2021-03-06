<div id="<?php echo $id; ?>" class="<?php echo $class; ?> sabai-clearfix" itemscope itemtype="http://schema.org/LocalBusiness">
    <meta itemprop="name" content="<?php Sabai::_h($entity->getTitle()); ?>"/>
    <meta itemprop="url" content="<?php echo $this->Entity_PermalinkUrl($entity); ?>"/>
    <div class="sabai-row-fluid">
        <div class="sabai-span12">
            <h3 class="sabai-entity-name"><?php echo $entity; ?></h3>
            <div class="sabai-header-right">
                <div class="sabai-btn-group sabai-pull-right"><a href="<?php echo $this->Entity_PermalinkUrl($entity); ?>/reviews/add" class="sabai-btn sabai-btn-small sabai-btn-danger"><i class="sabai-icon-pencil"></i> Сэтгэгдэл бичих</a></div>
            </div>
            <?php if (!empty($entity->voting_rating['']['count'])): ?>
                <div class="sabai-directory-rating" itemprop="aggregateRating" itemscope itemtype="http://schema.org/AggregateRating">
                    <?php echo $this->Voting_RenderRating($entity); ?>
                    <span class="sabai-directory-rating-average" itemprop="ratingValue"><?php echo number_format($entity->voting_rating['']['average'], 2); ?></span>
                    <span
                        class="sabai-directory-rating-count"><?php printf(_n('(<span itemprop="reviewCount">%d</span> review)', '(<span itemprop="reviewCount">%d</span> reviews)', $entity->voting_rating['']['count'], 'sabai-directory'), $entity->voting_rating['']['count']); ?></span>
                </div>
            <?php endif; ?>
            <?php if ($entity->directory_category): ?>
                <div class="sabai-directory-taxonomy">
                    <?php foreach ($entity->directory_category as $category): ?>
                        <?php echo $this->Entity_Permalink($category, array('bullet-icon' => 'folder-open')); ?>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            <?php if ($labels = $this->Content_RenderLabels($entity)): ?>
                <div class="sabai-directory-labels"><?php echo $labels; ?></div>
            <?php endif; ?>
        </div>
        <div class="sabai-span8" >
            <div class="sabai-directory-images">
                <?php if (!empty($entity->data['directory_photos'])): $photo = $entity->data['directory_photos'][0]; ?>
                    <a href="<?php echo $this->Entity_Url($entity, '/photos', array('photo_id' => $photo->getId())); ?>" title="<?php Sabai::_h($photo->getTitle()); ?>">
                        <img src="<?php echo $this->Directory_PhotoUrl($photo, 'medium'); ?>" alt="" itemprop="image"/>
                    </a>
                    <?php $i = 0;
                    while ($photos = array_slice($entity->data['directory_photos'], $i * 4 + 1, 4)): ?>
                        <div class="sabai-directory-thumbnails sabai-row-fluid">
                            <?php foreach ($photos as $photo): ?>
                                <div class="sabai-span3">
                                    <a href="<?php echo $this->Entity_Url($entity, '/photos', array('photo_id' => $photo->getId())); ?>"
                                       title="<?php Sabai::_h($photo->getTitle()); ?>"><img src="<?php echo $this->Directory_PhotoUrl($photo, 'thumbnail'); ?>" alt=""/></a>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <?php ++$i; endwhile; ?>
                <?php else: ?>
                    <img src="<?php echo $this->NoImageUrl(); ?>" alt=""/>
                <?php endif; ?>
            </div>
            <div class="sabai-directory-main">
                <div class="sabai-directory-info sabai-clearfix">
                    <?php if (!empty($entity->directory_location[0]['address'])): ?>
                        <div class="sabai-directory-address" itemprop="address" itemscope
                             itemtype="http://schema.org/PostalAddress"><?php Sabai::_h($entity->directory_location[0]['address']); ?></div>
                    <?php endif; ?>
                    <?php if (!empty($entity->directory_contact[0]['has_phone'])): ?>
                        <div class="sabai-directory-phone">
                            <?php if (!empty($entity->directory_contact[0]['phone'])): ?>
                                <span class="sabai-directory-tel" itemprop="telephone"><a
                                        href="tel:<?php Sabai::_h($entity->directory_contact[0]['phone']); ?>"><?php Sabai::_h($entity->directory_contact[0]['phone']); ?></a></span>
                            <?php endif; ?>
                            <?php if (!empty($entity->directory_contact[0]['mobile'])): ?>
                                <span class="sabai-directory-mobile" itemprop="telephone"><span class="sabai-directory-separator"> / </span><a
                                        href="tel:<?php Sabai::_h($entity->directory_contact[0]['mobile']); ?>"><?php printf(__('%s (Mobile)', 'sabai-directory'), Sabai::_h($entity->directory_contact[0]['mobile'])); ?></a></span>
                            <?php endif; ?>
                            <?php if (!empty($entity->directory_contact[0]['fax'])): ?>
                                <span class="sabai-directory-fax" itemprop="faxnumber"><span
                                        class="sabai-directory-separator"> / </span><?php printf(__('%s (Fax)', 'sabai-directory'), Sabai::_h($entity->directory_contact[0]['fax'])); ?></span>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($entity->directory_contact[0]['website'])): ?>
                        <div class="sabai-directory-website"><a href="<?php Sabai::_h($entity->directory_contact[0]['website']); ?>" target="_blank"
                                                                rel="nofollow external"><?php Sabai::_h(mb_strimwidth($entity->directory_contact[0]['website'], 0, 30, '...')); ?></a>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($entity->directory_contact[0]['email'])): ?>
                        <div class="sabai-directory-email"><a href="mailto:<?php Sabai::_h($entity->directory_contact[0]['email']); ?>" target="_blank"
                                                              rel="nofollow external"><?php Sabai::_h(mb_strimwidth($entity->directory_contact[0]['email'], 0, 30, '...')); ?></a></div>
                    <?php endif; ?>
                    <?php if (!empty($entity->directory_social[0])): ?>
                        <div class="sabai-directory-social">
                            <?php if ($entity->directory_social[0]['twitter']): ?>
                                <a class="sabai-directory-social-twitter" target="_blank" rel="nofollow external"
                                   href="http://twitter.com/<?php Sabai::_h($entity->directory_social[0]['twitter']); ?>"><i class="sabai-icon-twitter-sign"></i></a>
                            <?php endif; ?>
                            <?php if ($entity->directory_social[0]['facebook']): ?>
                                <a class="sabai-directory-social-facebook" target="_blank" rel="nofollow external" href="<?php Sabai::_h($entity->directory_social[0]['facebook']); ?>"><i
                                        class="sabai-icon-facebook-sign"></i></a>
                            <?php endif; ?>
                            <?php if ($entity->directory_social[0]['googleplus']): ?>
                                <a class="sabai-directory-social-googleplus" target="_blank" rel="nofollow external"
                                   href="<?php Sabai::_h($entity->directory_social[0]['googleplus']); ?>"><i class="sabai-icon-google-plus-sign"></i></a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
                <?php if ($listing_body = $this->Content_RenderBody($entity)): ?>
                    <div class="sabai-directory-body" itemprop="description">
                        <?php echo $listing_body; ?>
                    </div>
                <?php endif; ?>
                <div class="sabai-directory-custom-fields">
                    <?php $this->renderTemplate('directory_custom_fields', array('entity' => $entity)); ?>
                </div>
            </div>
        </div>
        <div class="sabai-span4 sabai-directory-main">
            This is left side
        </div>
    </div>
    <div class="sabai-entity-links sabai-btn-group">
        <?php echo $this->ButtonLinks($links); ?>

    </div>

</div>
