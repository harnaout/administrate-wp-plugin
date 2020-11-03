<?php
// UI for "Minion" Post Type details custom fields
global $post;
//$course = new ADM\WPPlugin\PostTypes\Course();
$course = $metabox['args']['class'];
$metas = $course::$metas;
global $TMS_CUSTOM_FILEDS;
if (!empty($TMS_CUSTOM_FILEDS)) {
    $metas = array_merge($metas, $TMS_CUSTOM_FILEDS);
}
?>
<div class="admwpp-form-wrapper admwpp-metabox">
    <?php if (!empty($metabox['args']['info'])) : ?>
      <fieldset class="admwpp-metabox-info-fieldset">
        <div class="field-group admwpp-metabox-info">
          <?php echo $metabox['args']['info']; ?>
        </div>
      </fieldset>
    <?php endif; ?>
    <div class="clearfix"></div>
    <?php
    foreach ($metas as $key => $value) {
        $label = $value['label'];
        $type = $value['type'];
        // Use get_post_meta to retrieve an existing value from the database and use the value for the form
        $value = get_post_meta($post->ID, $key, true);
        if (is_array($value)) {
            $meta_value = implode(", ", $value);
        } else {
            $meta_value = $value;
        }
        ?>
        <fieldset class="admwpp-fieldset">
            <div class="admwpp-field-group">
                <label for="admwpp-course-<?php echo $key; ?>" class="admwpp-field-label">
                    <?php echo __($label, ADMWPP_TEXT_DOMAIN); ?>
                </label>
                <?php
                switch ($type) {
                    case 'textarea':
                        ?>
                        <textarea id="admwpp-course-<?php echo $key; ?>" name="<?php echo $key; ?>" class="admwpp-field-textarea"><?php echo $meta_value; ?></textarea>
                        <?php
                        break;

                    default:
                        ?>
                    <input type="<?php echo $type; ?>" id="admwpp-course-<?php echo $key; ?>" name="<?php echo $key; ?>" value="<?php echo esc_attr($meta_value); ?>" class="admwpp-field-input" />
                        <?php
                        break;
                }
                ?>
            </div>
        </fieldset>
    <?php } ?>
</div>
