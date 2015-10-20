<?php
session_start();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<?php
if(($_SERVER["REQUEST_METHOD"] != "POST") || isset($_POST["action"])){
    echo '<META HTTP-EQUIV="Refresh" CONTENT="60">'."\n";
}
?>
<title>
S20 remote
</title>

<!-- UPDATE THE PATH OF THE FILE orvfms.css BELOW TO MATCH
     YOUR LOCAL CONFIGURATION.                       -->
<link rel="stylesheet" type="text/css" href="../css/orvfms.css"> 
<script>
      function convToString(time){
            var  h,m,min,s;
            var  hs,ms,ss;
            var t;
            h = Math.floor(time/3600);
            min = time % 3600;
            m = Math.floor(min / 60);
            s = min % 60;
            hs=('0'+h.toString()).slice(-2);
            ms=('0'+m.toString()).slice(-2);
            ss=('0'+s.toString()).slice(-2);
            t = hs+':'+ms+':'+ss;
            return t;
      }
</script>
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
  This program was developed independently and it is not
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

/* UPDATE THE PATH OF THE  orvfms LIBRARY  BELOW TO MATCH
   YOUR LOCAL CONFIGURATION.              
*/
define("ORVFMS_PATH","../lib/orvfms/");

include(ORVFMS_PATH."orvfms.php"); 

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
   && (count($s20Table)>0) && ((time()-$time_ref < 300))){
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
    //    print_r($_POST);
    if(isset($_POST['action'])){
        $submitAction = 'setTimer';
        include(ORVFMS_PATH."timer_settings.php");
        timerSettings($s20Table);
    }
    else{
        $submitAction = $_POST['selected'];
        if(substr($submitAction,0,strlen(PREFIX_CODE)) == PREFIX_CODE){
            $timerName = substr($submitAction,strlen(PREFIX_CODE));
            $submitAction = "timerMenu";
        }
        else{
            $mac = getMacFromName($submitAction,$s20Table);
            $st = $s20Table[$mac]['st'];
            $newSt = actionAndCheck($mac,($st==0 ? 1 : 0),$s20Table);
            $s20Table[$mac]['st']=$newSt;
            $swVal = $s20Table[$mac]['switchOffTimer']; 
            if(($st == 0) && ($newSt == 1) && ($swVal > 0)){
                $s20Table[$mac]['timerVal'] = $swVal;
                $s20Table[$mac]['timerAction'] = 0;
            }
            if(DEBUG)
                print "<br><br><br><br>".$st." => ".$newSt." -> ".$swVal."<br><br><br>";
        }
    }
}
else{
    $submitAction ="";
}

$_SESSION["s20Table"]=$s20Table;
if(DEBUG)
    print_r(-$s20Table);

if($submitAction == "timerMenu"){
    include(ORVFMS_PATH."timer_page.php");
    displayTimerPage($timerName,$s20Table,$myUrl);
}
else{
    include(ORVFMS_PATH."main_page.php");
    displayMainPage($s20Table,$myUrl);
    include(ORVFMS_PATH."main_page_scripts.php");
}
?>
</body>
</html>