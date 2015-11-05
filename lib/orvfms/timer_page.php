<?php
function displayTimerPage($timerName,&$s20Table,$myUrl){
    $mac = getMacFromName($timerName,$s20Table);
    $swVal = $s20Table[$mac]['switchOffTimer'];
    $timerVal = $s20Table[$mac]['timerVal'];
    $timerAction = $s20Table[$mac]['timerAction'];
?>

<script>
var swVal = <?php echo $swVal ?>;
var timerVal = <?php echo $timerVal ?>;
var timerAction = <?php echo $timerAction ?>;
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
var timerEnd;
var countDownId;
function displayCountDownTimer(){
    var delta;
    var countDownStr;
    delta = Math.round(timerEnd - new Date().getTime()/1000);
    if(delta < 0){
        clearInterval(countDownId);
        countDownStr = "hh:mm:ss";
        document.getElementById("countDown").style.color = "black";
    }
    else{
        countDownStr = convToString(delta);
        if(timerAction)
            document.getElementById("countDown").style.color = "green";
        else
            document.getElementById("countDown").style.color = "red";
    }
    document.getElementById("countDown").innerHTML = countDownStr;
}
function initTimerPageScripts(){
    document.getElementById("countDown").innerHTML = "hh:mm:ss";
    if(timerVal != 0){
        timerEnd = new Date().getTime()/1000 + timerVal;
        countDownId = setInterval(displayCountDownTimer,1000)
    }
}
</script>

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

<form action="<?php echo $myUrl ?>" method="post">
<input type="submit" name="toMainPage" value="back" 
    id="backButton">
<input type="submit" name="toDetailsPage" value="details" 
    id="menuButton">


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
</div><p>
<div id="countDown"></div>
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
<button type="submit" name="toMainPage" value="setCountdown" 
    id="timerPageButton">Set countdown</button><p>
<button type="submit" name="toMainPage" value="clearCountdown"
    id="timerPageButton">Clear countdown</button><br>

<?php
    if($swVal > 0){
        echo "<hr>";
        $msg = "<p>Automatic switch off timer set to: ".secToHourString($swVal)."<p>";
        echo $msg; 
?>
<button type="submit" name="toMainPage" value="clearSwitchOff" id="timerPageButton">
Clear automatic switch off<br>
</button><p><p>        
<?php        
    }
?>
</form>
</div>
<script>
    initTimerPageScripts();
</script>
<?php
}
?>
