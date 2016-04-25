<?php
function displaySceneListPage($sceneList,$s20Table,$myUrl){

?>


<div style="text-align:center">
    

<h2> 
Scene list
</h2>



<hr>
<?php


    echo '<form action="'.$myUrl.'" method="post">';
?>

    <input type="submit" name="toMainPage" value="back<?php echo NULL_MAC; ?>" 
        id="backButton"> 
    <button type="submit" name="toSceneEdit" value="add" 
        id="addButton" >Add</button>
    
<?php
    echo '<div class="scene">'."\n";
    
    foreach($sceneList as $name => $sceneVal){
        echo '<div class="rowScene">';
        echo '<div class="buttonSceneCol">';
        echo '<button type="submit" name="toMainPage" value="sceneAct'.$name.NULL_MAC.'" id="doneButton">'.$name.'</button>'; 

        echo '</div>';

        echo '<div class="editSceneCol">';
        echo '<button type="submit" name="toSceneEdit" value="edit'.$name.'"
                  id="editButton">EditScene</button>';
        echo '</div>';  // editSceneCol


        echo '<div class="delSceneCol">';
        echo '<button type="submit" name="toSceneList" value="del'.$name.'" 
                  id="delButton">DeleteScene</button>';

        echo '</div>';   //delSceneCol

        echo '</div>'; // end div rowScene        
    }
    echo '</div>'; // end overall div scene
    echo "</form>\n"; // end form

}
?>

