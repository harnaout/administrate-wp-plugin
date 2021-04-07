<?php
$categories = get_terms(array(
    'taxonomy' => 'learning-category',
    'hide_empty' => true,
    'parent' => 0,
));
?>
<div class="adwmpp-filter-wrapper adwmpp-categories-wrapper categories-wrapper adwmpp-dropdown dropdown">
    <div class="adwmpp-input-wrapper">
        <label><i class="adwmpp-cats-icon cats-icon"></i><?php _e('Categories', ADMWPP_TEXT_DOMAIN); ?></label>
        <span class="adwmpp-chosen-options chosen-options"></span>
        <i class="adwmpp-toggle-arrow toggle-arrow"></i>
    </div>
    <div class="adwmpp-dropdown-wrapper dropdown-wrapper">
        <div class="adwmpp-dropdown-body dropdown-body">
            <?php if (!empty($categories)) :
                if ($categories_filter_type == 'select') : ?>
                    <select class="admwpp-select admwpp-custom-select" name="lcat[]" multiple>
                        <option class="adwmpp-option-item option-item" value="">
                            <label>
                            <i></i>
                            <span>Select a category</span>
                            </label>
                        </option>
                        <?php foreach ($categories as $category) :
                            $admwpp_tms_id = get_term_meta($category->term_id, 'admwpp_tms_id', true);
                            if ($admwpp_tms_id) :
                                $selected = "";
                                if (in_array($admwpp_tms_id, $lcat)) {
                                    $selected = "selected='selected'";
                                }
                                ?>
                                <option class="adwmpp-option-item option-item" value="<?php echo $admwpp_tms_id; ?>"
                                <?php echo $selected; ?>>
                                    <label for="learning-cat-<?php echo $category->term_id; ?>">
                                    <i></i>
                                    <span><?php echo $category->name; ?></span>
                                    </label>
                                </option>
                                <?php
                            endif;
                        endforeach;
                        ?>
                    </select>
                <?php else : ?>
                    <ul class="admwpp-category-listing">
                        <?php foreach ($categories as $category) :
                            $admwpp_tms_id = get_term_meta($category->term_id, 'admwpp_tms_id', true);
                            if ($admwpp_tms_id) :
                                $checked = "";
                                if (in_array($admwpp_tms_id, $lcat)) {
                                    $checked = "checked='checked'";
                                }
                                ?>
                                <li class="adwmpp-checkbox-item checkbox-item">
                                    <input type="radio" class="adwmpp-checkbox-input checkbox-input"
                                    id="learning-cat-<?php echo $category->term_id; ?>"
                                    name="lcat[]"
                                    value="<?php echo $admwpp_tms_id; ?>"
                                    <?php echo $checked; ?>
                                    />
                                    <label for="learning-cat-<?php echo $category->term_id; ?>">
                                    <i></i>
                                    <span><?php echo $category->name; ?></span>
                                    </label>
                                </li>
                                <?php
                            endif;
                        endforeach;
                        ?>
                    </ul>
                <?php endif;
            endif; ?>
        </div>
    </div>
</div>
