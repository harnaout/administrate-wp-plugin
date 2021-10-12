<?php
/**
 * search-form.php
 *
 * PHP version 7
 *
 * @author Omaya Noureddine <orn@administrate.co>
 */
?>
<div class="adwmpp-filters adwmpp-search-cats-filters">
    <form class="adwmpp-filters-form" id="adwmpp-search-form" method="GET">
        <div class='row adwmpp-filters-row'>
            <div class='col-sm-12 col-md-4 input-wrapper adwmpp-search-wrapper'>
                <input class="adwmpp-search-input" type="text" name='query' value='<?php echo $query; ?>' placeholder="<?php _e('Search...', ADMWPP_TEXT_DOMAIN); ?>" autocomplete="off"/>
                <button type="submit" class="adwmpp-search-button btn">
                    <i class="fa fa-search"></i>
                </button>
            </div>
            <div class='col-sm-12 col-md-4'><?php include($categoryFilterTemplate); ?></div>
            <div class='col-sm-12 col-md-4'><?php include($locationsFilterTemplate); ?></div>
        </div>
        <div class='row adwmpp-filters-row'>
            <div class='col-sm-12 col-md-4'><?php include($dayOfWeekTemplate); ?></div>
            <div class='col-sm-12 col-md-4'><?php include($timeOfDayTemplate); ?></div>
            <div class='col-sm-12 col-md-4'><?php include($minPlacesTemplate); ?></div>
        </div>
        <div class='row adwmpp-filters-row'>
            <div class='col-sm-12 col-md-4'><?php include($dateFilterTemplate); ?></div>
            <div class='col-sm-12 col-md-6'></div>
            <div class='col-sm-12 col-md-2'>
                <div class="adwmpp-dropdown-footer dropdown-footer pull-right">
                    <div class="adwmpp-links-wrapper links-wrapper">
                        <?php
                        global $post;
                        $pageUrl = get_the_permalink($post);
                        ?>
                        <a href="<?php echo $pageUrl; ?>" class="btn btn-info adwmpp-filter-btn clear-btn adwmpp-btn" value="<?php _e('Clear', ADMWPP_TEXT_DOMAIN); ?>"><?php _e('Clear', ADMWPP_TEXT_DOMAIN); ?></a>
                        <button class="adwmpp-search-btn adwmpp-btn btn btn-info" value="Search">
                            <?php _e('Search', ADMWPP_TEXT_DOMAIN); ?>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
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
        <?php else : ?>
            <div class="admwpp-courses-listing row">
                <div class='col-sm-12'>
                <p><?php _e('No results found!', ADMWPP_TEXT_DOMAIN); ?></p>
                </div>
            </div>
        <?php endif;
        include($pagerTemplate);
    } ?>
</div>
