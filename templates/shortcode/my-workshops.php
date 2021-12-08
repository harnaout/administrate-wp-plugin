<div class="admwpp-my-workshops">
    <?php
    if ($workshops) {
        foreach ($workshops as $key => $workshop) {
            $postId = $workshop['postId'];
            $title = $workshop['title'];
            $events = $workshop['events'];
            echo "<div class='admwpp-workshop admwpp-workshop-$key'>";

            if ($postId) {
                echo "<h2 class='admwpp-title'><a class='course-link' target='_blank' href='".get_the_permalink($postId)."'>$title</a></h2>";
            } else {
                echo "<h2 class='admwpp-title'>$title</h2>";
            }

            if (!empty($events)) {
                ?>
                <table class="table table-striped table-sm table-responsive">
                    <thead class="thead-light">
                       <tr>
                         <th scope="col"><?php echo __("Title", "admwpp"); ?></th>
                         <th scope="col"><?php echo __("Location", "admwpp"); ?></th>
                         <th scope="col"><?php echo __("Booked Places", "admwpp"); ?></th>
                         <th scope="col"><?php echo __("Reserved", "admwpp"); ?></th>
                         <th scope="col"><?php echo __("Starts", "admwpp"); ?></th>
                       </tr>
                    </thead>
                    <tbody>
                    <?php
                    foreach ($events as $key => $event) {
                        $title = $event['title'];
                        $bookedPlaces = $event['bookedPlaces'];
                        $reserved = $event['reserved'] ? 'YES' : 'NO';
                        $type = $event['type'];
                        $location = $event['location'];
                        $start = date(ADMWPP_DATE_TIME_FORMAT, strtotime($event['start']));
                        echo "<tr class='admwpp-event admwpp-event-$key admwpp-event-$type'>";
                        echo "<td class='admwpp-title'>$title</td>";
                        echo "<td class='admwpp-location'>$location</td>";
                        echo "<td class='admwpp-places'>$bookedPlaces</td>";
                        echo "<td class='admwpp-reserved'>$reserved</td>";
                        echo "<td class='admwpp-start'>$start</td>";
                        echo "</tr>";
                    }
                    ?>
                    </tbody>
                </table>
                <?php
            }
            echo "</div>";
        }
    } else {
        echo __("No workshops yet to be listed.", "admwpp");
    }
    ?>
</div>
