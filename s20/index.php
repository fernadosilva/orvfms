<?php
session_start();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<?php
    /*
      if(($_SERVER["REQUEST_METHOD"] != "POST") || (isset($_POST['toMainPage']))){
        echo '<META HTTP-EQUIV="Refresh" CONTENT="150">'."\n";
    }
    */
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

/* UPDATE THE PATH to THE  orvfms LIBRARY and img directory 
   BELOW TO MATCH YOUR LOCAL CONFIGURATION.              
*/



define("ORVFMS_PATH","../lib/orvfms/");
define("IMG_PATH","../img/");

require_once(ORVFMS_PATH."utils.php");

function  getMacAndActionFromPost(&$action,$postVal){
    $nc = strlen($postVal);
    $actLen = $nc - 12;
    $action = substr($postVal,0,$actLen);
    $mac = substr($postVal,$actLen);
    return $mac;
}


require_once(ORVFMS_PATH."orvfms.php"); 

$myUrl = htmlspecialchars($_SERVER["PHP_SELF"]);

$daysOfWeek = array("Monday","Tuesday","Wednesday","Thursday",
                   "Friday","Saturday","Sunday");

$months = array("Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec");


if(isset($_SESSION["s20Table"])) {
    $s20Table = $_SESSION["s20Table"];
}
else{
    $s20Table = readDataFile();
}

// $s20Table = readDataFile(); // DELETE THIS LINE

$ndummy = 0;
if(isset($s20Table)){
    foreach($s20Table as $mac => $data){
        if(array_key_exists('off',$s20Table[$mac])) $ndummy++;
    }
}



//
// Refresh/update only S20 data if $s20Table was initialized before
// Refresh also if "inactive" S20s exists and last refressh was more than 5m away. 
//
// Otherwise, reinitialize all $s20Table structure
//


