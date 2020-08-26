<div class="updated error admwpp-activate">
  <?php if ($hook_suffix == 'plugins.php') : ?>
    <a href="admin.php?page=admwpp-settings" class='button'>
      <?php echo __('Activate Your Administrate Plugin'); ?>
    </a>
    <div class="admwpp-description">
      <strong>
        <?php echo __('Almost done'); ?>
      </strong>
      &nbsp;-&nbsp;
      <?php echo __('Go to the Administrate plugin settings page to activate the plugin'); ?>
    </div>
  <?php else : ?>
    <div class="admwpp-description">
      <?php echo __('Use your Administrate account APP ID and Secret to activate the Plugin'); ?>.
    </div>
  <?php endif; ?>
  <div class='clearfix'></div>
</div>
