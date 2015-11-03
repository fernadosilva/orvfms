<?php
function getRepeatFromWeekDays(){
    $bit = 1;
    $tot = 0;
    for($k = 0 ; $k < 7; $k++){
        $wd ="weekday".$k;
        if(isset($_POST[$wd])){
            $tot += $bit;
        }
        $bit *= 2;
    }
    if($tot != 0) $tot+=128;
    return $tot;
}
function editProcess($timerName,&$s20Table){    
    $recCode = $_POST['recCode'];
    $mac = getMacFromName($timerName,$s20Table);
    $h = $_POST['hours'];
    $m = $_POST['minutes'];
    $s = $_POST['seconds'];
    $action = $_POST['detailAction'];
    $rep = getRepeatFromWeekDays();
    if($recCode == ""){
        addTimer($mac,$h,$m,$s,$action,$rep,$s20Table);
    }
    else{
        updTimer($mac,$recCode,$h,$m,$s,$action,$rep,$s20Table);
    }
}

function delProcess($timerName,$recCode,&$s20Table){
    $mac = getMacFromName($timerName,$s20Table);
    delTimer($mac,$recCode,$s20Table);
}        
?>