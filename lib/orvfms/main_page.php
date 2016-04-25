<?php

function mkNextActString($nextTimeStamp,$nextAct,$top){
    global $daysOfWeek;
    if($nextAct){
        $color="#00BB00";
        //        $label = " on: ";
    }
    else{
        $color="#EE0000";
        $label = "off: ";
        //
    }
    $label="";
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
    
    $actString = '<div class="next" style="top:'.$top.
               'vh; color:'.$color.';"><span>'.$nextS.'</span>   </div>';

    return $actString;
}

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
    if($ndevs > 0)
        $bigButHeight = 90 / $ndevs;    
    else
        $bigButHeight = 90;

// Countdown & next action time font size 
    $fsize = 3;          // in vh units; must match CSS

// Font size of "next action" fields
    $fszn  = 2.1;        // in vh units; must match CSS 

// Bottom margin between the bottom of the button and counter / next action info
    $botMargin = 0.5;   

    $posBigButton = 0;
    $clockTopMargin = 2;
    $timerLabelTopMargin = $bigButHeight - $fsize - $botMargin;  
    $timerLabelvSpace    = $bigButHeight * 0.10;
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
        $bname = $name;
        
        if(array_key_exists('off',$devData)) {
            if(!array_key_exists('lastOffCheck',$s20Table[$mac])){
                $s20Table[$mac]['lastOffCheck'] = $s20Table[$mac]['off'];
                $_SESSION['s20Table'] = $s20Table;
                $devData = $s20Table[$mac];
            }
            if((time() - $devData['lastOffCheck']) > 24*3600){  // Check once per day
                $s20Table[$mac]['lastOffCheck'] = time();
                $_SESSION['s20Table'] = $s20Table;
                $ip = getIpFromMac($mac,$s20Table);
                if($ip!=0){
                    $s20Table[$mac]['ip'] = $ip;
                    $st = checkStatus($mac,$s20Table);
                    $_SESSION["s20Table"] = $s20Table;
                    if($st >= 0){
                        unset($s20Table[$mac]['off']);
                        $s20Table[$mac]['st'] = $st;
                        $_SESSION['s20Table'] = $s20Table;
                        $devData = $s20Table[$mac];
                    }
                }
            }
        }

        if(array_key_exists('off',$devData)) {
            $type = "graybutton";
            $bname = $name." (?)";
            $val = "check";
        }
        else{
            $val = "switch";
        }

        // display big button
        $bigButton='<button type="submit" name="toMainPage" 
              value="'.$val.$mac.'" id="'.$type.'" '.$style.'>'.$bname.'</button><br>'."\n"; 
        echo $bigButton;        

        if(!array_key_exists('off',$devData)){
            // overlay timer button for each field (countdown timers);
            $posTimerButton = $posBigButton + $clockTopMargin;
            $timerButtonName = 'clock'.$mac;
            $styleTimer = 'style="top:'.$posTimerButton.'vh"';
            $timerButton     = '<input type="submit" name="toCountDownPage" id="countDownButton" 
                value="timer'.$mac.'" '.$styleTimer.'/>'."\n";        
            echo $timerButton;
            
            // overlay clock button for each field
            $clockButton     = '<input type="submit" name="toDetailsPage" id="clockButton" 
                value="clock'.$mac.'" '.$styleTimer.'/>'."\n";        
            echo $clockButton;

            // overlay warning button for each button if tz or dst differ between server and socket
            if(($devData['timeZone'] != $devData['serverTimeZone']) ||
               ($devData['dst'] != $devData['serverDst'])){
                $warningButton     = '<input type="submit" name="toSetupPage" id="warningButton" 
                          value="setup'.$mac.'" '.$styleTimer.'/>'."\n";        
                echo $warningButton;
            }

            // Include field for timer information
            // error_log("\n".$name." ".$devData['timerVal']." action:".$devData['timerAction']."\n");
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
            $timerLabelTop  = $posBigButton  + $timerLabelTopMargin;

?>
        <div class="counter" id="<?php echo $mac; 
              ?>" style="top:<?php      echo $timerLabelTop ?>vh;
                         color:<?php     echo $color; ?>;
                         font-size:<?php echo $fsize; ?>vh;"></div>
<?php
            $next = getAllActions($mac,$s20Table);
            $nd = count($next);
            $maxd = $s20Table[$mac]['next'];
            $fszn = 2.1;
            if($nd > $maxd) $nd = $maxd;
            if($nd > 0){
                // $top = $posBigButton + $timerLabelTopMargin - $nd * $timerLabelvSpace; // OK
                $top = $posBigButton + $bigButHeight - ($nd+1) * $fszn  - $botMargin;
                $actString = '<div class="next" style="top:'.$top.
                           'vh; color:#4C4C4C;"><span>Next:</span>   </div>';
                echo $actString.'\n';
                for($j=0; $j < $nd; $j++){
                    $nextAct  = $next[$j][0];
                    $nextTimeStamp = $next[$j][1];
                    // $top = $posBigButton + $timerLabelTopMargin - ($nd-$j-1) * $timerLabelvSpace; // OK
                    $top = $posBigButton + $bigButHeight - ($nd - $j) * $fszn - $botMargin; 
                    $nextActS = mkNextActString($nextTimeStamp,$nextAct,$top); 
                    echo $nextActS."\n";
                }
            }
        }
        $posBigButton  +=$bigButHeight;        
    } 
/* Overlay find button */
?>

<input type="submit" name="toMainPage" id="findButton" 
                value="find000000000000">

<input type="submit" name="toSceneList" id="sceneButton" 
                value="scene000000000000">

</form>
</center>
<?php
}
?>


