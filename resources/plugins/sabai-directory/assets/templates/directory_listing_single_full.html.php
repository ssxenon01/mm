<div id="<?php echo $id;?>" class="<?php echo $class;?> sabai-clearfix" itemscope itemtype="http://schema.org/LocalBusiness">
    <meta itemprop="name" content="<?php Sabai::_h($entity->getTitle());?>" />
    <meta itemprop="url" content="<?php echo $this->Entity_PermalinkUrl($entity);?>" />
<?php if ($labels = $this->Content_RenderLabels($entity)):?>
    <div class="sabai-directory-labels"><?php echo $labels;?></div>
<?php endif;?>
    <div class="sabai-row-fluid">
        <div class="sabai-span12">
            <h1 style="font-size: 22px; text-transform: uppercase;"><?php echo $entity->getTitle();?></h1>
            <?php if (!empty($entity->voting_rating['']['count'])):?>
                <div class="sabai-directory-rating" itemprop="aggregateRating" itemscope itemtype="http://schema.org/AggregateRating">
                    <?php echo $this->Voting_RenderRating($entity);?>
                    <span class="sabai-directory-rating-average" itemprop="ratingValue"><?php echo number_format($entity->voting_rating['']['average'], 2);?></span>
                    <span class="sabai-directory-rating-count"><?php printf(_n('(<span itemprop="reviewCount">%d</span> review)', '(<span itemprop="reviewCount">%d</span> reviews)', $entity->voting_rating['']['count'], 'sabai-directory'), $entity->voting_rating['']['count']);?></span>
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
                    <div class="sabai-directory-address" itemprop="address" itemscope itemtype="http://schema.org/PostalAddress"><?php Sabai::_h($entity->directory_location[0]['address']);?></div>
                <?php endif;?>
                <?php if (!empty($entity->directory_contact[0]['has_phone'])):?>
                    <div class="sabai-directory-phone">
                        <?php   if (!empty($entity->directory_contact[0]['phone'])):?>
                            <span class="sabai-directory-tel" itemprop="telephone"><a href="tel:<?php Sabai::_h($entity->directory_contact[0]['phone']);?>"><?php Sabai::_h($entity->directory_contact[0]['phone']);?></a></span>
                        <?php   endif;?>
                        <?php   if (!empty($entity->directory_contact[0]['mobile'])):?>
                            <span class="sabai-directory-mobile" itemprop="telephone"><span class="sabai-directory-separator"> / </span><a href="tel:<?php Sabai::_h($entity->directory_contact[0]['mobile']);?>"><?php printf(__('%s (Mobile)', 'sabai-directory'), Sabai::_h($entity->directory_contact[0]['mobile']));?></a></span>
                        <?php   endif;?>
                        <?php   if (!empty($entity->directory_contact[0]['fax'])):?>
                            <span class="sabai-directory-fax" itemprop="faxnumber"><span class="sabai-directory-separator"> / </span><?php printf(__('%s (Fax)', 'sabai-directory'), Sabai::_h($entity->directory_contact[0]['fax']));?></span>
                        <?php   endif;?>
                    </div>
                <?php endif;?>
                <?php if (!empty($entity->directory_contact[0]['website'])):?>
                    <div class="sabai-directory-website"><a href="<?php Sabai::_h($entity->directory_contact[0]['website']);?>" target="_blank" rel="nofollow external"><?php Sabai::_h(mb_strimwidth($entity->directory_contact[0]['website'], 0, 30, '...'));?></a></div>
                <?php endif;?>
                <?php if (!empty($entity->directory_contact[0]['email'])):?>
                    <div class="sabai-directory-email"><a href="mailto:<?php Sabai::_h($entity->directory_contact[0]['email']);?>" target="_blank" rel="nofollow external"><?php Sabai::_h(mb_strimwidth($entity->directory_contact[0]['email'], 0, 30, '...'));?></a></div>
                <?php endif;?>

                <?php if (!empty($entity->directory_social[0])):?>
                    <div class="sabai-directory-social">
                        <?php   if ($entity->directory_social[0]['twitter']):?>
                            <a class="sabai-directory-social-twitter" target="_blank" rel="nofollow external" href="http://twitter.com/<?php Sabai::_h($entity->directory_social[0]['twitter']);?>"><i class="sabai-icon-twitter-sign"></i></a>
                        <?php   endif;?>
                        <?php   if ($entity->directory_social[0]['facebook']):?>
                            <a class="sabai-directory-social-facebook" target="_blank" rel="nofollow external" href="<?php Sabai::_h($entity->directory_social[0]['facebook']);?>"><i class="sabai-icon-facebook-sign"></i></a>
                        <?php   endif;?>
                        <?php   if ($entity->directory_social[0]['googleplus']):?>
                            <a class="sabai-directory-social-googleplus" target="_blank" rel="nofollow external" href="<?php Sabai::_h($entity->directory_social[0]['googleplus']);?>"><i class="sabai-icon-google-plus-sign"></i></a>
                        <?php   endif;?>
                    </div>
                <?php endif;?>
        </div>
        <div class="sabai-span6 sabai-directory-images" style="margin-left: 0 !important; padding-right: 10px;">
