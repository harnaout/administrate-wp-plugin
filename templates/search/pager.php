<?php
global $wp;
$queryVars = array();
$pagedParam = '?paged=';
if (get_query_var('query')) {
    $queryVars['query'] = get_query_var('query');
    $pagedParam = '&paged=';
}
if (get_query_var('lcat')) { 
    $queryVars['lcat'] = get_query_var('lcat');
    $pagedParam = '&paged=';
}
if (get_query_var('paged')) {
    $queryVars['paged'] = get_query_var('paged');
}
$currentUrl = add_query_arg( $queryVars, home_url( $wp->request ) . '/');

$currentPageNumber = $searchResults['currentPage'];
$totalPageNumber = $searchResults['totalNumPages'];

if (isset($_GET['paged']) && !empty($_GET['paged'])) {
    $pattern = '/paged=' . $currentPageNumber . '/';
    $firstPageUrl = preg_replace($pattern, 'paged=1', $currentUrl);
    $lastPageUrl = preg_replace($pattern, 'paged=' . $totalPageNumber, $currentUrl);
    $prevPageUrl = preg_replace($pattern, 'paged=' . ($currentPageNumber - 1), $currentUrl);
    $nextPageUrl = preg_replace($pattern, 'paged=' . ($currentPageNumber + 1), $currentUrl);
} else {
    $firstPageUrl = $currentUrl . $pagedParam . 1;
    $lastPageUrl = $currentUrl . $pagedParam . $totalPageNumber;
    $prevPageUrl = $currentUrl . $pagedParam . ($currentPageNumber - 1);
    $nextPageUrl = $currentUrl . $pagedParam . ($currentPageNumber + 1);
}
$prevPage = '';
$nextPage = '';
if ($searchResults['hasPreviousPage'] == true) :
    $prevPage = '<a href="' . $prevPageUrl . '" class="admwpp-prev">' . __('prev <', ADMWPP_TEXT_DOMAIN) . '</a>';
endif;
if ($searchResults['hasNextPage'] == true) :
    $nextPage = '<a href="' . $nextPageUrl . '" class="admwpp-next">' . __('> next', ADMWPP_TEXT_DOMAIN) . '</a>';
endif;

if ($searchResults['totalNumPages'] > 1) :
    if ($pager == 'simple') : ?>
        <div class="admwpp-pager admwpp-simple-pager">
            <a href="<?php echo esc_url($firstPageUrl); ?>" class='admwpp-first first'>first <<</a>
            <?php echo $prevPage; ?>
            <span><?php echo $currentPageNumber . '/' . $totalPageNumber; ?></span>
            <?php echo $nextPage; ?>
            <a href="<?php echo esc_url($lastPageUrl); ?>" class='admwpp-last last'>>> last</a>
        </div>
    <?php endif;
endif; ?>
