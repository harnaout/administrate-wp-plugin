<div class="row admwpp-date-filter">
    <div class='col-xs-12 col-sm-6'>
        <input type="text" placeholder="<?php _e('From date', 'admwpp'); ?>" class="admwpp-date admwpp-from-date" id="from" name="from"
    <?php echo (isset($_GET['from']) && !empty($_GET['from']))?'value="' . $_GET['from'] . '"':''; ?> autocomplete="off">
    </div>
    <div class='col-xs-12 col-sm-6'>
        <input type="text" placeholder="<?php _e('To date', 'admwpp'); ?>" class="admwpp-date admwpp-to-date" id="to" name="to"
        <?php echo (isset($_GET['to']) && !empty($_GET['to']))?'value="' . $_GET['to'] . '"':''; ?> autocomplete="off">
    </div>
</div>
