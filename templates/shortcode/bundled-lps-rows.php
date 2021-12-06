<?php
foreach ($bundledLps['bundledLps'] as $tmsId => $lp) {
    $ojectives = array();
    foreach ($lp['events'] as $key => $event) {
        $ojectives[] = $event['name'];
    }

    $start = date('d/m/Y', strtotime($lp['start']));
    $end = date('d/m/Y', strtotime($lp['end']));

    $startTime = date('h:m', strtotime($lp['start']));
    $endTime = date('h:m', strtotime($lp['end']));

    echo "<tr class='admwpp-lp' data-admwpp_tms_id='$tmsId'>";
    echo "<td class='admwpp-title'>" . $lp['name'] . "</td>";
    echo "<td class='admwpp-ojectives'>" . implode("<br/>", $ojectives) ."</td>";
    echo "<td class='admwpp-language'>" . $lp['language'] . "</td>";
    echo "<td class='admwpp-date'>$start - $end</td>";
    echo "<td class='admwpp-time'>$startTime - $endTime</td>";
    echo "<td class='admwpp-price'>" . $lp['formattedPrice'] . "</td>";
    echo "<td class='admwpp-action'>" . do_shortcode("[admwswp-addToCart path_id='$tmsId']") . "</td>";
    echo "</tr>";
}
