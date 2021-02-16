<?php
/**
 * search-form.php
 *
 * PHP version 7
 *
 * @author Omaya Noureddine <orn@administrate.co>
 */
?>
<div class="adwmpp-mobile-filter-button-wrapper">
    <div class="adwmpp-mobile-filter-button">
        <i class="fas fa-filter"></i>
        <span><?php _e('Filter resultaten', ADMWPP_TEXT_DOMAIN); ?></span>
    </div>
</div>
<div class="adwmpp-filters adwmpp-search-cats-filters">
    <div class="inner">
        <div class="adwmpp-top-head">
            <div class="left-wrapper">
                <i class="fas fa-filter"></i>
                <span><?php _e('Filter resultaten', ADMWPP_TEXT_DOMAIN); ?></span>
            </div>
            <div class="right-wrapper">
                <div class="close-wrapper">
                    <a class="close-btn">
                        <span></span>
                    </a>
                </div>
            </div>
        </div>
        <form class="adwmpp-filters-form" id="adwmpp-search-form" method="GET">
            <div class="input-wrapper adwmpp-search-wrapper">
                <input class="adwmpp-search-input" type="text" name='query' value='<?php echo $query; ?>' placeholder="<?php _e('Search...', ADMWPP_TEXT_DOMAIN); ?>" />
                <button type="submit" class="adwmpp-search-button">
                    <i class="fa fa-search"></i>
                </button>
            </div>
            <?php include($categoryFilterTemplate);
            if ($dateSettingsOption == 1) {
                include($dateFilterTemplate);
            }
            if ($locationSettingsOption == 1) {
                include($locationsFilterTemplate);
            } ?>
            <div class="adwmpp-dropdown-footer dropdown-footer">
                <div class="adwmpp-links-wrapper links-wrapper">
                    <button class="adwmpp-filter-btn adwmpp-btn" value="<?php _e('Filter wissen', 'cga'); ?>">
                        <?php _e('Filter', ADMWPP_TEXT_DOMAIN); ?>
                    </button>
                    <button class="adwmpp-search-btn adwmpp-btn" value="<?php _e('Zoeken', 'cga'); ?>">
                        <?php _e('Search', ADMWPP_TEXT_DOMAIN); ?>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
<div class="admwpp-search-results">
    <?php
    if ($searchResults) {
        if (!empty($searchResults['courses'])) :
            $courses = $searchResults['courses']; ?>
            <div class="admwpp-courses-listing">
                <?php foreach ($courses as $key => $course) :
                    include($courseTemplate);
                endforeach ?>
            </div>
        <?php endif;
        include($pagerTemplate);
    } ?>
</div>