if(isset($s20Table) && (count($s20Table) > 0)){
    $s20Table = updateAllStatus($s20Table);  
    if(DEBUG)
        error_log("Session restarted; only status update\n");
}
else{
    $s20Table=initS20Data();   

    $ndev = count($s20Table);
    if($ndev == 0){
        echo "<h2>No sockets found</h2>";
        echo " Please check if all sockets are on-line and assure that they\n";
        echo " they are not locked (check WiWo app -> select socket -> more -> advanced).<p>";
        echo " In this version, locked or password protected devices are not supported.<p>";
        exit(1);
    }
    if(DEBUG)
        error_log("New session: S20 data initialized\n");
}
$_SESSION["s20Table"] = $s20Table;
//
// Check which page must be displayed
//
if ($_SERVER["REQUEST_METHOD"] != "POST"){
    require_once(ORVFMS_PATH."main_page.php");
    displayMainPage($s20Table,$myUrl);
    require_once(ORVFMS_PATH."main_page_scripts.php");
}
else if(isset($_POST['toMainPage'])){
    $mac = getMacAndActionFromPost($actionValue,$_POST['toMainPage']);
    if($actionValue == "check"){
        $ip = getIpFromMac($mac,$s20Table);
        $s20Table[$mac]['lastOffCheck'] = time();
        $_SESSION['s20Table'] = $s20Table;
        if($ip!=0){
            $s20Table[$mac]['ip'] = $ip;
            $st = checkStatus($mac,$s20Table);
            $_SESSION["s20Table"] = $s20Table;
            if($st >= 0){
                unset($s20Table[$mac]['off']);
                $s20Table[$mac]['st'] = $st;
                $_SESSION['s20Table'] = $s20Table;
                require_once(ORVFMS_PATH."main_page.php");
                displayMainPage($s20Table,$myUrl);
                require_once(ORVFMS_PATH."main_page_scripts.php");
            }
        }
        if(($ip == 0) || ($st < 0)){            
            require_once(ORVFMS_PATH."setup_page.php");
            displaySetupPage($mac,$s20Table,$myUrl);
        }
    } 
    else if(substr($actionValue,0,9) == "procSetup"){
        require_once(ORVFMS_PATH."process_setup.php");
        processSetup($mac,$s20Table,$actionValue);
        require_once(ORVFMS_PATH."main_page.php");
        displayMainPage($s20Table,$myUrl);
        require_once(ORVFMS_PATH."main_page_scripts.php");
    }    
    else{
        if(($actionValue != "back") && ($mac != "000000000000") && !array_key_exists('off',$s20Table[$mac])){
            if($actionValue=="switch"){
                $st = $s20Table[$mac]['st'];
                $newSt = actionAndCheck($mac,($st==0 ? 1 : 0),$s20Table);
                $s20Table[$mac]['st']=$newSt;
                $swVal = $s20Table[$mac]['switchOffTimer']; 
                if(($st == 0) && ($newSt == 1) && ($swVal > 0)){
                    $s20Table[$mac]['timerVal'] = $swVal;
                    $s20Table[$mac]['timerAction'] = 0;
                }
            }
            else if(($actionValue == "setCountdown") ||
                    ($actionValue == "clearCountdown") ||
                    ($actionValue == "clearSwitchOff")){
                require_once(ORVFMS_PATH."timer_settings.php");
                timerSettings($s20Table,$mac,$actionValue);
            }
            else{
                error_log("Unknown action value: >".$actionValue."<\n");
            }
        }
        else if($actionValue == "find"){
            $s20Table = initS20Data();
        }
        else if($actionValue == "back"){
        /* OK, nothing done */
        }
        require_once(ORVFMS_PATH."main_page.php");
        displayMainPage($s20Table,$myUrl);
        require_once(ORVFMS_PATH."main_page_scripts.php");
    }
}        
else if(isset($_POST['toCountDownPage'])){
    $mac = getMacAndActionFromPost($actionValue,$_POST['toCountDownPage']);
    require_once(ORVFMS_PATH."timer_page.php");
    displayTimerPage($mac,$s20Table,$myUrl);
}
else if(isset($_POST['toDetailsPage'])){
    require_once(ORVFMS_PATH."edit_process.php");
    $mac = getMacAndActionFromPost($actionValue,$_POST['toDetailsPage']);
    if($actionValue=="updateOrAdd"){
        editProcess($mac,$s20Table);
    }
    else if(substr($actionValue,0,3)=="del"){
        $recCode = substr($actionValue,3);
        delProcess($mac,$recCode,$s20Table);        
    } else if($actionValue == "clock"){
        /* Nothing here, just display page */
    }
    require_once(ORVFMS_PATH."details_page.php");
    displayDetailsPage($mac,$s20Table,$myUrl);
}
else if(isset($_POST['toEditPage'])){
    $mac = getMacAndActionFromPost($actionValue,$_POST['toEditPage']);
    if(substr($actionValue,0,4) == "edit"){
        $editIndex = substr($actionValue,4);
    }
    else{
        $editIndex = -1;
    }
    require_once(ORVFMS_PATH."edit_page.php");
    displayEditPage($mac,$editIndex,$s20Table,$myUrl);
}
else if(isset($_POST['toSetupPage'])){
    $mac = getMacAndActionFromPost($actionValue,$_POST['toSetupPage']);
    if($actionValue == "procSync"){ // Sync socket TZ to server TZ
        $serverTz = $s20Table[$mac]['serverTimeZone'];
        $serverDst = $s20Table[$mac]['serverDst'];
        setTimeZone($mac,$serverTz,$serverDst,$s20Table);
    }
    else if($actionValue != "setup"){
        echo "Unexpected error in setup (505)<p>\n";
    }
    require_once(ORVFMS_PATH."setup_page.php");
    displaySetupPage($mac,$s20Table,$myUrl);
}
else{
    echo "Unexpected error 505 (unkown code) <p>\n";
}

?>
</body>
</html>