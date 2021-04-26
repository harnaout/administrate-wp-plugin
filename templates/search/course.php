<?php
$courseId = $course['postId'];
$type = $course['type'];
$title = $course['name'];
$courseDescription = wp_trim_words($course['description'], 25);
$formattedPrice = $course['formattedPrice'];
$imageUrl = $course['imageUrl'];
$category = $course['category'];
$summary = $course['summary'];
?>
<div class='admwpp-cousre <?php echo ($template === 'list')?'admwpp-list':''; ?>'>
    <div class="inner-cont">
        <?php if (isset($imageUrl)) : ?>
            <div class='admwpp-image'>
                <a href="<?php echo get_the_permalink($courseId); ?>">
                    <img src='<?php echo $imageUrl; ?>'/>
                </a>
            </div>
        <?php endif; ?>
        <div class='admwpp-info'>
            <h3 class="admwpp-course-title"><a href="<?php echo get_the_permalink($courseId); ?>">
            <?php echo $title; ?></a></h3>
            <?php if ($summary) : ?>
                <div class='admwpp-desc'><?php echo $summary; ?></div>
            <?php endif;
            if ($category) : ?>
                <div class='admwpp-category'><?php echo $category; ?></div>
            <?php endif;
            if ($formattedPrice) : ?>
                <div class='admwpp-price'><?php echo $formattedPrice; ?></div>
            <?php endif; ?>
        </div>
    </div>
</div>
