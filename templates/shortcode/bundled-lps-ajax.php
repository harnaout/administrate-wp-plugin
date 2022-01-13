<?php
global $post;
$post_id = $post->ID;
?>
<div id='admwpp-bundled-lps-ajax' class='admwpp-bundled-lps-ajax' data-page=1 data-per_page='<?php echo $per_page; ?>' data-post_id='<?php echo $post_id; ?>' data-container='admwpp-bundled-lps-ajax' data-order_by='name' data-order_direction='asc'>
    <div class='fa-3x text-center'>
        <i class='fas fa-circle-notch fa-spin'></i>
    </div>
</div>
