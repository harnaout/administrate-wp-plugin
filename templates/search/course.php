<?php
$courseId = $course['postId'];
$type = $course['type'];
$title = $course['name'];
$courseDescription = wp_trim_words($course['description'], 25);
$formattedPrice = $course['formattedPrice'];
$imageUrl = $course['imageUrl'];
$category = $course['category'];
$summary = $course['summary'];
$permalink = get_the_permalink($courseId);
$customFieldValues = $course['customFieldValues'];
?>
<div class='admwpp-cousre <?php echo ($template === 'list')?'admwpp-list-item':'admwpp-grid-item col-12 col-sm-6 col-md-4'; ?>'>
    <div class="inner-cont">
        <?php if (isset($imageUrl)) : ?>
            <div class='admwpp-image'>
                <a href="<?php echo $permalink; ?>">
                    <img src='<?php echo $imageUrl; ?>'/>
                </a>
            </div>
        <?php endif; ?>
        <div class='admwpp-info'>
            <h3 class="admwpp-course-title"><a href="<?php echo $permalink; ?>">
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
