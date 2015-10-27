<?php

function timerSettings(&$s20Table,$actionValue){
    //
    // Implements  the actions selected in the web timer page.
    //
    $actionType = $_POST['actionType'];
    $name       = $_POST['name'];
    $mac = getMacFromName($name,$s20Table);

    if(($actionValue == "clearSwitchOff")  ||
       ($actionValue == "clearCountdown")){
        $h = $m = $s = 0;
        $act = $s20Table[$mac]['st'];
    }
    else{
        $action     = $_POST['action'];
        $h          = $_POST['hours'];
        $m          = $_POST['minutes'];
        $s          = $_POST['seconds'];
        $act = ($action == "on" ? 1 : 0);
    }
    $sec = $h * 3600 + $m * 60 + $s;

    if(($actionValue == "clearCountdown") ||
       (($actionValue == "setCountdown") && ($actionType == "now"))){
        //
        // Update regular countdown timer
        //
        // Set
        setTimer($mac,$h,$m,$s,$act,$s20Table);
        // Confirm
        $s20Table[$mac]['timerVal'] = checkTimerSec($mac,$s20Table,$action);
        $s20Table[$mac]['timerAction'] = $action;
        if(($s20Table[$mac]['timerVal'] != $sec) ||
           ($s20Table[$mac]['timerAction'] != $act))
            return 1;
    }
    else{
        // 
        // Update automatic switch off after on timer
        //
        if(setSwitchOffTimer($mac,$sec,$s20Table) != $sec)
            return 1;
    }
    return 0;
}

?>