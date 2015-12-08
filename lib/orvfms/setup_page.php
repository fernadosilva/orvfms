
<?php
function displaySetupPage($mac,&$s20Table,$myUrl){
    global $daysOfWeek;    

    $devData = $s20Table[$mac];
    $timerName = $devData['name'];
?>

<div style="text-align:center">
<h2> 
<form action="<?php echo $myUrl ?>" method="post">
<?php
    if(array_key_exists('off',$s20Table[$mac])){
        echo $timerName;  
        $isActive = 0;
    }
    else{
        $isActive = 1;
?>

        <input type = "text" name="newName" value="<?php echo $timerName; ?>" id="inputName">
<?php
    }

    if(isset($devData['st']) && ($devData['st'] >= 0) && !array_key_exists('off',$s20Table[$mac])){
        $stDisplay = ($devData['st'] ? "greenCircle100px.png" : "redCircle100px.png");
        echo '<img src="'.IMG_PATH.
                         $stDisplay
                         .'" style="width:0.8em;position:relative;top:0.1em;left:0.3em;">';    

    }
    $nnext = $devData['next'];

    if($isActive){
        echo '<br><div id="mayEdit">Socket name above is editable</div>';
    }
?> 
</h2>
<p>
<hr>





    <input type="submit" name="toMainPage" value="back<?php echo $mac ?>"  
    id="backButton"> 

<?php
    if($isActive){
?>

<div>
        Number of next events displayed in main page for this timer: 
<select name="numberOfNextEvents">        
<?php

        for($i = 0 ; $i < 8; $i++){
            echo '<option value="'.$i.'"'.($nnext==$i ? ' selected="selected"' : ' ').'>'.$i.'</option>'."\n";
        }
?>
</select>

<p>
<hr>
<?php
    $ip = getIpFromMac($mac,$s20Table);
    $dev = $s20Table[$mac];
    $time = $dev['time'];
    $serverTime = $dev['serverTime'];
    $tz = $dev['timeZone'];
    $serverTzS = date_default_timezone_get();
    $serverTz  =  timezone_open ($serverTzS);
    $serverTzOffset = $serverTz -> getOffset(new DateTime());
    echo '<div id="socketTime"></div>';
    echo '<div id="serverTime"></div>';
    echo "<hr>";                                                  
?>
S20 mac address - 
<?php echo formatMac($mac); ?>
<hr>
<script>
var socketTimeRef = <?php echo $time; ?>;
var serverTimeRef = <?php echo $serverTime; ?>;
var socketTz  = <?php echo $tz; ?>;
var serverTz  = <?php echo $serverTzOffset; ?>;
var t0_ref = new Date().getTime()/1000;


function  displaySocketTime(){
    var now,socketTime,serverTime;
    now = new Date().getTime()/1000;
    socketTime = now - t0_ref + socketTimeRef;
    serverTime = now - t0_ref + serverTimeRef;
    var socketTimeO = new Date(1000*socketTime);
    var serverTimeO = new Date(1000*serverTime);
    var socketTimeS = socketTimeO.toString();
    var serverTimeS = serverTimeO.toString();
    socketTimeS = socketTimeS.substring(0,24);
    serverTimeS = serverTimeS.substring(0,24);
    var msgSckt = "Socket time is " + socketTimeS+", tz="+socketTz;
    var msgServ = "Server time is " + serverTimeS+", tz="+serverTz;
    document.getElementById('socketTime').innerHTML = msgSckt;
    document.getElementById('serverTime').innerHTML = msgServ;
}
setInterval(displaySocketTime,1000); 
</script>


<p>

<button type="submit" name="toMainPage" value="procSetup<?php echo $mac ?>" id="doneButton">Done</button>




<div style="margin-top:10vh;">
<hr>
Delete device from the system<p>
<button type="submit" name="toMainPage" value="procSetupDel<?php echo $mac ?>" id="deleteButton">Delete device</button>
<hr>
</div>
<?php
        }
        else{

            $inactiveTimeStamp = $s20Table[$mac]['off'];
            $inactiveString = gmdate("D M j G:i:s T Y",$inactiveTimeStamp);

            $now = time();
            $delta = $now - $inactiveTimeStamp;
            $deltaString = secToHourString($delta);
            $msg = "This device is inactive since ".$inactiveString;
            $msg = $msg." (".$deltaString."s ago)<p>";
            $msg = $msg."It did not reply to a re-activate command. Is it connected and on-line?<p>";
            echo $msg;
?>
<hr>
<button type="submit" name="toMainPage" value="procSetupDel<?php echo $mac ?>" id="deleteButton">Delete device</button>
<p>
<button type="submit" name="toMainPage" value="procSetupCancel<?php echo $mac ?>" id="cancelButton">Cancel</button>


<?php
        }
?>
</form>

</div>

<?php
}
?>


