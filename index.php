<?php
/**
 * Run MTAF and show results of run
 *
 * @author Alex Rodoman alex.rodoman@gmail.com
 */


include_once "MTAF_Runner.php";

$r = new MTAF_Runner();
$state = $r->checkState();
if (!isset($_POST['action'])) {
    ?>
    <head>
        <script src="jquery-1.9.1.js"></script>
    </head>
    <body>
    <h2 style="width: 100%; background-color: #cccccc; padding: 5px 5px 5px 25px;">MTAF Runner</h2>
    <h3>Current State: <?php echo $state; ?></h3>
    <form method="post" id="run_form">
        <input type="hidden" name="action" id="action" value="view">
        <?php
        if ($state != "running") {
            ?>
            <input type="submit" value="Run Script" onclick="$('#action').val('run');">
        <?php
        } else {
            ?>
            <input type="submit" value="Stop Script" onclick="$('#action').val('stop'); ">
        <?php
        }
        ?>
    </form>
    <?php
    // view
    if ($r->checkPartialResultPresent()) {
        ?>
        <div id="status"></div>
        <div id="results">
            Available Results:<br>
            <?php
            $availableResults = $r->listAvailableResults();
            foreach ($availableResults as $resultFile) {
                echo "<a href='" . $r->getResulstURL() . $resultFile . "?t=". time(). "' target='_blank'>" . $resultFile . "</a><br>";
            }
            ?>
        </div>
        <input type="button" value="Refresh" onclick="location.href='index.php';">
        </body>
    <?php
    }
} else {
    if ($_POST['action'] == 'run') {
        $r->run();
        header('location: index.php');
    } elseif ($_POST['action'] == 'stop') {
        $r->stop();
        header('location: index.php');
    }
}
if ($state == "running") {

?>
<script>
  $(document).ready(function(){
var refreshId = setInterval(function()
{
     $('#status').fadeOut("slow").load('get-results.php').fadeIn("slow");
}, 5000);
  });
</script>
<?php
}