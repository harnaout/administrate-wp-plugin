<?php
/**
 * search-form.php
 *
 * PHP version 7
 *
 * @author Omaya Noureddine <orn@administrate.co>
 */
?>
<div class="mobile-filter-button-wrapper">
    <div class="mobile-filter-button">
        <i class="fas fa-filter"></i>
        <span><?php _e('Filter resultaten', 'cga'); ?></span>
    </div>
</div>
<div class="filters search-cats-filters">
    <div class="inner">
        <div class="top-head">
            <div class="left-wrapper">
                <i class="fas fa-filter"></i>
                <span><?php _e('Filter resultaten', 'cga'); ?></span>
            </div>
            <div class="right-wrapper">
                <div class="close-wrapper">
                    <a class="close-btn">
                        <span></span>
                    </a>
                </div>
            </div>
        </div>
        <form class="filters-form" id="adwmpp-search-form" method="GET">
            <div class="input-wrapper search-wrapper">
                <input class="search-input" type="text" name='query' value='<?php echo $query; ?>' placeholder="<?php _e('Search...', 'cga'); ?>" />
                <button type="submit" class="search-button">
                    <i class="fa fa-search"></i>
                </button>
            </div>
            <?php include($categoryFilterTemplate); ?>
        </form>
    </div>
</div>
<div class="search-results">
    <?php
    if ($searchResults) {
        if (!empty($searchResults['courses'])) {
            $courses = $searchResults['courses'];
            foreach ($courses as $key => $course) {
                $course = $course['node'];
                include($courseTemplate);
            }
        }
    }
    ?>
</div>
