<?php
/*************************************************************************
*  Copyright (C) 2015 by Fernando M. Silva
*                                                                       *
*  This program is free software; you can redistribute it and/or modify *
*  it under the terms of the GNU General Public License as published by *
*  the Free Software Foundation; either version 3 of the License, or    *
*  (at your option) any later version.                                  *
*                                                                       *
*  This program is distributed in the hope that it will be useful,      *
*  but WITHOUT ANY WARRANTY; without even the implied warranty of       *
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the        *
*  GNU General Public License for more details.                         *
*                                                                       *
*  You should have received a copy of the GNU General Public License    *
*  along with this program.  If not, see <http://www.gnu.org/licenses/>.*
*************************************************************************/

/*

  This program was developed independently and it is not
  supported or endorsed in any way by Orvibo (C).  

  General test program of intended to test all functions
  in orvfms.php and util.php

  It loops through all s20 plugs and switch them off and on.

  At the end it tests the count down timer in one of the devices,
  counting twice for 10 seconds, and toggling the result at the
  end.

  PLEASE TAKE INTO ACCOUNT POSSIBLE SAFETY ISSUES  of switching on/off! 
  Uplug any device or appliance that nay be affected by this test cycle!
  
  This is intended to be run from the command line for test purposes. Use 

  prompt>  php-cgi test.php

*/

include("orvfms.php");

$s20Table = initS20Data(); 
if(count($s20Table) == 0){
    echo " No sockets found\n\n";
    echo " Please check if all sockets are on-line and assure that they\n";
    echo " they are not locked (check WiWo app -> select socket -> more -> Advanced\n\n";
    echo " This code does not support locked or password protected devices\n\n\n";
    exit(0);
}

$s20Table = updateAllStatus($s20Table);   // Update all status (not required, just for test, 
                                              //since immediately after init they   
                                              // are already uptodate)
// Print the full array

print_r($s20Table);   

//
// Loop over all switch and toggle twice the status
//

for($i = 0; $i < 2; $i++){
    foreach($s20Table as $mac => $devData){
        $name   = $devData['name'];    
        $ip     = $devData['ip'];
        $st = checkStatus($mac,$s20Table);
        echo "Status of S20 named >".$name. "< (mac=".$mac.", IP=".$ip.") is ".($st ? "ON" : "OFF")."\n";
        echo "  ...Turning it ".($st ? "OFF" : "ON")."\n";
        sendActionByDeviceName($name,($st ? 0 : 1),$s20Table);
        $st = checkStatus($mac,$s20Table);
        echo "  ...new status is ".($st ? "ON" : "OFF")."\n\n";
        ob_flush();
    }
    sleep(2);
}

for($i = 0; $i < 2 ; $i++){
//
// Timer test; toggle first device twice, 10 seconds
//
    $mac = array_keys($s20Table)[0];
    $ip=$s20Table[$mac]['ip'];
    $name=$s20Table[$mac]['name'];

    echo "\n\n ***  Testing timer in ".$name." *** \n\n";
    
    $st = checkStatus($mac,$s20Table);
    
    echo " Initital status: ".actionToTxt($st)."\n\n";


    if($st){
        $action = 0;
    }
    else{
        $action = 1;
    }
    
    $h=0;$m=0;$s=5;

    echo "setting timer: ".$name.' -> '.actionToTxt($action)." Time=".
                          sprintf("%02d:%02d:%02d\n",$h,$m,$s);
    
    if(!setTimer($mac,$h,$m,$s,$action,$s20Table)){
        echo "Set timer succeed\n";
    }
    else{
        echo "Some problem on set timer\n";
    }
    //
    //Let us check
    //

    while(1){
        $timer = checkTimer($mac,$s20Table,$h,$m,$s,$action);
        $st    = checkStatus($mac,$s20Table);
        if(!$timer){
            echo "Timer is off, status = ".actionToTxt($st)."\n";
            break;
        }
        else{        
            echo sprintf("%02d:%02d:%02d to => %s, current is %s\n",
                         $h,$m,$s,actionToTxt($action),actionToTxt($st));
        }
        ob_flush();
        sleep(2);
    }
}

//
// Test automatic switch off after on first device.
//
echo "\n\n\nTesting automatic switch off after on using switch ".$name."\n";
$initValue = getSwitchOffTimer($mac,$s20Table);

$setOff = 1800; 
$res=setSwitchOffTimer($mac,$setOff,$s20Table);
if($res == $setOff){
    echo "\nSetting automatic switch off timer to ".$setOff."s OK\n";
}
else{
    echo "\nSet automatic switch off timer failed\n";
}

$setOff = 0;
$res = setSwitchOffTimer($mac,$setOff,$s20Table);
if($res == $setOff){
    echo "\nResetting automatic switch off timer OK\n";
}
else{
    echo "\nReset automatic switch off timer ".$name." failed\n";
}

// Program again the initial value

$res=setSwitchOffTimer($mac,$initValue,$s20Table);
echo "\nSetting automatic switch off timer ".$name." to initial value (".$res."s)\n\n\n"; 


echo " ...Testing local time (note: at time of initialization, may be delayed by 20s or more)\n\n";

$k=0;
foreach($s20Table as $mac => $dev){
    $k++;
    echo "\n\n".$k.".\n";
    $name = $dev['name'];
    $time = $dev['time'];
    $serverTime = $dev['serverTime'];
    $tzS = $dev['timeZoneSet'];
    $tz = $dev['timeZone'];
    
    echo sprintf(": %10s<",$name)." => socket time = ".date("c",$time).
                                    " TzSet= ".$tzS." Tz= ".$tz."\n"; 
    echo sprintf(": %10s<",$name)." => server time = ".date("c",$serverTime)."\n";
}



echo "\n\nTest finished, check if everything seems OK\n";

?>
