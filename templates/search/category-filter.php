<div class="filter-wrapper categories-wrapper dropdown">
    <div class="input-wrapper">
        <label><i class="icon cats-icon"></i><?php _e('Categorie', 'cga'); ?></label>
        <span class="chosen-options"></span>
        <i class="toggle-arrow"></i>
    </div>
    <div class="dropdown-wrapper">
        <div class="dropdown-body">
            <?php if (!empty($categories)) : ?>
                <ul class="category-listing">
                    <?php foreach ($categories as $category) :
                        $admwpp_tms_id = get_term_meta($category->term_id, 'admwpp_tms_id', true);
                        if ($admwpp_tms_id) :
                            $checked = "";
                            if ($admwpp_tms_id === $lcat) {
                                $checked = "checked='checked'";
                            }
                            ?>
                            <li class="checkbox-item">
                                <input type="radio" class="checkbox-input"
                                id="learning-cat-<?php echo $category->term_id; ?>"
                                name="lcat"
                                value="<?php echo $admwpp_tms_id; ?>"
                                <?php echo $checked; ?>
                                />
                                <label for="learning-cat-<?php echo $category->term_id; ?>">
                                  <i></i>
                                  <span><?php echo $category->name; ?></span>
                                </label>
                            </li>
                            <?php
                        endif;
                    endforeach;
                    ?>
                </ul>
            <?php endif; ?>
        </div>
        <div class="dropdown-footer">
            <div class="links-wrapper">
                <button class="filter-btn" value="<?php _e('Filter wissen', 'cga'); ?>">
                    <?php _e('Filter wissen', 'cga'); ?>
                </button>
                <button class="search-btn" value="<?php _e('Zoeken', 'cga'); ?>">
                    <?php _e('Zoeken', 'cga'); ?>
                </button>
            </div>
        </div>
    </div>
</div>
