<?php if (isset($success)):?>
<?php   foreach ((array)$success as $_success):?>
<div class="sabai-success" style="margin-bottom:10px;"><?php echo $_success;?></div>
<?php   endforeach;?>
<?php endif;?>
<?php if (isset($error)):?>
<?php   foreach ((array)$error as $_error):?>
<div class="sabai-error" style="margin-bottom:10px;"><?php echo $_error;?></div>
<?php   endforeach;?>
<?php endif;?>
<?php if (isset($warning)):?>
<?php   foreach ((array)$warning as $_warning):?>
<div class="sabai-warning" style="margin-bottom:10px;"><?php echo $_warning;?></div>
<?php   endforeach;?>
<?php endif;?>
<?php if (isset($info)):?>
<?php   foreach ((array)$info as $_info):?>
<div class="sabai-info" style="margin-bottom:10px;"><?php echo $_info;?></div>
<?php   endforeach;?>
<?php endif;?>
<?php if (isset($notice)):?>
<?php   foreach ((array)$notice as $_notice):?>
<div style="margin-bottom:10px;"><?php echo $_notice;?></div>
<?php   endforeach;?>
<?php endif;?>
