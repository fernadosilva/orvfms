<?php

function processSetup($mac,&$s20Table,$actionValue){
    if($actionValue == "procSetup"){
        $nnext = (int) $_POST['numberOfNextEvents'];
        $s20Table[$mac]['next'] = $nnext;
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