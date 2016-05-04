<script>

</script>

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
<br>
<input id="applyToAll" type="checkBox" name="applyToAllAction">   
<label for="applyToAll"><span><span></span></span>Apply to all</label>
<p>
<button type="submit" name="toMainPage" value="procSetup<?php echo $mac ?>" id="doneButton">Done</button>
<hr>
<p><p>
<?php
    $ip = getIpFromMac($mac,$s20Table);
    $_SESSION['s20Table']=$s20Table;
    writeDataFile($s20Table);
    $dev = $s20Table[$mac];
    $time = $dev['time'];
    $serverTime = $dev['serverTime'];
    $tz  = $dev['timeZone'];
    $dst = $dev['dst']; // 1 means DST on
    $serverTz = $dev['serverTimeZone'];
    $serverDst = $dev['serverDst']; 
    $isSync = ($serverDst == $dst) && ($tz == $serverTz);
    if(!$isSync){
        echo '<div style="color:red">Warning: clock seems out of sync!<p></div>';
    }
    echo '<div id="socketTime"></div>';
    echo '<div id="serverTime"></div>';
?>
<p>
<?php
if(!$isSync){
?>
<button type="submit" name="toSetupPage" value="procSync<?php echo $mac ?>" id="syncButton">Sync TZ</button>
<?php
}
    echo "<p><hr>";                                                  
?>
S20 mac address - 
<?php echo formatMac($mac); ?><br>
IP address - 
<?php echo $ip; ?>

<script>
var socketTimeRef = <?php echo $time; ?>;
var serverTimeRef = <?php echo $serverTime; ?>;
var socketTz  = <?php echo $tz; ?>;
var socketDst = <?php echo $dst; ?>;

var serverTz  = <?php echo $serverTz; ?>;
var serverDst = <?php echo $serverDst; ?>;
var t0_ref = new Date().getTime()/1000;


function  displaySocketTime(){
    var now,socketTime,serverTime;
    now = new Date().getTime()/1000;
    socketTime = now - t0_ref + socketTimeRef + 3600 *  (socketTz + socketDst);
    serverTime = now - t0_ref + serverTimeRef + 3600 *  (serverTz + serverDst);
    var socketTimeO = new Date(1000*socketTime);
    var socketTimeS = socketTimeO.toISOString();

    var serverTimeO = new Date(1000*serverTime);
    var serverTimeS = serverTimeO.toISOString();
    //    socketTimeS = socketTimeS.substring(0,24);
    //    serverTimeS = serverTimeS.substring(0,24);

    var msgSckt = "Socket time is " + socketTimeS+", tz="+socketTz+" dst=";
    msgSckt = msgSckt + (socketDst ? "on" : "off");
 
    var msgServ = "Server time is " + serverTimeS+", tz="+serverTz+" dst=";
    msgServ = msgServ + (serverDst ? "on" : "off");

    document.getElementById('socketTime').innerHTML = msgSckt;
    document.getElementById('serverTime').innerHTML = msgServ;
}
setInterval(displaySocketTime,1000); 
</script>


<p>






<div style="margin-top:5vh;">
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
            $msg = "This device seems to be inactive <br>at least since ".$inactiveString;
            $msg = $msg." (".$deltaString."s ago)<p>";
            $msg = $msg."It did not reply to a re-activate command. Is it connected and on-line?<p>";
            $msg =$msg."Last known name: ".$s20Table[$mac]['name'];
            echo $msg;
?>
<hr>
<button type="submit" name="toMainPage" value="procSetupDel<?php echo $mac ?>" id="deleteButton">Delete device</button>
<p><p>
<hr><P>
<button type="submit" name="toMainPage" value="procSetupCancel<?php echo $mac ?>" id="cancelButton">Cancel</button>
<p>
<?php
            if(isset($_POST['toSetupPage']) && (substr($_POST['toSetupPage'],0,4)=="wake")){
                echo "Retry failed<p>";
?>
<button type="submit" name="toSetupPage" value="wake<?php echo $mac ?>" id="cancelButton">Retry again</button>
<?php
            }
            else{
?>
<button type="submit" name="toSetupPage" value="wake<?php echo $mac ?>" id="cancelButton">Retry</button>
<?php
            }
        }
?>
</form>

</div>

<?php
}
?>


