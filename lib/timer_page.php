<script>
function setActionOff(){
    var action;
    action = document.getElementById("action1");
    action.checked = true;
}
function checkIfValidOn(){
    var actionType;
    actionType = document.getElementById("actionType2");
    if(actionType.checked == true){
        setActionOff();
    }
}
</script>
<?php
function displayTimerPage($timerName,&$s20Table,$myUrl){
?>
<div style="text-align:center">
<h2> <?php echo $timerName ?> </h2>

<hr>

<form action="<?php echo $myUrl ?>" method="post">
<div>
Action<br>
  <input id="action1" type="radio" name="action" value="off"  checked>
  <label for="action1"><span><span></span></span>OFF</label>
  <input id="action2" type="radio" name="action" value="on" 
           onClick="checkIfValidOn()"> 
  <label for="action2"><span><span></span></span>ON</label>
<p>
Action type<br>
  <input id="actionType1" type="radio" name="actionType" value="now" checked> 
  <label for="actionType1"><span><span></span></span>Now</label>
  <input id="actionType2" type="radio" name="actionType" value="switchOn" onclick="setActionOff()"> 
  <label for="actionType2"><span><span></span></span>After switch on</label>
    <br>
</div>
<div>hh   :    mm   :    ss</div>
<div>
<select name="hours">
<?php
    for($i = 0 ; $i < 17; $i++)
        echo '<option value="'.$i.'">'.$i.'</option>'."\n";
?>
</select>:
<select name="minutes">
<?php
    for($i = 0 ; $i < 60; $i++)
        echo '<option value="'.$i.'">'.$i.'</option>'."\n";
?>
</select>:
<select name="seconds">
<?php
    for($i = 0 ; $i < 60; $i++)
        echo '<option value="'.$i.'">'.$i.'</option>'."\n";
?>

    <input type="hidden" name="name" value="<?php echo $timerName ?>">
</select>
</div>
<p>
<br>
<input type="submit" name="buttonPressed" value="Set countdown" 
    id="timerPageButton"><p>
<input type="submit" name="buttonPressed" value="Clear countdown"
    id="timerPageButton"><br>

<?php
    $mac = getMacFromName($timerName,$s20Table);
    $swVal = $s20Table[$mac]['switchOffTimer'];
    if($swVal > 0){
        echo "<hr>";
        $msg = "<p>Automatic switch off timer set to: ".secToHourString($swVal)."<p>";
        echo $msg; 
?>
<input type="submit" name="buttonPressed" value="Clear automatic switch off"
id="timerPageButton"><p><p>        
<?php        
    }
?>
</form>
</div>
<?php
}
?>
