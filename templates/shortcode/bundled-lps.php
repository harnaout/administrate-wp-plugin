<div class="admwpp-bundled-lps">
    <?php
    //echo do_shortcode("[admwswp-addToCart path_id='TGVhcm5pbmdQYXRoOjMz']");
    if ($bundledLps) {
        ?>
        <table class="table table-striped table-sm table-responsive">
            <thead class="thead-light">
               <tr>
                 <th scope="col"><?php echo __("Bundle", "admwpp"); ?></th>
                 <th scope="col"><?php echo __("Objectives", "admwpp"); ?></th>
                 <th scope="col"><?php echo __("Language", "admwpp"); ?></th>
                 <th scope="col"><?php echo __("Date", "admwpp"); ?></th>
                 <th scope="col"><?php echo __("Time", "admwpp"); ?></th>
                 <th scope="col"><?php echo __("Price*", "admwpp"); ?></th>
                 <th scope="col"></th>
               </tr>
            </thead>
            <tbody>
        <?php
        foreach ($bundledLps as $tmsId => $lp) {
            $ojectives = array();
            foreach ($lp['events'] as $key => $event) {
                $ojectives[] = $event['name'];
            }

            $start = date('d/m/Y', strtotime($lp['start']));
            $end = date('d/m/Y', strtotime($lp['end']));

            $startTime = date('h:m', strtotime($lp['start']));
            $endTime = date('h:m', strtotime($lp['end']));

            echo "<tr class='admwpp-lp admwpp-lp-$tmsId'>";
            echo "<td class='admwpp-title'>" . $lp['name'] . "</td>";
            echo "<td class='admwpp-ojectives'>" . implode("<br/>", $ojectives) ."</td>";
            echo "<td class='admwpp-language'></td>";
            echo "<td class='admwpp-date'>$start - $end</td>";
            echo "<td class='admwpp-time'>$startTime - $endTime</td>";
            echo "<td class='admwpp-price'>" . $lp['formattedPrice'] . "</td>";
            echo "<td class='admwpp-action'>" . do_shortcode("[admwswp-addToCart path_id='$tmsId']") . "</td>";
            echo "</tr>";
        }
        ?> </tbody>
    </table>
        <?php
    } else {
        echo __("No Bundles yet to be listed.", "admwpp");
    }
    ?>
</div>
