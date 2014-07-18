
<div class="swiper-slide">
    <?php if (!empty($entity->data['directory_photos'][0])): $photo = $entity->data['directory_photos'][0];?>
        <img src="<?php echo $this->Directory_PhotoUrl($photo, 'large');?>" alt="" />
    <?php else:?>
        <img src="<?php echo $this->NoImageUrl(true);?>" alt="" />
    <?php endif;?>
    <a href="http://localhost:8888/mymenu/vip-beer-pong/"><div class="title">VIP beer pong <span class="published">4 сар 23, 2014</span></div></a>
</div>



