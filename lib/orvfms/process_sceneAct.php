<?php
function processSceneAct($sceneList,&$s20Table,$sceneName){
    $actList = $sceneList[$sceneName];
    foreach($actList as $mac => $macAction){
        $seconds = $macAction['time']+1;
        $action  = $macAction['action'];
        $s20Table[$mac]['timerVal']=$seconds;        
        $s20Table[$mac]['timerAction']=$action;       
        $_SESSION['s20Table'] = $s20Table;
        setTimer($mac,0,0,$seconds,$action,$s20Table);
    }    
}
?>