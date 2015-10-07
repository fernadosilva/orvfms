<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<META HTTP-EQUIV="Refresh" CONTENT="60">
<title>
S20 remote
</title>

<!-- CHECK THE LOCATION OF THE mystyle.css -->

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
  This is a generic php example of an application and 
  web interface that may be installed in a local server to
  control Orvibo S20 sockets, using the orvfms library
  supplied jointly. This library only supports 
  ON/OFF actions

  This program was developed independenntly and it is not
  supported or endorsed in any way by Orvibo (C).

  This interface, while just a quite simple example, is fully functional 
  and it was conceived to be responsive in any web viewport size, in particular
  smartphones. It divides the viewport in N horizontal buttons, each one with 
  the name of each S20 automatically found in the local network. Each button
  is green or red according to the current S20 state (green = ON)   
  
  Adjust the include below to the correct location of your orvfms.php. Check also
  the css style located above in this page
  
*/

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
