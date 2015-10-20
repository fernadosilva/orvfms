<script>
<?php
echo "var timerId;\n";
$macs = array_keys($s20Table);
$nCounters = 0;
$nSw = 0;
foreach ($macs as $mac) {
    $val = $s20Table[$mac]['timerVal'];
    $swVal  = $s20Table[$mac]['switchOffTimer'];
    if($val > 0){
        $id = 'b'.$mac;
        $action = $s20Table[$mac]['timerAction'];
        if($nCounters++== 0){
            $timerNames='var timerNames = ["b'.$mac.'"';;
            $timerVals = "var timerVals  = [".$val;
            $timerActs = "var timerAct  = [".$action;
        }
        else{
            $timerNames=$timerNames.',"b'.$mac.'"';
            $timerVals =$timerVals.",".$val;
            $timerActs =$timerActs.",".$action;
        }
    }
    if($swVal > 0){
        if($nSw++== 0){
            $switchOff = "var switchOff  = [".$swVal;
            $swNames='var swNames = ["b'.$mac.'"';;
        }
        else{
            $switchOff=$switchOff.','.$swVal;
            $swNames=$swNames.',"b'.$mac.'"';
        }
    }


}

echo "var nCounters=".$nCounters.";\n";
echo "var nSw=".$nSw.";\n";
if($nCounters > 0){
    $timerNames = $timerNames.'];'."\n";
    $timerVals  = $timerVals."];\n";            
    $timerActs  = $timerActs."];\n";    
    echo $timerNames;
    echo $timerVals;
    echo $timerActs;
    echo "var nTimers=".$nCounters.";\n";
}
if($nSw > 0){
    $switchOff = $switchOff."]\n";
    $swNames   = $swNames."]\n";
    echo $switchOff;
    echo $swNames;
}

?>
      function initTimers(){
          var k;
          for(k = 0; k < nTimers;k++){
              timerVals[k] += new Date().getTime()/1000+0.25; // convert seconds to the time the counter  will expire. 
              // add 250ms of toerance such that the count down and 
              // refresh of the page 
              // happens a little bit after the s20 action.
          }
      }
      function fillSwitchOffTimers(){
          var k;
          var t;
          var name,val;
          for(k=0;k<nSw;k++){
              name = swNames[k];
              val  = switchOff[k];
              if(!((nCounters > 0) && (timerNames.indexOf(name) != -1))){
                  t = convToString(val);
                  document.getElementById(name).innerHTML = t;
              }
          }
      }
      function refreshCounter(){
          var k;
          var currentTime,delta;
          currentTime = new Date().getTime()/1000;
          for(k = 0; k < nTimers;k++){
              delta = Math.round(timerVals[k] - currentTime);
              if(delta<0){
                  clearInterval(timerId);
                  location.reload(true);
              }
              else{
                  t = convToString(delta);
                  document.getElementById(timerNames[k]).innerHTML = t;
              }
          } 
      }
<?php
if($nSw > 0)
    echo "fillSwitchOffTimers()\n";
if($nCounters > 0){ 
?>   
    initTimers();
    refreshCounter();
    timerId = setInterval(refreshCounter,250); 
<?php
}
?>
</script>

