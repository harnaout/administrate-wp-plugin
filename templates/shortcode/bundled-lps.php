<?php
global $post;
$postId = 0;
if (isset($post)) {
    $postId = $post->ID;
}
if (isset($_GET['post_id'])) {
    $postId = $_GET['post_id'];
}
?>
<div id='amwpp-bundled-lps' class="admwpp-bundled-lps row justify-content-center">
    <?php
    if ($bundledLps['bundledLps']) {
        ?>
        <div class="col-auto table-responsive">
            <table class="table table-striped table-hover">
                <thead class="thead-light">
                   <tr>
                     <th scope="col"><?php echo __("Bundle", "admwpp"); ?></th>
                     <th scope="col"><?php echo __("Objectives", "admwpp"); ?></th>
                     <th scope="col"><?php echo __("Language", "admwpp"); ?></th>
                     <th scope="col"><?php echo __("Date", "admwpp"); ?></th>
                     <th scope="col"><?php echo __("Time", "admwpp"); ?></th>
                     <th scope="col"><?php echo __("Price*", "admwpp"); ?></th>
                     <th scope="col"></th>
                   </tr>
                </thead>
                <tbody>
                    <?php include('bundled-lps-rows.php'); ?>
                </tbody>
            </table>
            <?php
            if ($bundledLps['hasNextPage']) {
                $next = $bundledLps['currentPage'] + 1;
                ?>
                <div class="col-md-12 text-center">
                    <a class='admwpp-button admwpp-bundled-loadmore-btn btn btn-lg btn-secondary' data-page='<?php echo $next; ?>' data-per_page='<?php echo $per_page; ?>' data-post_id='<?php echo $post_id; ?>' data-container='amwpp-bundled-lps'><?php _e('Load More', 'admwpp'); ?> <div class='admwpp-loader fa-3x text-center'><i class='fas fa-circle-notch fa-spin'></i></div></a>
                </div>
                <?php
            } ?>
        </div>
        <?php
    } else {
        echo __("No Bundles yet to be listed.", "admwpp");
    }
    ?>
</div>
