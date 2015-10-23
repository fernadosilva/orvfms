<?php
function displayDetailsPage($timerName,&$s20Table,$myUrl){
    $mac = getMacFromName($timerName,$s20Table);
?>


<div style="text-align:center">
<h2> <?php echo $timerName ?> </h2>


<hr>
<?php
    $allTimers = parseTimerTable($mac,$s20Table);
    $nTimers  = count($allTimers);
    for($i = 0; $i < $nTimers ; $i++){
        $details = $allTimers[$i];
        //        $date=sprintf("%02d/%02d/%04d",
        //              $details['d'],$details['m'],$details['y']);
        $hour = secToHourString($details['time']);

        //        echo $i." set to switch ".($details['st'] ? "on " : "off").
        //       " at ".$hour." since ".$date." (stored in index ".
        //       $details['index'].")\n";   
        echo "<div>";
        echo $hour;
        echo "   ";
        echo $details['st'] ? "on " : "off";
        echo "<p>\n";
        echo "</div>";
    }
    echo "<hr><p><p>Sorry: not editable yet<p>";


    
?>

<?php
}
?>