<?php
global $post;
$course = new ADM\WPPlugin\PostTypes\Course();
$courseTerms = get_the_term_list($post->ID, $course::$taxonomy, '<div class="admwpp-categories-list">', ', ', '</div>');
if (!empty($courseTerms)) :
    echo $courseTerms;
endif;
?>