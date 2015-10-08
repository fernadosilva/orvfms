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
        sendActionByDeviceName($name,$s20Table,($st ? 0 : 1));
        $st = checkStatus($mac,$s20Table);
        echo "  ...new status is ".($st ? "ON" : "OFF")."\n\n";
        ob_flush();
    }
    sleep(2);
}


?>
