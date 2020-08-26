<?php
$exists = array_key_exists('success', get_defined_vars());
if (!$exists) {
    $success = '';
}

$exists = array_key_exists('message', get_defined_vars());
if (!$exists) {
    $message = '';
}

$exists = array_key_exists('class', get_defined_vars());
if (!$exists) {
    $class = 'admwpp_hide';
}
?>

<div id="admwpp-message-box" class="<?php echo $success; ?> <?php echo $class; ?>">
  <?php echo $message; ?>
</div>