<?php if (!empty($entity->data['directory_photos'])): $photo = $entity->data['directory_photos'][0];?>
            <a href="<?php echo $this->Entity_Url($entity, '/photos', array('photo_id' => $photo->getId()));?>" title="<?php Sabai::_h($photo->getTitle());?>"><img width="100%"  src="<?php echo $this->Directory_PhotoUrl($photo, 'large');?>" alt="" itemprop="image" /></a>
<?php   $i = 0; while ($photos = array_slice($entity->data['directory_photos'], $i * 4 + 1, 4)):?>
            <div class="sabai-directory-thumbnails sabai-row-fluid">
<?php     foreach ($photos as $photo):?>
                <div class="sabai-span3">
                    <a href="<?php echo $this->Entity_Url($entity, '/photos', array('photo_id' => $photo->getId()));?>" title="<?php Sabai::_h($photo->getTitle());?>"><img src="<?php echo $this->Directory_PhotoUrl($photo, 'thumbnail');?>" alt="" /></a>
                </div>
<?php     endforeach;?>
            </div>
<?php   ++$i; endwhile;?>
<?php else:?>
            <img src="<?php echo $this->NoImageUrl();?>" alt="" />
<?php endif;?>
        </div>
        <div class="sabai-span6" style="margin-top: -115px;">
<?php if ($listing_body = $this->Content_RenderBody($entity)):?>
            <div class="sabai-directory-body" itemprop="description">
                <?php echo $listing_body;?>
            </div>
