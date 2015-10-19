<?php

function displayMainPage(&$s20Table,$myUrl){
    
    $ndevs = count($s20Table);

 ?>
<center>
<form action="<?php echo $myUrl ?>" method="post">
<?php

// Compute big button height (90 percent of viewport height) 
    $bheight = 90 / $ndevs;

// Compute timer margin
    $tmargin = -$bheight * 0.9;

// Compute timer font size 
    $fsize = $bheight*0.15; 
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
        $bigButton='<input type="submit" name="selected" value="'.$name.'" id="'.$type.'" '.$h.' /><br>'."\n"; 
        echo $bigButton;
        
        // overlay timer button for each field
        $timerButtonName = PREFIX_CODE.$name;
        $styleTimer = 'style="top:'.$posTimerButton.'vh"';
        $posTimerButton+=$bheight;
        $timerButton     = '<input type="submit" name="selected" id="timerButton" value="'.$timerButtonName.'" '.$styleTimer.'/>'."\n";
        echo $timerButton;
        // Include field for timer information
        if($devData['timerVal'] != 0)
            if($devData['timerAction'])
                $color="green";
            else
                $color="#980000";
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
    } 
?>
</center>
<?php
}
?>


