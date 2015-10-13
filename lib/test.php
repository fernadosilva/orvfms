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
    
    $h=0;$m=0;$s=10;

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
            echo sprintf("Current=%02d:%02d:%02d To => %s, curr = %s\n",
                         $h,$m,$s,actionToTxt($action),actionToTxt($st));
        }
        ob_flush();
        sleep(2);
    }
}
echo "Test finished, everything seems OK\n";

?>
