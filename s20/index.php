<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<META HTTP-EQUIV="Refresh" CONTENT="60">
<title>
S20 remote
</title>

<!-- UPDATE THE PATH OF THE FILE orvfms.css BELOW TO MATCH
     YOUR LOCAL CONFIGURATION.                       -->
<link rel="stylesheet" type="text/css" href="../css/orvfms.css"> 
</head>
<body>

<?php
/*************************************************************************
*  Copyright (C) 2015 by Fernando M. Silva   fcr@netcabo.pt             *
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
  This program was developed independenntly and it is not
  supported or endorsed in any way by Orvibo (C).

  This page implements a web interface to control Orvibo S20 sockets 
  attached to the local network. Major functions are implemented in the
  orvfms.php library. 

  The web interface provides status report of all S20 attached to the 
  network and supports ON/OFF actions of the detected devices. It features
  a responsive behavior to changing viewport sizes, including smartphones. It 
  divides the viewport in N horizontal buttons, each one labeled with the 
  name automatically retrieved from the connected S20s. Each button is 
  shown in green or red according to the current S20 state (green = ON).  
  
  Note: adjust the include line orvfms.php below to the correct path, as well
  as the location of the CSS  orvfms.css in the <head> section above.  
*/

/* UPDATE THE PATH OF THE FILE orvfms.php BELOW TO MATCH
   YOUR LOCAL CONFIGURATION.                             -->

include( "../lib/orvfms/orvfms.php"); 

session_start();
$myUrl = htmlspecialchars($_SERVER["PHP_SELF"]);
#print_r($_SESSION);


if(isset($_SESSION["allS20Data"]) && isset($_SESSION["devNumber"])){
    $allS20Data = $_SESSION["allS20Data"];
    if(count($allS20Data) == $_SESSION["devNumber"]){
            $allS20Data = updateAllStatus($allS20Data);  
    }  
    else{
        $allS20Data=initS20Data();    
        $ndev=count($allS20Data);
        $_SESSION["devNumber"]=$ndev;
    }
}
else{
    $allS20Data=initS20Data();    
    $ndev=count($allS20Data);
    $_SESSION["devNumber"]=$ndev;
}


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $location = $_POST['selected'];
    $mac = getMacFromName($location,$allS20Data);
    $st = $allS20Data[$mac]['st'];
    $newSt = actionAndCheck($mac,$allS20Data,($st==0 ? 1 : 0));
    $allS20Data[$mac]['st']=$newSt;
}
$_SESSION["allS20Data"]=$allS20Data;


$ndevs = count($allS20Data);

?>
<center>
<form action="<?php echo $myUrl ?>" method="post">
<?php

// Compute button height 
$bheight = intval(100 / ($ndevs) * 0.85);

//
// Sort array (in this case, by mac address), such that data is displayed in a deterministic sequence
//
$macs = array_keys($allS20Data);
sort($macs);
//
// Loop on all devices
//
foreach ($macs as $mac){
    $devData = $allS20Data[$mac];
    $st   = $devData['st'];
    $name = $devData['name'];
    if($st == 0){
        $type="redbutton";
    }
    else{
        $type = "greenbutton";
    }
    $h ='style="height:'.$bheight.'vh;"'; 
    $myButton='<input type="submit" name="selected" value="'.$name.'" id="'.$type.'" '.$h.' /><br>'."\n"; 
    echo $myButton;
} 

?>
</center>
</body>
</html>
