<?php
function displayDetailsPage($timerName,&$s20Table,$myUrl){
    global $daysOfWeek,$months;

    $mac = getMacFromName($timerName,$s20Table);
?>


<div style="text-align:center">
    

<h2> 
<?php 
    echo $timerName;  
    echo '<img src="'.IMG_PATH.
          ($s20Table[$mac]['st'] ? "greenCircle100px.png" : "redCircle100px.png")
                     .'" style="width:0.8em;position:relative;top:0.1em;left:0.3em;">';    
?> 
 </h2>



<hr>
<?php
    $allTimers = getAndParseTimerTable($mac,$s20Table);
    $_SESSION['details'] = $allTimers;
    $nTimers  = count($allTimers);
    echo '<form action="'.$myUrl.'" method="post">';
?>
    <input type="submit" name="toMainPage" value="Back" 
        id="backButton"> 
    <button type="submit" name="toEditPage" value="add" 
        id="addButton" >Add</button>

<?php
    echo '<div class="details">'."\n";
    for($i = 0; $i < $nTimers ; $i++){
        echo '<div class="row">';
        $details = $allTimers[$i];
        $hour = secToHourString($details['time']);

        echo '<div class="hour">'.$hour.'</div>';

        echo '<div class="editCol">';
        echo '<button type="submit" name="toEditPage" value="edit'.$i.'" 
                  id="editButton">Edit</button>';
        echo '</div>';


        echo '<div class="onoff">';
        echo ($details['action'] ? "on " : "off");
        echo '<img src="'.IMG_PATH.
                         ($details['action'] ? 
                          "greenCircle100px.png" : "redCircle100px.png")
 .'" style="width:0.8em;position:relative;top:0.1em;left:0.3em;">';
        echo '</div>';
        $bits = $details['r'];
        //        echo $details['r']." ".$bits." ";
        $first = 1;
        echo '<div class="daysOfWeek">';
        if($bits > 0){
            for($k=0; $k < 7; $k++){
                $bit = $bits % 2;
                $bits = (int) ($bits / 2);
                if($bit){
                    if(!$first) {
                        echo ",";                    
                    }
                    echo substr($daysOfWeek[$k],0,2);
                    $first = 0;
                }
            }
        }
        else{
            echo $details['d'] . "  ". $months[$details['m']-1]. " " . $details['y'];
        }
        // echo " :".$details['recCode'];
        echo "</div>";
        echo '<div class="delCol">';
        echo '<button type="submit" name="toDetailsPage" value="del_'.$details['recCode'].'" 
                  id="delButton">Delete</button>';
        echo "</div>\n";
        echo "</div>\n";
    }

    echo "</div>\n";

    echo '<input type="hidden" name="name" value="'.$timerName.'">';
    echo "<p><p><p>";
    echo '<button type="submit" name="toMainPage" value="done" id="doneButton">Done</button>'; 
    echo "</form>\n";

  
    
?>

<?php
}
?>

