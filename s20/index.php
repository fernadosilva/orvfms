<?php
session_start();
?>
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
   YOUR LOCAL CONFIGURATION.                             */


include( "../lib/orvfms/orvfms.php"); 

$myUrl = htmlspecialchars($_SERVER["PHP_SELF"]);
if(DEBUG)
    print_r($_SESSION);

if(isset($_SESSION["s20Table"])) {
    $s20Table = $_SESSION["s20Table"];
}

if(isset($_SESSION["time_ref"])) 
    $time_ref = $_SESSION["time_ref"];
else
    $time_ref = 0;

if(isset($_SESSION["s20Table"]) && isset($_SESSION["devNumber"]) &&
   (count($s20Table) == $_SESSION["devNumber"]) 
   && (count($s20Table)>0) && ((time()-$time_ref < 180))){
    $s20Table = updateAllStatus($s20Table);  
    if(DEBUG)
        error_log("Session restarted; only status update\n");
}
else{
    $time_ref = time(); 
    $s20Table=initS20Data();    
    $ndev=count($s20Table);
    $_SESSION["devNumber"]=$ndev;
    $_SESSION["time_ref"]=$time_ref;
    if(DEBUG)
        error_log("New session: S20 data initialized\n");
}


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $location = $_POST['selected'];
    $mac = getMacFromName($location,$s20Table);
    $st = $s20Table[$mac]['st'];
    $newSt = actionAndCheck($mac,($st==0 ? 1 : 0),$s20Table);
    $s20Table[$mac]['st']=$newSt;
}
$_SESSION["s20Table"]=$s20Table;


$ndevs = count($s20Table);

?>
<center>
<form action="<?php echo $myUrl ?>" method="post">
<?php

// Compute button height 
$bheight = intval(100 / ($ndevs) * 0.85);

//
// Sort array (in this case, by mac address), such that data is displayed in a deterministic sequence
//
$macs = array_keys($s20Table);
sort($macs);
//
// Loop on all devices and display each button, coloured according to
// current S20 state.
//
foreach ($macs as $mac){
    $devData = $s20Table[$mac];
    $st   = $devData['st'];
    $name = $devData['name'];
    $type = ($st == 0 ? "redbutton" : "greenbutton");
    $h ='style="height:'.$bheight.'vh;"'; 
    $myButton='<input type="submit" name="selected" value="'.$name.'" id="'.$type.'" '.$h.' /><br>'."\n"; 
    echo $myButton;
} 

?>
</center>
</body>
</html>
