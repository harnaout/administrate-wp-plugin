<?php
namespace ADM\WPPlugin\Api;

global $post;
$selectedPartnerTmsId = get_post_meta($post->ID, 'tms_partner_id', true);
$selectedPartnerTmsName = get_post_meta($post->ID, 'tms_partner_name', true);
?>
<div class="admwpp-metabox">
  <?php if (!empty($metabox['args']['info'])) : ?>
    <fieldset class="admwpp-metabox-info-fieldset">
      <div class="field-group admwpp-metabox-info">
        <?php echo $metabox['args']['info']; ?>
      </div>
    </fieldset>
  <?php endif; ?>
  <div class="clearfix"></div>
  <div id="admwpp-accounts" class="admwpp-box-wrapper">
    <input placeholder="Search Partners" class='admwpp-autocomplete' id="admwpp-partners-search" autocomplete="off">
    <input type="hidden" id='admwpp-partner-id' name="tms_partner_id" value="<?php echo $selectedPartnerTmsId; ?>">
    <input type="hidden" id='admwpp-partner-name' name="tms_partner_name" value="<?php echo $selectedPartnerTmsName; ?>">
    <label id='admwpp-selected-partner-name'><?php echo $selectedPartnerTmsName; ?></label>
    </div>
  </div>
  <div class="clearfix"></div>
</div>
