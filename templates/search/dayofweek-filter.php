<div class="adwmpp-filter-wrapper adwmpp-dayofweek-wrapper adwmpp-dropdown dropdown">
    <div class="adwmpp-checkbox-wrapper checkbox-wrapper">
        <select class="admwpp-select admwpp-custom-select" name="dayofweek[]" multiple>
            <option class="adwmpp-option-item option-item" value="">
                <?php _e('Day of Week', 'admwpp'); ?>
            </option>
        <?php
        if ($daysOfWeekFilter) :
            foreach ($daysOfWeekFilter as $key => $value) :
                $selected = "";
                if (in_array($key, $dayofweek)) {
                    $selected = "selected='selected'";
                }
                ?>
                <option class="adwmpp-option-item option-item" value="<?php echo $key; ?>" <?php echo $selected; ?>>
                    <?php _e($value, 'admwpp'); ?>
                </option>
                <?php
            endforeach;
        endif;
        ?>
        </select>
    </div>
</div>
