<?php

function displayMainPage(&$s20Table,$myUrl){
    global $daysOfWeek;

    $ndevs = count($s20Table);

 ?>
<center>
<form action="<?php echo $myUrl ?>" method="post">
<?php

// Compute big button height (90 percent of viewport height) 
    $bheight = 90 / $ndevs;

// Compute timer margin
//    $tmargin = -$bheight * 0.9; //location:top

// Compute timer next action margin
    $tmargin_next = -$bheight * 0.22;    //location: bottom
    $tmargin = $tmargin_next;  
    
    
// Compute timer font size 
    $fsize = 3; //$bheight*0.15; 
    $fsize_next = 3; //$fsize * .6;
//
// Sort array (in this case, by mac address), such that data is displayed in 
// a deterministic sequence
//

    $macs = array_keys($s20Table);
    sort($macs);
//
// Loop on all devices and display each button, coloured according to
// current S20 state.
//



    $posTimerButton = 4;
    foreach ($macs as $mac){
        $devData = $s20Table[$mac];
        $st   = $devData['st'];
        $name = $devData['name'];
        $type = ($st == 0 ? "redbutton" : "greenbutton");
        $h ='style="height:'.$bheight.'vh;"'; 
        
        // display big button
        $bigButton='<button type="submit" name="toMainPage" 
               value="switch_'.$name.'" id="'.$type.'" '.$h.'>'.$name.'</button><br>'."\n"; 
        echo $bigButton;
        
        // overlay timer button for each field
        $timerButtonName = 'clock_'.$name;
        $styleTimer = 'style="top:'.$posTimerButton.'vh"';
        $posTimerButton+=$bheight;
        $timerButton     = '<input type="submit" name="toCountDownPage" id="timerButton" 
                value="timer_'.$name.'" '.$styleTimer.'/>'."\n";
        echo $timerButton;
        // Include field for timer information
        if($devData['timerVal'] != 0)
            if($devData['timerAction'])
                $color="#00BB00";
            else
                $color="#EE0000";
        else
            if($devData['switchOffTimer'] > 0)
                $color = "white";
            else
                $color = "black";

?>
        <div class="counter" id="<?php echo "b".$mac; 
              ?>" style="top:<?php      echo $tmargin ?>vh;
                         color:<?php     echo $color; ?>;
                         font-size:<?php echo $fsize; ?>vh;"></div>


<?php
        $next = getNextAction($mac,$s20Table);
        $nextAct  = $next[0];
        if($nextAct >= 0){
            if($nextAct){
                $color="#00BB00";
                $label = "on@ ";
            }
            else{
                $color="#EE0000";
                $label = "off@ ";
            }

            $nextTimeStamp = $next[1];
            $nextDate = getdate($nextTimeStamp);
            $hour = $nextDate['hours'];
            $min  = $nextDate['minutes'];
            $sec    = $nextDate['seconds'];
            
            $nextTimeS = sprintf("%02d:%02d:%02d",$hour,$min,$sec);
            $nextS = $label.$nextTimeS;
            if($nextTimeStamp-time() > 24*3600){
                $myWeekDay = $nextDate['wday'] - 1;
                if($myWeekDay < 0) $myWeekDay += 7;
                $weekDayName = $daysOfWeek[$myWeekDay];
                $nextS = $nextS.", ".substr($weekDayName,0,3);
            }
?>


                        <div class="next" style="top:<?php      echo $tmargin_next ?>vh;
                          color:<?php     echo $color; ?>;
                         font-size:<?php echo $fsize_next; ?>vh;"> <?php echo $nextS;?>   </div>
            
<?php
        }        
    } 
?>
</center>
<?php
}
?>


