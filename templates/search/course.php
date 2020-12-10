<?php
$title = $course['name'];
$teaserDescription = $course['teaserDescription'];
$category = $course['category'];
$price = $course['priceRange']['normalPrice']['amount'];
$imageUrl = $course['imageUrl'];
?>
<div class='row admwpp-cousre'>
    <div class='iadmwpp-mage col-xs-12 col-sm-6'><img src='<?php echo $imageUrl; ?>'/></div>
    <div class='col-xs-12 col-sm-6'>
        <div class='admwpp-title'><?php echo $title; ?></div>
        <div class='admwpp-desc'><?php echo $teaserDescription; ?></div>
        <div class='admwpp-category'><?php echo $category; ?></div>
        <div class='admwpp-price'><?php echo $price; ?></div>
    </div>
</div>


