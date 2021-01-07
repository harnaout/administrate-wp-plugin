<?php
global $wp;
$queryVars = array();

if (get_query_var('query')) {
    $queryVars['query'] = get_query_var('query');
}
if (get_query_var('lcat')) { 
    $queryVars['lcat'] = get_query_var('lcat');
}
if (get_query_var('paged')) {
    $queryVars['paged'] = get_query_var('paged');
}

$currentPageNumber = $searchResults['currentPage'];
$totalPageNumber = $searchResults['totalNumPages'];

$httpBuildQuery = http_build_query($queryVars);
$currentUrl = home_url( $wp->request ) . '?' . $httpBuildQuery;

$queryVars['paged'] = 1;
$httpBuildQuery = http_build_query($queryVars);
$firstPageUrl = home_url( $wp->request ) . '?' . $httpBuildQuery;

$queryVars['paged'] = $totalPageNumber;
$httpBuildQuery = http_build_query($queryVars);
$lastPageUrl = home_url( $wp->request ) . '?' . $httpBuildQuery;

$queryVars['paged'] = $currentPageNumber - 1;
$httpBuildQuery = http_build_query($queryVars);
$prevPageUrl = home_url( $wp->request ) . '?' . $httpBuildQuery;

$queryVars['paged'] = $currentPageNumber + 1;
$httpBuildQuery = http_build_query($queryVars);
$nextPageUrl = home_url( $wp->request ) . '?' . $httpBuildQuery;

$prevPage = '';
$nextPage = '';
$pagination = array(
    'first' => '',
    'prev' => '',
    'current' => '<span>' . $currentPageNumber . '/' . $totalPageNumber . '</span>',
    'next' => '',
    'last' => ''
);
if ($searchResults['hasPreviousPage'] == true) :
    $pagination['first'] = '<a href="' . $firstPageUrl . '" class="admwpp-first first">' . __('<< first', ADMWPP_TEXT_DOMAIN) . '</a>';
    $pagination['prev']  = '<a href="' . $prevPageUrl . '" class="admwpp-prev">' . __('< prev', ADMWPP_TEXT_DOMAIN) . '</a>';
endif;
if ($searchResults['hasNextPage'] == true) :
    $pagination['next'] = '<a href="' . $nextPageUrl . '" class="admwpp-next">' . __('next >', ADMWPP_TEXT_DOMAIN) . '</a>';
    $pagination['last'] = '<a href="' . $lastPageUrl . '" class="admwpp-last last">' . __('last >>', ADMWPP_TEXT_DOMAIN) . '</a>';
endif;

if ($searchResults['totalNumPages'] > 1) :
    if ($pager == 'simple') : ?>
        <div class="admwpp-pager admwpp-simple-pager">
            <?php echo implode("", $pagination); ?>
        </div>
    <?php else : ?>
        <div class="admwpp-pager admwpp-full-pager">
            <?php echo paginate_links(array(
                'format' => '?paged=%#%',
                'total' => $totalPageNumber,
                'prev_text' => '<i class="fa fa-chevron-left"></i>',
                'next_text' => '<i class="fa fa-chevron-right"></i>',
                'end_size' => 3,
            )); ?>
       </div>
<?php endif;
endif; ?>
