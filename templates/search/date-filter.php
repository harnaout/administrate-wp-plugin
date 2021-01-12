<div class="admwpp-date-filter">
    <label for="from">From</label>
    <input type="text" class="admwpp-date admwpp-from-date" id="from" name="from"
    <?php echo (isset($_GET['from']) && !empty($_GET['from']))?'value="' . $_GET['from'] . '"':''; ?>>
    <label for="to">to</label>
    <input type="text" class="admwpp-date admwpp-to-date" id="to" name="to"
    <?php echo (isset($_GET['to']) && !empty($_GET['to']))?'value="' . $_GET['to'] . '"':''; ?>>
</div>
