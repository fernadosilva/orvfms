<script>
function setActionOff(){
    var x;
    x = document.getElementsByName("action");
    x.value = "off";
    console.log("xpto\n");
}
</script>
<?php
function displayTimerPage($timerName,&$s20Table,$myUrl){
?>
<h1> <?php echo $timerName ?> </h1>
<form action="<?php echo $myUrl ?>" method="post">
<div>
Action<br>
  <input type="radio" name="action" value="off" checked> OFF
  <input type="radio" name="action" value="on"> ON<p>
Action type<br>
  <input type="radio" name="actionType" value="now" checked> Now
  <input type="radio" name="actionType" value="" onclick="setActionOff()"> After switched on<br>
<div>
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
<input type="submit" name="buttonPressed" value="Set Countdown"><p>
<input type="submit" name="buttonPressed" value="Clear countdown"><p>


<?php
    $mac = getMacFromName($timerName,$s20Table);
    $swVal = $s20Table[$mac]['switchOffTimer'];
    if($swVal > 0){
        $msg = "<p>Automatic switch off timer set to: ".secToHourString($swVal)."<p>";
        echo $msg; 
?>
<input type="submit" value="Clear automatic switch off timer"><p>        
<?php        
    }
}
?>
</form>