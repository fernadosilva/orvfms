<?php

/*************************************************************************
*  Copyright (C) 2015 by Fernando M. Silva   fcr at netcabo dot pt      *
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
      Main  functions to control the Orvibo (C) S20 swicth.

      This program was developed independently and it is not
      supported or endorsed in any way by Orvibo (C).
   
*/

include_once("globals.php"); // constants
include_once("utils.php");   // utitlity functions

function sendByteMsg($s,$msg,$addr){
    //
    // Send the datagram $msg to address ($addr,PORT), using 
    // opened socket $s
    // 
    $byteMsg = hex_str2byte($msg);
    $nBytesMsg=strlen($msg)/2;
    if(!socket_sendto($s,$byteMsg,$nBytesMsg,0,$addr,PORT)){
        echo "<h1>Error sending  message to socket in sendByteMsg, addr= ".$addr."</h1>\n";
        exit(0);
    }
}

function createSocketAndSendMsg($msg,$addr){
    //
    // Create socket, bind it to local address (0.0.0.0,PORT),
    // for listening, sets timeout to receiving operations, 
    // and sends msge $msg to address ($addr,PORT)
    // 
    $s = socket_create(AF_INET,SOCK_DGRAM,0);
    if(!$s){
        echo "<h1>Error opening socket</h1>";
        exit(0);
    }

    if(!socket_bind($s,"0.0.0.0",PORT)){
        echo "<h1>Error binding socket</h1>";
        exit(0);
    }
    if(!socket_set_option($s,SOL_SOCKET,SO_BROADCAST,1)){
        echo "<h1>Error setting socket options</h1>";
        exit(0);
    }
    // Set the timeout to 300ms; seems enough. 
    $timeout = array('sec' => 0,'usec'=> 300000); 
    socket_set_option($s,SOL_SOCKET,SO_RCVTIMEO,$timeout);
    sendByteMsg($s,$msg,$addr);
    return $s;
}


function searchS20(){
    //
    // This function searchs for all S20 in a local network
    // through a broadcast call 
    // and returns an associative array $allS20Data indexed
    // by each S20 mac adress. Each array position is itself 
    // an associative array which contains 
    //
    // $allS20Data[$mac)['ip'] - IP adresss
    // $allS20Data[$mac)['st'] - current S20 status (ON=1,OFF=0)
    // $allS20Data[$mac)['imac'] - Inverted mac,not strictly required, 
    //                             computed just once for sake of efficiency.
    //
    // Note that $mac and is represented as a sequence of hexadecimals 
    // without the usual separators; for example, ac:cf:23:34:e2:b8 is represented
    // as "accf2334e2b8".
    //
    // An additional field $allS20Data[$mac]['name'] is later added 
    // to each entry with the name assigned to each device. 
    // This is done in a specific function since it requires a separate
    // request to each S20 (see function getName() and fillNames below).
    //
    // Returns the $allS20Data array
    //
    $s = createSocketAndSendMsg(DISCOVERY_MSG,IP_BROADCAST);
    $recIP="";
    $recPort=0;
    $allS20Data=array();
    while ( 1 ){
        $n=@socket_recvfrom($s,$bytesRecMsg,42,0,$recIP,$recPort);
        if(!$n) 
            break;
        if($n == 42){
            $recMsg = hex_byte2str($bytesRecMsg,$n);
            if(substr($recMsg,0,4) == "6864"){
                $mac = substr($recMsg,14,12);
                $status = (int) substr($recMsg,-1);
                $allS20Data[$mac]=array();
                $allS20Data[$mac]['ip']=$recIP;
                $allS20Data[$mac]['st']=$status;
                $allS20Data[$mac]['imac']=invMac($mac);
            }
        }        
    }
    socket_close($s);
    return $allS20Data;
}

function subscribe($mac,$allS20Data){
    //
    // Sends a subscribe message to S20 specified by mac address 
    // $mac, using global device information im $allS20Data.
    // 
    // Returns the socket status 
    //
    $imac = $allS20Data[$mac]['imac'];
    $ip   = $allS20Data[$mac]['ip'];
    $msg = SUBSCRIBE.$mac.TWENTIES.$imac.TWENTIES;
    $s = createSocketAndSendMsg($msg,$ip);
    $stay=1;
    $loop_count=0;
    while($stay){
        if(++$loop_count > MAX_RETRIES){
            echo "<h1> Error: too many retries without successfull subscription</h1>\n";
            exit(0);
        }
        $n=@socket_recvfrom($s,$bytesRecMsg,24,0,$recIP,$recPort);        
        if($n == 0){
            // This is probably due to timeout; retry...
            // echo "retrying on update\n";
            sendByteMsg($s,$msg,$ip);
        }
        else{
            $recMsg = hex_byte2str($bytesRecMsg,$n);            
            if($n == 24){
                if(substr($recMsg,0,4)=="6864"){
                    $rmac = substr($recMsg,12,12);
                    if($rmac==$mac){
                        $status = (int) substr($recMsg,-1);
                        $stay = 0;
                    }
                }                    
            }
        }
    }
    socket_close($s);
    return $status;
}

