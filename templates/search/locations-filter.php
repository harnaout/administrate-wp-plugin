<?php
$locationsFilter = ADM\WPPlugin\Api\Search::getLocationsFilter();
?>
<div class="adwmpp-filter-wrapper adwmpp-locations-wrapper adwmpp-dropdown dropdown">
    <div class="adwmpp-dropdown-wrapper dropdown-wrapper">
        <div class="adwmpp-dropdown-body dropdown-body">
            <select class="admwpp-select admwpp-custom-select" name="loc[]" multiple>
                <option class="adwmpp-option-item option-item" value="">
                    <?php _e('Locations', 'admwpp'); ?>
                </option>
                <?php
                if ($locationsFilter) :
                    foreach ($locationsFilter as $tmsId => $location) :
                        $selected = "";
                        if (in_array($tmsId, $loc)) {
                            $selected = "selected='selected'";
                        }
                        ?>
                        <option class="adwmpp-option-item option-item" value="<?php echo $tmsId; ?>" <?php echo $selected; ?>>
                            <?php _e($location, 'admwpp'); ?>
                        </option>
                        <?php
                    endforeach;
                endif;
                ?>
            </select>
        </div>
    </div>
</div>
