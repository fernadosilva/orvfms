<?php

function processSetup($mac,&$s20Table,$actionValue){
    if($actionValue == "procSetup"){
        $oldName = $s20Table[$mac]['name'];
        if(isset($_POST["newName"])){
            $newName = $_POST["newName"];
            if($newName != $oldName){
                $newName = setName($mac,$newName,$s20Table);
                if( $newName != ""){
                    $s20Table[$mac]['name'] = $newName;
                    $_SESSION['s20Table'] = $s20Table;
                    writeDataFile($s20Table);
                }
            }
        }
        $nnext = (int) $_POST['numberOfNextEvents'];
        if(!isset($_POST['applyToAllAction'])){
            $s20Table[$mac]['next'] = $nnext;
        }
        else{
            foreach($s20Table as $macAux => $devAux){
                $s20Table[$macAux]['next'] = $nnext;
            }
        }
        writeDataFile($s20Table);
        $_SESSION['s20Table'] = $s20Table;


    }
    else if ($actionValue == "procSetupCancel"){
        // OK, nothing here
    }
    else if ($actionValue == "procSetupDel"){
        unset($s20Table[$mac]);
        writeDataFile($s20Table);
        $_SESSION['s20Table'] = $s20Table;
    }    
    else{
        error_log("process setup: unknow action: >".$actionValue."<\n");
    }
}

?>