function getName($mac,$allS20Data){
    //
    // Returns the registered name in S20 specified by the mac $mac.
    // Uses previous device information available in $allS20Data.
    //
    $ip = $allS20Data[$mac]['ip'];
    subscribe($mac,$allS20Data);
    $getSocketData = "6864001D7274".$mac.TWENTIES."0000000004001700000000";    
    $s = createSocketAndSendMsg($getSocketData,$ip);
    $recIp = ""; $recPort=0;
    $stay = 1;
    $loop_count = 0;
    while($stay){
        if(++$loop_count > MAX_RETRIES){
            echo "<h1> Error: too many retries without successfull reply in getName()</h1>\n";
            exit(0);
        }
        $n=@socket_recvfrom($s,$bytesRecMsg,168,0,$recIP,$recPort);        
        if($n == 0){
            // This is probably due to timeout; retry...
            sendByteMsg($s,$getSocketData,$ip);
            //            echo "retrying\n";
        }
        else{
            $recMsg = hex_byte2str($bytesRecMsg,$n);            
            //            print_r( "getName (".$n.")=".$recMsg."\n");
            if($n==168){
                if(substr($recMsg,0,4)=="6864"){
                    $rmac = substr($recMsg,12,12);
                    if($rmac == $mac){
                        $name = substr($bytesRecMsg,70,16);
                        $stay=0;
                    }
                }
            }
        }
        //        ob_flush();
    }
    socket_close($s);
    return trim($name);
}

function fillNames($allS20Data){
    //
    // Loos through all S20 regiestered in $allS20Data and
    // fills the name in each entry
    // 
    // 
    foreach($allS20Data as $mac => $devData){
        $name = getName($mac,$allS20Data);
        $allS20Data[$mac]['name'] = $name;
    }    
    return $allS20Data;
}

function initS20Data(){
    //
    // Search all sockets in the network, and returns 
    // an associative array with all collected data,
    // including names
    //
    $allS20Data = searchS20();
    $allS20Data = fillNames($allS20Data);
    return $allS20Data;
}

function checkStatus($mac,$allS20Data){
    //
    // Checks the power status of the S20 speciifed by
    // mac adresss $mac using available information in 
    // $allS20Data. This is basically done with a subscribe 
    // function (see above)
    // 
    return subscribe($mac,$allS20Data);
}

function updateAllStatus($allS20Data){
    //
    // This function updates the power status of all S20 in $allAllS20Data.
    //
    // InitS20Data also fills the power status when it is called.
    // However, this function is more efficient when $allS20Data
    // was already initialized and relevant available 
    // and one just wants to update the power status of all S20s
    //
    foreach($allS20Data as $mac => $devData){
        $allS20Data[$mac]['st'] = checkStatus($mac,$allS20Data);
    }
    return $allS20Data;
}

function sendAction($mac,$allS20Data,$action){
    //
    // Sends an $action (ON=1, OFF = 0) to S20 specified by $mac
    // It retries until a proper reply is received with the desired 
    // power status
    // However, we have detected that the reported power status just
    // after an action fails sometimes, and therefore you should not
    // use this function alone. Prefer switchAndCheck() below, which 
    // performs a double check of the final state.
    //
    subscribe($mac,$allS20Data);
    $msg = ACTION.$mac.TWENTIES; 
    if($action)
        $msg .= ON;
    else
        $msg .= OFF;
    $ip = $allS20Data[$mac]['ip'];
    $s = createSocketAndSendMsg($msg,$ip);    
    $stay=1;
    $loop_count = 0;
    while($stay){
        if(++$loop_count > MAX_RETRIES){
            echo "<h1> Error: too many retries without successfull sendAction()</h1>\n";
            exit(0);
        }
        $n=@socket_recvfrom($s,$bytesRecMsg,23,0,$recIP,$recPort);        
        if($n == 0){
            // This is probably due to timeout; retry...
            // echo "retrying on switch\n";
            sendByteMsg($s,$msg,$ip);
        }
        else{
            $recMsg = hex_byte2str($bytesRecMsg,$n);            
            // print_r("\nSent      ".$msg."\n");
            // print_r("Receiving ".$recMsg." from ".$recIP."\n\n");
            if($n == 23){
                if(substr($recMsg,0,4)=="6864"){
                    $rmac = substr($recMsg,12,12);
                    if($rmac==$mac){
                        $status = (int) substr($recMsg,-1);
                        if($status == $action)
                            $stay = 0;
                    }
                }                    
            }
        }
    }
    socket_close($s);
}

function actionAndCheck($mac,$allS20Data,$action){
    /*
      This function implements a switch and check satus.
      The check is in fact a double check and should not 
      be required, since the sendAction function checks the 
      power status itself in the reply command and only gives up
      when the correct reply is received. 
      Nevertheless, we have seen the S20 fail report the 
      wrong status sometimes on power on/power off actions 
      Checking the status through a separate subscribe command
      seems ro be able to always get the right status.
    */
    $stay = 1;
    $loop_count = 0;
    while($stay){
        if(++$loop_count > MAX_RETRIES){
            echo "<h1> Error: too many retries without successfull action in actionAndCheck ()</h1>\n";
            exit(0);
        }
        sendAction($mac,$allS20Data,$action);
        $st = checkStatus($mac,$allS20Data);
        if($st == $action){
            $stay = 0;
        }
        else{
            $logmsg = "switch action FAILED, repeating:\n".
                    " (ordered=".$action." checked=".$st.")\n";
            error_log($logmsg);
        }
    } 
    return $st;
}

function getMacFromName($name,$allS20Data){
//
// Returns the $mac address of the S20 with name $name
//
    $count = 0;
    foreach($allS20Data as $imac => $devData){
        if($devData['name'] == $name){
            $mac = $imac;
            $count++;
        } 
    }
    if($count == 0){
        echo "<h1>Not found S20 with name ".$name." </h1>\n";
        exit(0);
    }
    if($count > 1){
        echo "<h1>Ambiguous: more than one S20 found with same name  ".$name." result may be incorrect</h1>\n";
    }
    // echo "Found mac for ".$name." = ".$mac."\n";
    return $mac;
}

function sendActionByDeviceName($name,$allS20Data,$action){
    //
    // Sends an action to device designates with $name
    //    
    $mac = getMacFromName($name,$allS20Data);
    return actionAndCheck($mac,$allS20Data,$action);
}
?>

