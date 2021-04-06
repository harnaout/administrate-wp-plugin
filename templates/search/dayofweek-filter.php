<div class="adwmpp-filter-wrapper adwmpp-dayofweek-wrapper locations-wrapper adwmpp-checkbox checkbox">
    <h3 class="adwmpp-filter-title"><?php _e('Day Of Week', 'admwpp'); ?></h3>
    <div class="adwmpp-checkbox-wrapper checkbox-wrapper">
        <?php
        foreach ($daysOfWeekFilter as $key => $value) {
            $checked = "";
            if (in_array($key, $dayofweek)) :
                $checked = 'checked="checked"';
            endif;
            ?>
            <div class="adwmpp-checkbox-item">
                <input type="checkbox"
                id="adwmpp-<?php echo $key; ?>"
                name="dayofweek[]"
                value="<?php echo $key; ?>" <?php echo $checked; ?>/>
                <label for="adwmpp-<?php echo $key; ?>">
                    <?php _e($value, 'admwpp'); ?>
                </label>
            </div>
            <?php
        }
        ?>
    </div>
</div>
