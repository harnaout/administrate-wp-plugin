<div class="adwmpp-filter-wrapper adwmpp-timeofday-wrapper timeofday-wrapper adwmpp-dropdown dropdown">
    <div class="adwmpp-dropdown-wrapper dropdown-wrapper">
        <div class="adwmpp-dropdown-body dropdown-body">
            <select class="admwpp-select admwpp-custom-select" name="timeofday" autocomplete="off">
                <option class="adwmpp-option-item option-item" value="">
                    <?php _e('Time of Day', 'admwpp'); ?>
                </option>
                <?php
                if ($timeofdayFilter) :
                    foreach ($timeofdayFilter as $key => $value) :
                        $selected = "";
                        if ($key === $timeofday) {
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
</div>
