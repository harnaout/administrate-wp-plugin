<?php include ADMWPP_TEMPLATES_DIR . 'development/_routes_table_head.php'; ?>

<?php foreach ($routes as $route) : ?>
    <?php include ADMWPP_TEMPLATES_DIR . 'development/route.php'; ?>
<?php endforeach; ?>

<?php include ADMWPP_TEMPLATES_DIR . 'development/_routes_table_footer.php';
