<?php

function displayMainPage(&$s20Table,$myUrl){
    global $daysOfWeek;

    $ndevs = count($s20Table);

 ?>
<center>
<form action="<?php echo $myUrl ?>" method="post">
<?php

//
// Sort array (in this case, by mac address), such that data is displayed in 
// a deterministic sequence
//

    $macs = array_keys($s20Table);

    sort($macs);
    //
    // Dynamic location style details;
    //
// Compute big button height (90 percent of viewport height) 
    $bigButHeight = 90 / $ndevs;    
    
// Timer& next action time font size 
    $fsize = 3; 
    $fsize_next = 3; 

    $posBigButton = 0;
    $clockTopMargin = 4;
    $timerLabelTopMargin = $bigButHeight * 0.8;

//
// Loop on all devices and display each button, coloured according to
// current S20 state.
//
    foreach ($macs as $mac){
        $devData = $s20Table[$mac];
        $st   = $devData['st'];
        $name = $devData['name'];
        $type = ($st == 0 ? "redbutton" : "greenbutton");
        $style ='style="height:'.$bigButHeight.'vh;top:'.$posBigButton.'vh;"'; 
        
        // display big button
        $bigButton='<button type="submit" name="toMainPage" 
               value="switch_'.$name.'" id="'.$type.'" '.$style.'>'.$name.'</button><br>'."\n"; 
        echo $bigButton;
        
        // overlay timer button for each field
        $posTimerButton = $posBigButton + $clockTopMargin;
        $timerButtonName = 'clock_'.$name;
        $styleTimer = 'style="top:'.$posTimerButton.'vh"';
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
        $timerLabelTop = $posBigButton + $timerLabelTopMargin;
?>
        <div class="counter" id="<?php echo "b".$mac; 
              ?>" style="top:<?php      echo $timerLabelTop ?>vh;
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


                        <div class="next" style="top:<?php      echo $timerLabelTop ?>vh;
                          color:<?php     echo $color; ?>;
                         font-size:<?php echo $fsize_next; ?>vh;"><span> <?php echo $nextS;?></span>   </div>

            
<?php
        }
        $posBigButton  +=$bigButHeight;        
    } 
?>
</center>
<?php
}
?>


