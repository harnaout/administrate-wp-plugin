<div class="adwmpp-filter-wrapper adwmpp-types-wrapper adwmpp-dropdown dropdown">
    <div class="adwmpp-dropdown-wrapper dropdown-wrapper">
        <div class="adwmpp-dropdown-body dropdown-body">
            <select class="admwpp-select admwpp-custom-select" name="type">
                <option class="adwmpp-option-item option-item" value="">
                    <?php _e('Type', 'admwpp'); ?>
                </option>
                <?php
                if ($typesFilter) :
                    foreach ($typesFilter as $key => $label) :
                        $selected = "";
                        if ($key === $type) {
                            $selected = "selected='selected'";
                        }
                        ?>
                        <option class="adwmpp-option-item option-item" value="<?php echo $key; ?>" <?php selected($key, $type); ?>>
                            <?php _e($label, 'admwpp'); ?>
                        </option>
                        <?php
                    endforeach;
                endif;
                ?>
            </select>
        </div>
    </div>
</div>
