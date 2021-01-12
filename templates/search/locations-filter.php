<div class="adwmpp-filter-wrapper adwmpp-locations-wrapper locations-wrapper adwmpp-dropdown dropdown">
    <div class="adwmpp-input-wrapper">
        <label><i class="adwmpp-locations-icon locations-icon"></i><?php _e('locations', ADMWPP_TEXT_DOMAIN); ?></label>
        <span class="adwmpp-chosen-options chosen-options"></span>
        <i class="adwmpp-toggle-arrow toggle-arrow"></i>
    </div>
    <div class="adwmpp-dropdown-wrapper dropdown-wrapper">
        <div class="adwmpp-dropdown-body dropdown-body">
            <?php if ($locations_filter_type == 'select') : ?>
                <select class="admwpp-select admwpp-custom-select" name="loc">
                    <option class="adwmpp-option-item option-item" value="first location">
                        <label for="location-id-1">
                            <i></i>
                            <span>first location</span>
                        </label>
                    </option>
                    <option class="adwmpp-option-item option-item" value="second location">
                        <label for="location-id-2">
                            <i></i>
                            <span>second location</span>
                        </label>
                    </option>
                    <option class="adwmpp-option-item option-item" value="third location">
                        <label for="location-id-3">
                            <i></i>
                            <span>third location</span>
                        </label>
                    </option>
                    <option class="adwmpp-option-item option-item" value="forth location">
                        <label for="location-id-4">
                            <i></i>
                            <span>forth location</span>
                        </label>
                    </option>
                </select>
            <?php else : ?>
                <ul class="admwpp-location-listing">
                    <li class="adwmpp-checkbox-item checkbox-item">
                        <input type="radio" class="adwmpp-checkbox-input checkbox-input"
                        id="location-id-1"
                        name="loc"
                        value="first location">
                        <label for="location-id-1">
                            <i></i>
                            <span>first location</span>
                        </label>
                    </li>
                    <li class="adwmpp-checkbox-item checkbox-item">
                        <input type="radio" class="adwmpp-checkbox-input checkbox-input"
                        id="location-id-2"
                        name="loc"
                        value="Second location">
                        <label for="location-id-2">
                            <i></i>
                            <span>Second location</span>
                        </label>
                    </li>
                    <li class="adwmpp-checkbox-item checkbox-item">
                        <input type="radio" class="adwmpp-checkbox-input checkbox-input"
                        id="location-id-3"
                        name="loc"
                        value="Third location">
                        <label for="location-id-3">
                            <i></i>
                            <span>Third location</span>
                        </label>
                    </li>
                    <li class="adwmpp-checkbox-item checkbox-item">
                        <input type="radio" class="adwmpp-checkbox-input checkbox-input"
                        id="location-id-4"
                        name="loc"
                        value="Forth location">
                        <label for="location-id-4">
                            <i></i>
                            <span>Forth location</span>
                        </label>
                    </li>
                </ul>
            <?php endif; ?>
        </div>
    </div>
</div>
