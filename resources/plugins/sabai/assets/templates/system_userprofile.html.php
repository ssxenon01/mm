<div class="sabai-system-user-info">
    <div class="sabai-system-user-avatar">
        <?php echo $this->UserIdentityThumbnailMedium($identity);?>
    </div>
    <div class="sabai-system-user-contact">
        <h3><?php Sabai::_h($identity->name);?></h3>
        <ul>
<?php if ($identity->url):?>
            <li><a rel="nofollow" href="<?php Sabai::_h($identity->url);?>"><?php Sabai::_h($identity->url);?></a></li>
<?php endif;?>
        </ul>
    </div>
</div>
<?php if ($profile):?>
<div class="sabai-system-user-profile"><?php echo $profile;?></div>
<?php endif;?>
<?php if (!empty($activities)):?>
<div class="sabai-system-user-activities">
<?php   if (count($activities) > 1):?>
<?php     foreach ($activities as $activity):?>
    <h4><?php Sabai::_h($activity['title']);?></h4>
    <ul class="sabai-system-user-activity sabai-clearfix">
<?php       foreach ($activity['stats'] as $stat):?>
<?php         if (isset($stat['url'])):?>
        <li><a href="<?php echo $stat['url'];?>"><?php echo $stat['formatted'];?></a></li>
<?php         else:?>
        <li><span><?php echo $stat['formatted'];?></span></li>
<?php         endif;?>
<?php       endforeach;?>
    </ul>
<?php     endforeach;?>
<?php   else: $activity = array_shift($activities);?>
    <ul class="sabai-system-user-activity sabai-clearfix">
<?php     foreach ($activity['stats'] as $stat):?>
<?php       if (isset($stat['url'])):?>
        <li><a href="<?php echo $stat['url'];?>"><?php echo $stat['formatted'];?></a></li>
<?php       else:?>
        <li><span><?php echo $stat['formatted'];?></span></li>
<?php       endif;?>
<?php     endforeach;?>
    </ul>
<?php   endif;?>
</div>
<?php endif;?>
