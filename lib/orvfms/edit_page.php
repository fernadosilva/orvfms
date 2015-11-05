<script>
function clearAllFunc(){
    var k;
    var label;
    if(document.getElementById("clearAll").checked){
        for(k=0;k<7;k++){
            label="weekday"+k;
            document.getElementById(label).checked = false;
        }
        document.getElementById("setAll").checked = false;
    }
    updateAllFunc();
}

function setAllFunc(){
    var k;
    var label;
    if(document.getElementById("setAll").checked){
        for(k=0;k<7;k++){
            label="weekday"+k;
            document.getElementById(label).checked = true;
        }
        document.getElementById("clearAll").checked = false;
    }
    updateAllFunc();
}

function updateAllFunc(){
    var k;
    var label;
    var n;
    n = 0;
    for(k=0;k<7;k++){
        label="weekday"+k;
        if(document.getElementById(label).checked) n++;
    }
    if(n == 7){
        document.getElementById("setAll").checked = true;
    }
    else{
        document.getElementById("setAll").checked = false;
        if(n==0){
            document.getElementById("clearAll").checked = true;
        }
        else
            document.getElementById("clearAll").checked = false;
    }
}

</script>

<?php
function displayEditPage($timerName,$editIndex,&$s20Table,$myUrl){
    global $daysOfWeek;    
    $mac = getMacFromName($timerName,$s20Table);
    $details = $_SESSION['details'];
    $nTimers = count($details);
    $thisTimer = $details[$editIndex];
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
    if($editIndex < 0){
        $h = $m = $s = 0;
        $code = "";
        $action = 0;
    }
    else{
        secToHour($thisTimer['time'],$h,$m,$s);
        $recCode = $thisTimer['recCode'];
        $action = $thisTimer['action'];
    }
?>

<div>
<form action="<?php echo $myUrl ?>" method="post">
    <input type="submit" name="toDetailsPage" value="Back" 
        id="backButton"> 
    <div>hh : mm : ss</div>
<div>
<select name="hours">
<?php
    for($i = 0 ; $i < 24; $i++){
        if($i == $h)
            $selected = ' selected="selected"';
        else
            $selected = '';
        echo '<option value="'.$i.'"'.$selected.'>'.$i.'</option>'."\n";
    }
?>
</select>:
<select name="minutes">
<?php
    for($i = 0 ; $i < 60; $i++){
        if($i == $m)
            $selected = ' selected="selected"';
        else
            $selected = '';
        echo '<option value="'.$i.'"'.$selected.'>'.$i.'</option>'."\n";
    }

?>
</select>:
<select name="seconds">
<?php
    for($i = 0 ; $i < 60; $i++){
        if($i == $s)
            $selected = ' selected="selected"';
        else
            $selected = '';
        echo '<option value="'.$i.'"'.$selected.'>'.$i.'</option>'."\n";
    }


?>


</select>
</div>
<p>
<p>


<div>
Action<br>
<input id="action1" type="radio" name="detailAction" value="0" <?php if($action==0) echo "checked";?>>
  <label for="action1"><span><span></span></span>OFF</label>
  <input id="action2" type="radio" name="detailAction" value="1" <?php if($action==1) echo "checked";?>> 
  <label for="action2"><span><span></span></span>ON</label>
</div><p>
<p>
<hr>
<h2>Repeat every:</h2>
<div class="editDetail">

<?php
    $bits = $thisTimer['r'];
    for($k=0; $k <  7; $k++){
        $bit = (int) $bits % 2;
        $bits = (int) ($bits / 2);
        echo '<div class="rowDetail">';
        echo '<div class="weekDayCol">';
        echo $daysOfWeek[$k];
        echo '</div>';
?>
      <div class="checkBoxCol">
      <input type="checkbox" id="weekday<?php echo $k; ?>" 
        name="weekday<?php echo $k; ?>"  <?php if($bit==1) echo "checked"; ?> onclick="updateAllFunc()" > 
      <label for="weekday<?php echo $k; ?>" ><span></span></label>        
      </div>
      </div>
<?php
    }
?>
 <p>
 <div class="rowDetail">
   <div class="weekDayCol"> Everyday </div>
   <div class="checkBoxCol">
      <input type="checkbox" id="setAll" onclick="setAllFunc()">       <label for="setAll"><span></span></label>        
   </div>
 </div>
 <div class="rowDetail">
   <div class="weekDayCol"> Once </div>
   <div class="checkBoxCol">
      <input type="checkbox" id="clearAll" onclick="clearAllFunc()">       <label for="clearAll"><span></span></label>        
   </div>
 </div>
</div>
<hr>
<button type="submit" name="toDetailsPage" value="updateOrAdd" id="doneButton">Done</button>
<input type="hidden" name="name" value="<?php echo $timerName; ?>">
<input type="hidden" name="recCode" value="<?php echo $recCode;  ?>">
</form>
<script>
updateAllFunc();
</script>
<?php
}
?>

