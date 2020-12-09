<?php
global $post;
$course = new ADM\WPPlugin\PostTypes\Course();
$metas = $course::$metas;
if (!empty($metas)) : ?>
    <ul class='admwpp-meta-information'>
        <?php foreach ($metas as $key => $meta) :
            $label = $meta['label'];
            $type = $meta['type'];
            $showOnFornt = $meta['showOnFront'];
            $tmsKey = $meta['tmsKey'];
            $metaValue = get_post_meta($post->ID, $key, true);
            if ($metaValue && $showOnFornt == true) :
                echo "<li class='admwpp-$key'>
                <span class='admwpp-label label'>" . $label . ":</span>
                <span>" . $metaValue . "</span></li>";
            endif;
        endforeach; ?>
    </ul>
<?php endif; ?>
