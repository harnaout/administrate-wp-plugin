<?php
$categoriesFilter = ADM\WPPlugin\Api\Search::getCategoriesFilter();
?>
<div class="adwmpp-filter-wrapper adwmpp-categories-wrapper categories-wrapper adwmpp-dropdown dropdown">
    <div class="adwmpp-dropdown-wrapper dropdown-wrapper">
        <div class="adwmpp-dropdown-body dropdown-body">
            <?php if (!empty($categoriesFilter)) : ?>
                <select class="admwpp-select admwpp-custom-select" name="lcat[]" multiple autocomplete="off">
                    <option class="adwmpp-option-item option-item" value="">
                        <?php _e("Categories", 'admwpp'); ?>
                    </option>
                    <?php foreach ($categoriesFilter as $tmsId => $category) :
                        $categoryName = $category['name'];
                        $selected = "";
                        $class = "";
                        if (in_array($tmsId, $lcat)) {
                            $selected = "selected='selected'";
                        }
                        if (isset($category['children'])) {
                            $children = $category['children'];
                            $class = 'admwpp-has-children';
                        }
                        ?>
                        <option class="adwmpp-option-item option-item <?php echo $class; ?>" value="<?php echo $tmsId; ?>"
                        <?php echo $selected; ?>><?php echo __($categoryName, 'admwpp'); ?></option>
                        <?php
                        if (isset($children)) {
                            foreach ($children as $tmsId => $childName) {
                                $class = 'admwpp-child-item';
                                $selected = "";
                                if (in_array($tmsId, $lcat)) {
                                    $selected = "selected='selected'";
                                }
                                ?>
                                <option class="adwmpp-option-item option-item <?php echo $class; ?>" value="<?php echo $tmsId; ?>"
                                <?php echo $selected; ?>><?php echo __($childName, 'admwpp'); ?></option>
                                <?php
                            }
                        }
                    endforeach;
                    ?>
                </select>
            <?php endif; ?>
        </div>
    </div>
</div>
