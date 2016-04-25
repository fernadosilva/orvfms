<?php

function displayEditScene($sceneVal,&$sceneList,$s20Table,$myUrl){
?>
<script>
    function refreshVisibility(mac){
        isActive='isActive'+mac;
        onoff='onoff'+mac;
        sec='sec'+mac;
        onoffObj=document.getElementById(onoff);
        secObj=document.getElementById(sec);
        if(document.getElementById(isActive).checked){
            secObj.style.visibility="visible";
            onoffObj.style.visibility="visible";            
        } 
        else{
            secObj.style.visibility="hidden";
            onoffObj.style.visibility="hidden";            
        }
    }
</script>
<?php
    

    $sceneName = "My scene name";  // default scene name is null in case of new/add or unexisting
    if($sceneVal == "add"){
        $sceneTmp = array();
    }
    else if(substr($sceneVal,0,4) =="edit"){
        $sceneName = substr($sceneVal,4);
        if(isset($sceneList[$sceneName])){
            $sceneTmp = $sceneList[$sceneName];
        }
        else{
            $sceneTmp = array();
        }
    }
    else{
        echo "505: Unexpected action code in displayEditScene()";
    }
    $_SESSION['orgSceneName']=$sceneName;
?>    
<div style="text-align:center">
<h2> 
<form action="<?php echo $myUrl ?>" method="post">
<input type = "text" name="newName" value="<?php echo $sceneName; ?>" id="sceneName">
    <br><div id="mayEdit">Edit scene name above; name must be unique.</div>
</h2>
<p>
    <input type="submit" name="toSceneList" value="back" id="backButton"> 
<hr>
<?php
    $macs = array_keys($s20Table);
    sort($macs);
    
    echo '<div class="sceneEdit">'."\n";
    foreach($macs as $mac){
        if(isset($sceneTmp[$mac])){
            $inc = 1;
            $act = $sceneTmp[$mac]['action'];
            $sec = $sceneTmp[$mac]['time'];
        }
        else{
            $inc = 0;
        }
        $name = $s20Table[$mac]['name'];        

        echo '<div class="rowSceneEdit">';
        
        echo '<div class="sceneEditNameCol">';
        echo $name;
        echo '</div>';

        echo '<div class="sceneEditActiveCol">'."\n";
        echo '<input id="isActive'.$mac.'" type="checkBox" name="isActive'.$mac.'"';
        if($inc) echo " checked";
        echo  ' onclick="refreshVisibility(\''.$mac.'\')">';
        echo "\n";
        echo '<label for="isActive'.$mac.'"><span><span></span></span></label>';
        echo '</div>'."\n";
        
        echo '<div class="sceneEditActionCol">'."\n";
        if($inc){
            echo '<div id="onoff'.$mac.'" style="visibility:visible">'."\n";            
        }
        else{
            echo '<div id="onoff'.$mac.'" style="visibility:hidden">'."\n";
            $act = 0;
        }
        echo '<input id="action1'.$mac.'" type="radio" name="action'.$mac.'" value="off"';
        if(!$act) echo " checked";
        echo '>';
        echo '<label for="action1'.$mac.'"><span><span></span></span>OFF</label>'."\n";
        echo '<input id="action2'.$mac.'" type="radio" name="action'.$mac.'" value="on"';
        if($act) echo " checked";
        echo '>';
        echo '<label for="action2'.$mac.'"><span><span></span></span>ON</label>'."\n";
        echo '</div>';
        echo '</div>'; //sceneActionCol;

        echo '<div class="sceneEditSecondsCol">';
        if($inc)
            echo '<div id="sec'.$mac.'" style="visibility:visible">';
        else
            echo '<div id="sec'.$mac.'" style="visibility:hidden">';
        echo '<select name="seconds'.$mac.'">';

        for($i = 0 ; $i < 60; $i++){
            echo '<option value="'.$i.'"';
            if($inc && ($i == $sec))
                echo ' selected="selected"';
            echo '>'.$i.'</option>'."\n";
        }

        echo '</select>';
        echo 'sec';
        echo '</div>'; // id="sec$mac...;
        echo '</div>'; // sceneEditSecondsCol

        
            

        echo '</div>'; // end rowSceneEdit
    }
    echo '</div>'; // end div class sceneEdit
?>


<hr>

<button type="submit" name="toSceneList" value="save" id="doneButton">Save</button>


<?php
}
?>
