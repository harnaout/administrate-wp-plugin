<?php

$title = $course['name'];
$teaserDescription = $course['teaserDescription'];
$category = $course['category'];
$price = $course['priceRange']['normalPrice']['amount'];
$imageUrl = $course['imageUrl'];
$courseId = ADM\WPPlugin\PostTypes\Course::checkifExists($course['id']);
?>
<div class='admwpp-cousre <?php echo ($template === 'list')?'admwpp-list':''; ?>'>
    <div class="inner-cont">
        <?php if ($imageUrl) : ?>
            <div class='admwpp-image'>
                <a href="<?php echo get_the_permalink($courseId); ?>">
                    <img src='<?php echo $imageUrl; ?>'/>
                </a>
            </div>
        <?php endif; ?>
        <div class='admwpp-info'>
            <h3 class="admwpp-course-title"><a href="<?php echo get_the_permalink($courseId); ?>">
            <?php echo $title; ?></a></h3>
            <?php if ($imageUrl) : ?>
                <div class='admwpp-desc'><?php echo $teaserDescription; ?></div>
            <?php endif;
            if ($category) : ?>
                <div class='admwpp-category'><?php echo $category; ?></div>
            <?php endif;
            if ($price) : ?>
                <div class='admwpp-price'><?php echo $price; ?></div>
            <?php endif; ?>
        </div>
    </div>
</div>
