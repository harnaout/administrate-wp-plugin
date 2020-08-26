<?php
namespace ADM\WPPlugin;

use ADM\WPPlugin\Main as Main;

// Flash Message
$flash = Main::instance()->getFlash();
$flash->displayMessage();

$submit_text = 'Save Settings';

if ('admwpp_account_settings' == $tab) :
    if (Main::active()) :
        $submit_text =  'Re-Activate';
    else :
        $submit_text =  'Activate';
    endif;
endif;
?>

<div class="wrap admwpp_settings">
  <h2><?php echo __('Administrate Settings', ADMWPP_TEXT_DOMAIN); ?></h2>

  <?php if (!empty($_GET['settings-updated']) && $_GET['settings-updated'] == 'true') { ?>
    <div class="updated fade">
      <p><strong>
        <?php
        echo __("Administrate plugin " . $this->plugin_settings_tabs[$tab] . " settings have been successfully saved.", ADMWPP_TEXT_DOMAIN);
        ?>
      </strong></p>
    </div>
  <?php } ?>

  <?php if (!empty($_GET['settings-updated']) && ($_GET['settings-updated'] == 'false' || $_GET['settings-updated'] == 'error')) { ?>
    <div class="updated error fade">
      <p><strong>
        <?php echo __('Oops! Something went wrong while saving. Please try again.'); ?>
      </strong></p>
    </div>
  <?php } ?>

  <?php $this->pluginOptionsTabs(); ?>
  <div class="admwpp-settings-tabs-content <?php echo $tab; ?>">
    <div class="admwpp-settings-title">
      <?php
        echo $this->pluginOptionsIcons($tab);
        echo __($this->plugin_settings_tabs[$tab] . ' Settings', ADMWPP_TEXT_DOMAIN);
        ?>
      <a href="<?php echo ADMWPP_WEBSITE; ?>" title="Administrate" target="_blank"><span class="admwpp-bookwitty-icon"></span></a>
    </div>

    <?php if ('admwpp_account_settings' == $tab) : ?>
      <form class="admwpp_settings_form" id='admwpp-activation' action="<?php echo ADMWPP_URL_ROUTES; ?>" method="post">
      <input type="hidden" value="oauth/authorize" name="_uri">
    <?php else : ?>
      <form class="admwpp_settings_form" id='admwpp-settings' method="post" action="options.php">
    <?php endif; ?>
        <div class="admwpp_actions">
        <?php if ('admwpp_account_settings' != $tab) : ?>
            <?php
            submit_button(
                __($submit_text, ADMWPP_TEXT_DOMAIN),
                'submit admwpp-settings-button',
                'submit',
                false,
                null
            );
            ?>
            <a href="<?php echo ADMWPP_URL_ROUTES; ?>?_uri=settings/<?php echo $tab; ?>/reset" data-method="put" class='button delete admwpp-settings-button admwpp_reset_settings' data-confirm="<?php echo __('Are you sure you want to reset the settings?', ADMWPP_TEXT_DOMAIN); ?>">
              <i class="fa fa-reply"></i>
              <?php echo __('Reset Settings', ADMWPP_TEXT_DOMAIN); ?>
              </a>
        <?php endif; ?>
        </div>
        <?php wp_nonce_field('update-options'); ?>
        <?php settings_fields($tab); ?>
        <?php do_settings_sections($tab); ?>
        <div class="admwpp_actions">
          <?php
            submit_button(
                __($submit_text, ADMWPP_TEXT_DOMAIN),
                'submit admwpp-settings-button'
            );
            ?>
        </div>
    </form>
  </div>
</div>
