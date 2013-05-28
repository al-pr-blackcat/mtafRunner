<?php
/**
 * Run MTAF and show results of run
 *
 * @author Alex Rodoman alex.rodoman@gmail.com
 */
include_once "MTAF_Runner.php";
$r = new MTAF_Runner();
$results = $r->getResulstURL(). 'logfile.txt';
echo "<pre>".implode(file($results))."</pre>";
?>