<?php endif;?>
            <!--map-->
            <?php
            ob_start();
            $this->renderTemplate($entity->getBundleType() . '_single_infobox', array('entity' => $entity));
            $content = ob_get_clean();
            ?>
            <script type="text/javascript">
                google.load("maps", "3", {other_params:"sensor=false&libraries=places&language=<?php echo $this->GoogleMaps_Language();?>", callback:function () {
                    $LAB.script("<?php echo $this->JsUrl('sabai-googlemaps-directionmap.js', 'sabai-directory');?>")
                        .script("<?php echo $this->JsUrl('sabai-googlemaps-autocomplete.js', 'sabai-directory');?>").wait(function(){
                            SABAI.GoogleMaps.directionMap(
                                "#sabai-directory-map",
                                <?php echo ($lat = $entity->getSingleFieldValue('directory_location', 'lat')) ? $lat : 'null';?>,
                                <?php echo ($lng = $entity->getSingleFieldValue('directory_location', 'lng')) ? $lng : 'null';?>,
                                "#sabai-directory-map-direction-search .sabai-directory-search-btn button",
                                "#sabai-directory-map-direction-search .sabai-directory-direction-location input",
                                "#sabai-directory-map-direction-search .sabai-directory-direction-mode select",
                                <?php echo json_encode($content);?>,
                                '#sabai-directory-map-direction-panel',
                                <?php echo json_encode($this->Config('Directory', 'map', 'options') + array('icon' => $this->Directory_ListingMapMarkerUrl($entity), 'zoom' => isset($map_settings['listing_default_zoom']) ? intval($map_settings['listing_default_zoom']) : 15, 'styles' => $map_settings['style'] ? $this->GoogleMaps_Style($map_settings['style']) : null));?>
                            );
                            SABAI.GoogleMaps.autocomplete(".sabai-directory-direction-location input", {componentRestrictions: {<?php if ($country):?>country: "<?php echo $country;?>"<?php endif;?>}});
                        });
                }});
            </script>
            <div id="sabai-directory-map" class="sabai-googlemaps-map" style="height:400px;"></div>
            <div id="sabai-directory-map-direction-panel" style="height:200px; overflow:scroll; display:none;"></div>

            <!--end map-->
            </div>
            <div class="clearfix"></div>
            <div class="sabai-span4" style="margin-left: 0 !important;">
                <h1>Цагийн хуваарь</h1>
                <div>
                    <?php foreach($entity->getFieldValues()['field_hours'] as $hours): ?>
                        <p><?php echo $hours;?></p>
                    <?php endforeach; ?>


                    <?php $this->renderTemplate('directory_custom_fields', array('entity' => $entity));?>
                </div>

            </div>
            <div class="sabai-span8">
                <?php $tenant_feature = $entity->getFieldValues()['field_tenant_feature']; ?>
                <?php $field_what = $entity->getFieldValues()['field_what']; ?>
                <h1>Нэмэлт мэдээлэл</h1>
                <table class="feature table table-bordered table-striped" style="font-size: 12px;">
                    <tr>
                        <td>Wi-Fi: <?php echo in_array('wifi',$tenant_feature)?'YES':'NO' ?></td>
                        <td>Ширээ захиалгатай: <?php echo in_array('tableorder',$tenant_feature)?'YES':'NO' ?></td>
                    </tr>
                    <tr>
                        <td>Картаар гүйлгээ хийдэг: <?php echo in_array('creditcard',$tenant_feature)?'YES':'NO' ?></td>
                        <td>Хүргэлтийн үйлчилгээ: <?php echo in_array('delivery',$field_what)?'YES':'NO' ?></td>
                    </tr>
                    <tr>
                        <td>VIP өрөө: <?php echo in_array('viproom',$tenant_feature)?'YES':'NO' ?></td>
                        <td>Хүүхэд үйлчлүүлэхэд тохиромжтой: <?php echo in_array('withchild',$tenant_feature)?'YES':'NO' ?></td>
                    </tr>
                    <tr>
                        <td>Зогсоол: <?php echo in_array('parking',$tenant_feature)?'YES':'NO' ?></td>
                        <td>Олуулаа очиход тохиромжтой: <?php echo in_array('biggroup',$tenant_feature)?'YES':'NO' ?></td>
                    </tr>
                    <tr>
                        <td>Нэг дор хүлээн авах: <?php echo $entity->getFieldValues()['field_limit'][0] ?></td>
                        <td>Катеринг үйлчилгээ: <?php echo in_array('catering',$tenant_feature)?'YES':'NO' ?></td>
                    </tr>
                    <tr>
                        <td>Алкохол: <?php echo in_array('drinks',$field_what)?'YES':'NO' ?></td>
                        <td>Дундаж үнэ: <?php echo '₮'.$entity->getFieldValues()['field_price'][0] ?></td>
                    </tr>
                    <tr>
                        <td>Амьд тоглолт: <?php echo in_array('livemusic',$tenant_feature)?'YES':'NO' ?></td>
                        <td>Орчин: </td>
                    </tr>
                    <tr>
                        <td>Гадаа суудал: <?php echo in_array('outdoors',$tenant_feature)?'YES':'NO' ?></td>
                        <td>Цагаан хоолтой: <?php echo in_array('veterian',$tenant_feature)?'YES':'NO' ?> </td>
                    </tr>
                </table>
            </div>
            <!--<div class="sabai-directory-custom-fields">
                <?php /*$this->renderTemplate('directory_custom_fields', array('entity' => $entity));*/?>
            </div>-->
        </div>
    </div>
    <div class="sabai-entity-links sabai-btn-group">
        <?php echo $this->ButtonLinks($links);?>
    </div>
</div>
