<?php

/**
 * Alternate "loop" to display posts in blog style.
 */

global $bunyad_loop;

if (!is_object($bunyad_loop)) {
    $bunyad_loop = $wp_query;
}

if ($bunyad_loop->have_posts()):

?>
<div class="city-pulse" >
    <div class="row">
        <div class="fun-preview row">

            <div class="slides col-md-4">
                <div class="thumb"><img src="http://localhost:8888/mymenu/resources/uploads/2014/06/fruit-yoghurt-dessert-750x393.jpg"></div>
                <div class="thumb"><img src="http://localhost:8888/mymenu/resources/uploads/2014/06/fruit-yoghurt-dessert-750x393.jpg"></div>
           </div>
           <div class="content col-md-8">
               <div class="thumb pull-right col-md-6" >
                   <img src="http://localhost:8888/mymenu/resources/uploads/2014/06/fruit-yoghurt-dessert-750x393.jpg">
               </div>
               <h1 class="title">Secrets of healthy eating</h1>
               <div class="description">
                   Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vivamus porta viverra purus a faucibus. Donec nunc odio, egestas a pretium et, facilisis eget turpis. Cras id risus massa. Sed ut diam sagittis nulla facilisis lobortis. Sed viverra consectetur tincidunt. Ut molestie scelerisque sodales. Pellentesque luctus nisl lobortis, dignissim purus vel, eleifend ipsum. Nullam sollicitudin ultrices purus at fermentum. Phasellus in nunc luctus, ultricies lectus sit amet, interdum enim. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae; Integer viverra, justo eu vestibulum commodo, est leo suscipit turpis, sed placerat enim risus sed turpis. In ultrices nisl et ligula tincidunt pharetra ut id arcu.

                   Aliquam erat volutpat. Aenean ut lacus in velit facilisis vehicula. Nullam non euismod nisl. Aliquam non dui tempus, accumsan eros ac, euismod urna. In hac habitasse platea dictumst. Vivamus id ante tortor. Sed nec varius elit. Fusce semper ultrices tortor ac elementum. Quisque malesuada imperdiet dolor, eu sagittis justo cursus at. Duis condimentum neque nec sem semper, at facilisis dui iaculis. Nam id ipsum sit amet diam iaculis dapibus et eget sem. Morbi ac lorem libero. Integer pulvinar, dolor sed aliquam posuere, leo sapien euismod ante, pharetra ullamcorper massa dui sed dolor. Vivamus massa est, tincidunt faucibus pellentesque vitae, fermentum sollicitudin sem. Vivamus ipsum lectus, tincidunt ut congue a, mattis vel libero.

                   Nulla facilisi. Proin id pulvinar odio. Donec bibendum tellus dui, a vestibulum nunc porttitor non. Duis mollis, ligula a ultricies elementum, lacus ipsum pretium eros, ut vehicula risus massa eget magna. Maecenas nunc risus, ultrices eget felis nec, laoreet vestibulum nisi. Integer nulla ipsum, euismod semper erat in, blandit lobortis mi. Mauris tempor sit amet justo sed tincidunt. Maecenas tincidunt quam at imperdiet euismod. Praesent commodo, lorem ac blandit dapibus, nisl est egestas neque, ac feugiat arcu enim et elit. Donec quis rhoncus nisl. Nam metus felis, elementum pulvinar neque eget, rhoncus feugiat tortor. Nam blandit a nulla vitae consequat. Sed est orci, sollicitudin in interdum eget, tempor eu mi.

                   Ut non dictum nibh. In hac habitasse platea dictumst. Suspendisse sodales volutpat rutrum. Maecenas pellentesque ante a quam hendrerit, fermentum iaculis augue iaculis. Praesent aliquet est metus, non gravida mauris luctus at. Vestibulum tristique interdum lacus, quis feugiat turpis dapibus vitae. Vivamus auctor malesuada tempus. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae; Nulla vitae leo feugiat, laoreet felis et, placerat lorem.

                   Nam ornare, erat eget porttitor tristique, nunc ante euismod diam, eu facilisis turpis nunc in odio. Aliquam lectus tellus, faucibus eu lorem et, congue blandit nisi. Maecenas auctor ante in tincidunt blandit. Integer egestas nec dolor faucibus lobortis. Cras sagittis odio sed dictum aliquam. Mauris blandit lectus in congue fermentum. Proin eu massa sodales lacus cursus dignissim. Nunc gravida et risus accumsan molestie. Sed cursus scelerisque felis. Aliquam ullamcorper, urna id egestas ornare, mauris erat gravida purus, in pretium nulla mauris tincidunt libero. Aliquam faucibus ligula mi, eget ultrices purus porta in. Donec feugiat, arcu ac ornare vehicula, magna urna pretium diam, vel molestie magna sem porta risus.
               </div>

           </div>

        </div>

        <div class="clearfix"></div>
        <div>
            <div class="masonry-loop">

                <?php
                $counter=0;
                    while ($bunyad_loop->have_posts()): $bunyad_loop->the_post(); ?>

                        <?php if (Bunyad::posts()->meta('featured_video')): // featured video available? ?>

                            <div class="col-md-6 m-item" >
                                <div class="video-box">
                                    <iframe src="<?php echo Bunyad::posts()->meta('featured_video');?>" width="100%" height="200" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>
                                </div>
                            </div>
                        <?php else: // normal featured image ?>


                            <?php
                            $counter++;
                            if($counter%5==1):?>
                                <div class="col-md-3 m-item" >
                                    <a href="<?php the_permalink() ?>"><div class="red-box">
                                            <div class="thumb"><?php the_post_thumbnail('thumbnail', array('title' => strip_tags(get_the_title()),'class'=>'photo', 'itemprop' => 'image')); ?></div>
                                            <div class="title"><?php the_title();?></div>
                                            <div class="description" ><?php echo preg_replace("/<a.*<\/a>/", "\n", wp_trim_excerpt()); ?></div>
                                        </div></a>
                                </div>
                            <?php elseif($counter%5==2): ?>
                                <div class="col-md-3 m-item" >
                                    <a href="<?php the_permalink() ?>"><div class="thumb-box">
                                            <div class="thumb"><?php the_post_thumbnail('thumbnail', array('title' => strip_tags(get_the_title()),'class'=>'photo', 'itemprop' => 'image')); ?></div>
                                            <div class="ribbon"><div class="ribbon-stitches-top"></div><strong class="ribbon-content"><h1><?php the_title();?></h1></strong><div class="ribbon-stitches-bottom"></div></div>
                                        </div></a>
                                </div>
                            <?php elseif($counter%5==3):?>
                                <div class="col-md-6 m-item" >
                                    <a href="<?php the_permalink() ?>"><div class="yellow-box">
                                            <div class="title"><?php the_title();?></div>
                                            <div class="thumb pull-left"><?php the_post_thumbnail('thumbnail', array('title' => strip_tags(get_the_title()),'class'=>'photo', 'itemprop' => 'image')); ?></div>
                                            <div class="description" ><?php echo preg_replace("/<a.*<\/a>/", "\n", wp_trim_excerpt()); ?></div>
                                            <div class="clearfix"></div>
                                        </div></a>
                                </div>
                            <?php elseif($counter%5==4):?>
                                <div class="col-md-6 m-item" >
                                    <a href="<?php the_permalink() ?>"><div class="trans-box">
                                            <div class="thumb pull-left col-md-4"><?php the_post_thumbnail('thumbnail', array('title' => strip_tags(get_the_title()),'class'=>'photo', 'itemprop' => 'image')); ?></div>
                                            <div class="title col-md-8"><?php the_title();?></div>
                                            <div class="description col-md-8" ><?php echo preg_replace("/<a.*<\/a>/", "\n", wp_trim_excerpt()); ?></div>
                                            <div class="clearfix"></div>
                                        </div></a>
                                </div>
                            <?php else:?>
                                <div class="col-md-3 m-item" >
                                    <a href="<?php the_permalink() ?>"><div class="red-box different">
                                            <div class="thumb"><?php the_post_thumbnail('thumbnail', array('title' => strip_tags(get_the_title()),'class'=>'photo', 'itemprop' => 'image')); ?></div>
                                            <div class="title"><?php the_title();?></div>
                                            <div class="description" ><?php echo preg_replace("/<a.*<\/a>/", "\n", wp_trim_excerpt()); ?></div>
                                        </div></a>
                                </div>
                            <?php endif;?>
                    <?php endif;?>
                <?php endwhile; ?>




            </div>
        </div>
    </div>
</div>
<?php else: ?>

    <article id="post-0" class="page no-results not-found">
        <div class="post-content">
            <h1><?php _e( 'Nothing Found!', 'bunyad' ); ?></h1>
            <p><?php _e('Apologies, but no results were found for the requested archive. Perhaps searching will help find a related post.', 'bunyad'); ?></p>
        </div><!-- .entry-content -->
    </article><!-- #post-0 -->

<?php endif; ?>
