<?php
function deleteScene($scene,&$sceneList){
    if(isset($sceneList[$scene])){
        unset($sceneList[$scene]);
        writeSceneList($sceneList);
        $_SESSION['sceneList']=$sceneList;
    }
}

function saveScene(&$sceneList,$s20Table){
    $newName = $_POST['newName'];
    $oldName = $_SESSION['orgSceneName'];
    $macs = array_keys($s20Table);
    $tmpScene = array();
//    displayDebug(120,$_POST);
    foreach($macs as $mac){
        $isActive = "isActive".$mac;
        if(isset($_POST[$isActive])){
            $secStr = "seconds".$mac;
            $actStr = "action".$mac;
            $sec = $_POST[$secStr];
            $act = ($_POST[$actStr] == "on" ? 1 : 0);
            $tmpScene[$mac]['time'] = $sec;
            $tmpScene[$mac]['action'] = $act;
        }
    }
    $sceneList[$newName] = $tmpScene;
    if($newName != $oldName){
    }
    writeSceneList($sceneList);
    $_SESSION['sceneList']=$sceneList;
}
?>