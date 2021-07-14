<?php
namespace ADM\WPPlugin\Api;

$locations = Search::getLocationsFilter();

global $post;
$selectedLocationTmsId = get_post_meta($post->ID, 'tms_location_id', true);
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
  <div id="admwpp-locations" class="admwpp-box-wrapper">
    <select name='tms_location_id' autocomplete="off">
      <option value=''><?php _e('Select a Location', ADMWPP_TEXT_DOMAIN); ?></option>
      <?php
        foreach ($locations as $key => $value) {
            $selected = selected($selectedLocationTmsId, $key, false);
            echo "<option value='$key' $selected>$value</option>";
        }
        ?>
    </select>
    </div>
  </div>
  <div class="clearfix"></div>
</div>
