<?php

function timerSettings(&$s20Table){
    $actionType = $_POST['actionType'];
    $name       = $_POST['name'];
    $mac = getMacFromName($name,$s20Table);

    if($_POST["buttonPressed"] != "Clear countdown"){
        $action     = $_POST['action'];
        $h          = $_POST['hours'];
        $m          = $_POST['minutes'];
        $s          = $_POST['seconds'];
    }
    else{
        $h = $m = $s = 0;
        $action = "off";
    }
    if(($actionType == "now") || 
       $_POST["buttonPressed"] == "Clear countdown"){
        // Set
        Settimer($mac,$h,$m,$s,($action == "on" ? 1 : 0),$s20Table);
        // Confirm
        $s20Table[$mac]['timerVal'] = checkTimerSec($mac,$s20Table,$action);
        $s20Table[$mac]['timerAction'] = $action;
    }
    else{
        print "<h1> Not implemented yet </h1>";
    }
    

}

?>