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

require_once("globals.php"); // constants
require_once("utils.php");   // utitlity functions

function cmpActions($a1,$a2){
    return ($a1[1] > $a2[1]);
}

function getAllActions($mac,$s20Table){
   $timers = $s20Table[$mac]['details'];
   $nt = count($timers);
   $nowStamp = time(); 
   $auxDate  = getdate($nowStamp);
   $nowWeekDay  = $auxDate['wday'];
   
   $year  = $auxDate['year'];
   $month = $auxDate['mon'];
   $day   = $auxDate['mday'];
   $nonRepeatSpent = array();

   $todayStamp = mktime(0,0,0,$month,$day,$year);
   $allActions = array();
   for($d = 0; $d < 8; $d++){       
       $wdAux = ($nowWeekDay + $d) % 7;
       $bit = $wdAux - 1;
       if($bit < 0) $bit += 7;
       $mask = (1 << $bit);

       for($i = 0; $i < $nt ; $i++){
           $t = $timers[$i];
           $rep = $t['r'];
           $daySecs = $t['time'];
           $act = $t['action'];

           $stamp = $todayStamp + $daySecs;
           
           if($stamp > $nowStamp){
               if($rep < 128){
                   $year  = $t['y'];
                   $month = $t['m'];
                   $day   = $t['d'];                                          
                   $dateStamp = mktime(0,0,0,$month,$day,$year);
                   $stampnr = $dateStamp + $daySecs;
                   if($stampnr == $stamp){
                       $action = array($act,$stampnr); 
                       array_push($allActions,$action);
                   }
               }
               else if($mask & $rep){
                   $action = array($act,$stamp); 
                   array_push($allActions,$action);                   
               }              
           }
       }
       $todayStamp += 24*3600;
   }
   usort($allActions,"cmpActions");
   //  print_r($allActions);
   return $allActions;
}

function getNextAction($mac,$s20Table){
    $timers = $s20Table[$mac]['details'];
    $nt = count($timers);
    if($nt==0){
        return array();
    }
    else{
        secToHour($timers[0]['time'],$h,$m,$s);
        $nextStamp = getNextEventTimeStamp($h,$m,$s,$timers[0]['r']);
        $nextAct = $timers[0]['action'];
        for($k = 1; $k < $nt; $k++){
            secToHour($timers[$k]['time'],$h,$m,$s);
            $nextEvent = getNextEventTimeStamp($h,$m,$s,$timers[$k]['r']);
            if($nextEvent < $nextStamp){
                $nextStamp = $nextEvent;
                $nextAct = $timers[$k]['action'];
            }
        }
        $next = array($nextAct,$nextStamp);
    }
    return array($next);
}

function getSocketTime($tab){
    $th=invertEndian(padHex(substr($tab,-2*5,8),8));
    $timeStamp = hexdec($th) - 2208988800;        
    return $timeStamp;
}

function cmpTimers($a,$b){
    return $a['time'] - $b['time'];
}

function adjustMsgSize($msg){
    //
    // Adjust the (sending) msg size
    //
    $newSize = strlen($msg) / 2;
    $newSizeHex = padHex(dechex($newSize),4);
    $newMsg = substr_replace($msg,$newSizeHex,2*2,4);    
    return $newMsg;
}

function buildSetTimerString($h,$m,$sec,$action,$rep,$date){
    $y    = $date[0];
    $mon  = $date[1];
    $d    = $date[2];

    $relevantBits = ($action == 1 ? "01":"00")."00";
    
    $yh = invertEndian(padHex(dechex($y),4));
    $monh = padHex(dechex($mon),2);
    $dh = padHex(dechex($d),2);

    $hh = padHex(dechex($h),2);
    $mh = padHex(dechex($m),2);
    $sh = padHex(dechex($sec),2);
    $rh = padHex(dechex($rep),2);

    $timerSetString = $relevantBits.$yh.$monh.$dh.$hh.$mh.$sh.$rh;
    return $timerSetString;
}



function delTimer($mac,$code,&$s20Table){
    $writeMsg = MAGIC_KEY."XXXX".WRITE_SOCKET_CODE.$mac.TWENTIES.FOUR_ZEROS.
              "030002".$code;    
    $writeMsg = adjustMsgSize($writeMsg);
    // echo "<p>";printHex($writeMsg); echo "<p>";
    $res = createSocketSendHexMsgWaitReply($mac,$writeMsg,$s20Table);    
    $s20Table[$mac]['details'] = getAndParseTimerTable($mac,$s20Table);
    return $res;
}

function getNextEventTimeStamp($h,$m,$s,$rep){
    //    echo "<p><p><p> Rep=".($rep-128)."<p>";
    $nowStamp = time();
    $auxDate = getdate($nowStamp);
    $weekDay = $auxDate['wday'];

    $year  = $auxDate['year'];
    $month = $auxDate['mon'];
    $day   = $auxDate['mday'];
    $refStamp = mktime($h,$m,$s,$month,$day,$year);
    
    for($wd=0; $wd < 8; $wd++){
        $auxDate = getdate($refStamp);
        $wdAux = ($wd+$weekDay) % 7; // 0 -Sunday, 6 - Saturday
        $bit = $wdAux - 1;
        if($bit < 0) $bit += 7;
        $mask = (1 << $bit);
        // echo "<p>wd=".$weekDay." bit = ".$bit." mask = ".$mask." rep = ".$rep."<p>";
        if(($mask & $rep) || !($rep)){
            if($refStamp > $nowStamp){
                $eventStamp = $refStamp;
                break;
            }
        }
        $refStamp += 24*3600; /* Advance one day */
    }   
    return $eventStamp;
}

function getDateFromTimerCode($code,$devData,$h,$min,$s,$rep){
    //
    // Get the date of the next 
    // occurring event at %h:$m given week repetion pattern $rep.
    //

    if($code != ""){
        if(isset($devData['details'])){
            $det = $devData['details'];
        }
        else if(isset($_SESSION['details'])){
            $det = $_SESSION['details'];
        }
        $nTimers = count($det);
        for($k = 0; $k < $nTimers; $k++){
            if($det[$k]['recCode'] == $code){
                $recData = $det[$k];
                break;
            }
        }
    }

    if($rep==0 || !isset($recData)){ 
        // If just once or new timer, date is the next event date
        $nextEventStamp = getNextEventTimeStamp($h,$min,$s,$rep);
        $dateAux = getdate($nextEventStamp);
        $y = $dateAux['year'];
        $m = $dateAux['mon'];
        $d = $dateAux['mday'];
    }
    else{
        // If date is available before, and it is a repeating event,
        // use original date
        $y = $recData['y'];
        $m = $recData['m'];
        $d = $recData['d'];
    }
    $date = array($y,$m,$d);
    //    print_r($date);
    return $date;
}

function updTimer($mac,$code,$h,$m,$sec,$action,$rep,&$s20Table){
    $date = getDateFromTimerCode($code,$s20Table[$mac],$h,$m,$sec,$rep);
    $relevant=buildSetTimerString($h,$m,$sec,$action,$rep,$date);
    $writeMsg = MAGIC_KEY."XXXX".WRITE_SOCKET_CODE.$mac.TWENTIES.FOUR_ZEROS.
              "0300011C00".$code.twenties(16).$relevant;
    $writeMsg = adjustMsgSize($writeMsg);
    $res = createSocketSendHexMsgWaitReply($mac,$writeMsg,$s20Table);
    $s20Table[$mac]['details'] = getAndParseTimerTable($mac,$s20Table);
    return $res;    
}

function addTimer($mac,$h,$m,$sec,$action,$rep,&$s20Table){
    srand();
    $timerList = $s20Table[$mac]['details'];
    // print_r($timerList);
    $nTimers = count($timerList);
    $stay = 1;
    while($stay){
        $stay = 0;
        $newDecCode = rand(0,65535);
        $newCode = padHex(dechex($newDecCode),4);
        for($k = 0; $k < $nTimers ; $k++){
            if($newCode == $timerList[$k]['recCode'])
                $stay = 1;
        }
    }
    $date = getDateFromTimerCode("",$s20Table[$mac],$h,$m,$s,$rep);
    $relevant=buildSetTimerString($h,$m,$sec,$action,$rep,$date);
    $writeMsg = MAGIC_KEY."XXXX".WRITE_SOCKET_CODE.$mac.TWENTIES.FOUR_ZEROS.
              "0300001C00".$newCode.twenties(16).$relevant;
    $writeMsg = adjustMsgSize($writeMsg);
    $res = createSocketSendHexMsgWaitReply($mac,$writeMsg,$s20Table);
    $tt = getAndParseTimerTable($mac,$s20Table);
    $s20Table[$mac]['details'] = $tt;
    return $res;
}

function getAndParseTimerTable($mac,&$s20Table){
    $timerList = array();
    $table = 3; $vflag = "02";
    $tab3 = getTable($mac,$table,$vflag,$s20Table);
    $recs = getRecordsFromTable($tab3,$s20Table);
    for($i=0;$i<count($recs);$i++){
        $timerList[$i] = getRecordDetails($recs[$i]);
        $timerList[$i]['recCode'] = substr($recs[$i],2*2,2*2);
    }
    usort($timerList,"cmpTimers");
    $s20Table[$mac]['details'] = $timerList;
    return $timerList;
}

function getRecordDetails($rec){
    $timerDetails = array();
    $swAction = (substr($rec,20*2,2) == "00" ? 0 : 1);
    $year = hexdec(invertEndian(substr($rec,22*2,4)));
    $month = hexdec(invertEndian(substr($rec,24*2,2)));
    $day = hexdec(invertEndian(substr($rec,25*2,2)));
    $h = hexdec(invertEndian(substr($rec,26*2,2)));
    $m = hexdec(invertEndian(substr($rec,27*2,2)));
    $s = hexdec(invertEndian(substr($rec,28*2,2)));
    $rep=substr($rec,29*2,2);

    //    print sprintf("Set to %3s:  %02d:%02d:%02 rep=%2s (since %02d/%02d/%04d)\n",($swAction ? "on ":"off"),$h,$m,$s,$rep,$day,$month,$year);

    $timerDetails['y'] = $year;
    $timerDetails['m'] = $month;
    $timerDetails['d'] = $day;
    $timerDetails['time'] = $h*3600+$m*60+$s;
    $timerDetails['r'] = hexdec($rep); // Note: in decimal, not string.
    $timerDetails['action'] = $swAction;   // Note: in decimal, not string.
    return $timerDetails;
}



function getRecordsFromTable($tab,$s20Table){
    $records=array();
    // First record & length in bytes 29,30, record numnber in 31,32
    $n = strlen($tab)/2;
    $k = 0;
    $start = 28;

    while($start < $n){
        $k++;
        $recLenHex = substr($tab,$start*2,4);
        $recLenHex = invertEndian($recLenHex);
        $recLen = hexdec($recLenHex);
        // print "rec= ".$k." Start = ".$start. " len= ".$recLen."\n";
        $rec = substr($tab,2*$start,2*($recLen+2));
        $records[]=$rec;
        // print $rec."\n";
        $start += ($recLen+2);
    }    
    return $records;    
}


function actionToTxt($st){
    return $st ? "ON" : "OFF";
}

function setTimer($mac,$h,$m,$s,$act,$s20Table){
    //
    // Sets countdown timer of device $mac to $h:$m:$s and action $act
    //
    $timeHex = hourToSecHexLE($h,$m,$s);
    $action  = ($act ? ONT : OFFT);
    $setTimer="00".$action.substr($timeHex,0,2).substr($timeHex,2,2);
 
    $cmdCode = "6364";
    $setTimerHexMsg=MAGIC_KEY."001A".$cmdCode.$mac.
                   TWENTIES.FOUR_ZEROS.$setTimer; 
   
    $stay = 1; $loop_count=0;
    while($stay && ($loop_count++ < MAX_RETRIES)){

        $recHex = createSocketSendHexMsgWaitReply($mac,
                                                  $setTimerHexMsg,$s20Table);

        $hexMsg = strtoupper($setTimerHexMsg);
        $recHex = strtoupper($recHex);
        $msgRecCode = substr($recHex,2*18,2);
        $recHexAux = $recHex; 
        $recHexAux[36]="0";$recHexAux[37]="0";
        if(DEBUG){
            print("Send\n");
            printHex($hexMsg);
            print("Rec\n");
            printHex($recHex);
            print("RecAux\n");
            printHex($recHexAux);
        }        
        if(($msgRecCode != "00") && ($recHexAux == $hexMsg))
            return 0;
        else
            error_log("Retrying in setTimer\n");
    }
    return 1;
}

function updTableTimers(&$s20Table){
    //
    // Update the values of count down timers in table $s20Table
    //
    // echo "Update table timers <p>";
    $justTest = 0;
    foreach($s20Table as $mac => $devData){
        // Check && update count down timers
        $s20Table[$mac]['timerVal'] = checkTimerSec($mac,$s20Table,$action);
        $s20Table[$mac]['timerAction'] = $action;
        // Check && update switch off after on timer
        updNameAndTimerAfterOnDevice($mac,$s20Table);
        getAndParseTimerTable($mac,$s20Table);
    }
}

function checkTimerSec($mac,$s20Table,&$act){
    //
    // Check if countdown timer of device with mac $mac is set.
    // returns the number of seconds and updates value action
    // with 0 (Off) or 1 (On).
    // If no timer is programmed, returns 0.
    //
    if(checkTimer($mac,$s20Table,$h,$m,$s,$action)){
        $act = $action;
        $seconds = $h*3600+$m*60+$s;
    }
    else{
        $act = $seconds = 0;
    }
    return $seconds;
}



function checkTimer($mac,$s20Table,&$h,&$m,&$sec,&$action){
    //
    // Check if countdown timer of device with mac $mac is set.
    // Updates the arguments h,m, and s with hour, minutes and seconds
    // and updates value action with 0 (Off) or 1 (On).
    // If no countdown timer is programmed, returns 0, otherwise 1.
    //    
    $cmdCode = "6364";
    $checkTimer="01000000";
    $checkTimerHexMsg = MAGIC_KEY."001A".$cmdCode.$mac.
                        TWENTIES.FOUR_ZEROS.$checkTimer; 
   
    $recHex = createSocketSendHexMsgWaitReply($mac,
                                    $checkTimerHexMsg,$s20Table);
    if(DEBUG){
        echo "Check timer\n";
        echo "Sent\n"; 
        printHex($checkTimerHexMsg);
        echo "Rec\n"; 
        printHex($recHex);
    }

    $relevant = substr($recHex,-6);
    $status = substr($relevant,0,2);
    $isSet = 0;
    if($status!="FF"){
        $isSet = 1;
        $timeHex = substr($relevant,4,2).substr($relevant,2,2);
        $seconds = hexdec($timeHex);
        secToHour($seconds,$h,$m,$sec); 
        if($status == "00")
            $action = 0; // Set to turn off
        else
            $action = 1; // Set to turn on
    }
    else{
        $h = $m = $sec  = $action = 0;
    }
    return $isSet;
}


function createSocketSendHexMsgWaitReply($mac,$hexMsg,$s20Table){
    //
    // Sends msg specified by $hexMsg, in hexadecimal, to device
    // sepcified by $mac and waits for reply.
    // 
    // Returns the reply in hex format after checking major conditions
    //
    $ip   = $s20Table[$mac]['ip'];
    $s = createSocketAndBind($ip);
    $hexRecMsg = sendHexMsgWaitReply($s,$hexMsg,$ip);
    socket_close($s);
    return $hexRecMsg;
}

function sendHexMsgWaitReply($s,$hexMsg,$ip){
    //
    // Sends msg specified by $hexMsg, in hexadecimal, to $ip and
    // waits for reply.
    // Returns the reply in hex format after checking major conditions
    //
    
    $magicKey = substr($hexMsg,0,4);               
    if($magicKey != MAGIC_KEY)
        error_log("Warning: wrong msg key in send msg (sendhexMsgWaitReply)!!");
    
    $msgCodeSend = substr($hexMsg,8,4);               
    //
    // double check msg length
    //
    $msgLenHex = substr($hexMsg,4,4);               
    $msgLen    = hexdec($msgLenHex);
    if($msgLen != strlen($hexMsg)/2){
        error_log($hexMsg."\n");
        error_log("Wrong msg length in sendHexWaitReply: Msg has ".(strlen($hexMsg)/2).
                  " bytes, code states ".$msgLen." bytes\n");        
    }
    $codeSend = substr($hexMsg,8,4);
    sendHexMsg($s,$hexMsg,$ip);
    $loop_count=0;
    for(;;){
        if(++$loop_count > MAX_RETRIES){
            echo "<h1> Error: too many retries without successfull replies</h1>\n";
            exit(0);
        }
        $n=@socket_recvfrom($s,$binRecMsg,BUFFER_SIZE,0,$recIP,$recPort);
        if($n == 0){
            // This is probably due to timeout; retry...
            sendHexMsg($s,$hexMsg,$ip);
            if(DEBUG)
                error_log( "retrying on update\n");
        }
        else{
            if($n >= 12){
                $recHexMsg     = hex_byte2str($binRecMsg,$n);                        
                $magicKey      = substr($recHexMsg,0,4);
                $recLenHex     = substr($recHexMsg,4,4);                
                $recLen        = hexdec($recLenHex);
                $msgCodeRec    = substr($recHexMsg,8,4);                
                if(DEBUG){
                    echo "Received: \n";
                    printHex($recHexMsg);
                    echo "Magic = ".$magicKey."\n";
                    echo "recLenHex  = ".$recLenHex." n = ".$n."\n";
                    echo "msgCodeRec = ".$msgCodeRec." (was ".$msgCodeSend.")\n";
                    echo "IP=".$recIP. " ".$ip."\n";
                }
                if(($magicKey == MAGIC_KEY) &&
                   ($n == $recLen) &&
                   ($recIP==$ip) &&
                   ($msgCodeRec == $msgCodeSend)) { 
                    // Everything seems OK
                    if(DEBUG) 
                        error_log("OK in sendHexMsgWaitReply: Number of retries to success ".$recIP." = ".$loop_count."\n");
                    return $recHexMsg;
                }
            }
        }
    } /* Never reaches */;
    echo "<h1>Fatal Error in sendHexMsgWaitReply: reached end of function</h1>";
    exit(0);
    return "";
}



function sendHexMsg($s,$hexMsg,$ip){
    //
    // Send the datagram $hexMsg to address ($ip,PORT), using 
    // opened socket $s
    // $hexMsg is an hexadecimal coded sequence/string, therefore must be converted to
    // binary.
    // 
    if(strlen($hexMsg) % 2){
        error_log("Warning: odd hex msg in sendHexMsg");
    }
    if(strlen($hexMsg) == 0){
        echo "<h1>Fatal: attempting to send null msg len in sendHexMsg</h1>\n";
        exit(0);
    }
    $binMsg = hex_str2byte($hexMsg);
    $lenBinMsg=strlen($hexMsg)/2;
    if(!socket_sendto($s,$binMsg,$lenBinMsg,0,$ip,PORT)){
        echo "<h1>Error sending  message to socket in sendHexMsg, addr= ".$ip."</h1>\n";
        exit(0);
    }
}

function createSocketAndBind($ip){
    //
    // Create socket, bind it to local address (0.0.0.0,PORT),
    // for listening, sets timeout to receiving operations, 
    // and sends msge $msg to address ($ip,PORT)
    // 
    $s = socket_create(AF_INET,SOCK_DGRAM,0);
    if(!$s){
        echo "<h1>Error opening socket</h1>";
        exit(0);
    }
    
    $loop_count = 0;
    $stay = 1;
    while($stay){
        if(!socket_bind($s,"0.0.0.0",PORT)){
            if(++$loop_count > MAX_RETRIES){
                error_log("Fatal error binding to socket\n");
                error_log("Bind loop count = ".$loop_count);
                echo "<h1>Error binding socket</h1>";
                exit(0);
            }
            error_log("Error binding to socket: ".$loop_count);
            usleep(TIMEOUT*1E6 * rand(0,10000)/10000.0); // backoff for a while
        }
        else{
            $stay = 0;
        }
    }
    if(DEBUG)
        error_log("Bind loop count = ".$loop_count);
    if(!socket_set_option($s,SOL_SOCKET,SO_BROADCAST,1)){
        echo "<h1>Error setting socket options</h1>";
        exit(0);
    }
    //
    // Set the timeout. Default set in globals.php
    // to 300ms; seems enough. 
    //
    $sec = (int)  TIMEOUT;
    $usec = (int) ((TIMEOUT - $sec) * 1000000.0);
    $timeout = array('sec' => $sec,'usec'=> $usec); 
    socket_set_option($s,SOL_SOCKET,SO_RCVTIMEO,$timeout);
    return $s;
}

function createSocketAndSendMsg($msg,$ip){
    //
    // Create socket, bind it to local address (0.0.0.0,PORT),
    // for listening, sets timeout to receiving operations, 
    // and sends msge $msg to address ($ip,PORT)
    // 
    $s = createSocketAndBind($ip);		
    sendHexMsg($s,$msg,$ip);			
    return $s;
}


function searchS20(){
    //
    // This function searchs for all S20 in a local network
    // through a broadcast call 
    // and returns an associative array $s20Table indexed
    // by each S20 mac adress. Each array position is itself 
    // an associative array which contains 
    //
    // $s20Table[$mac)['ip'] - IP adresss
    // $s20Table[$mac)['st'] - current S20 status (ON=1,OFF=0)
    // $s20Table[$mac)['imac'] - Inverted mac,not strictly required, 
    //                             computed just once for sake of efficiency.
    //
    // Note that $mac and is represented as a sequence of hexadecimals 
    // without the usual separators; for example, ac:cf:23:34:e2:b8 is represented
    // as "accf2334e2b8".
    //
    // An additional field $s20Table[$mac]['name'] is later added 
    // to each entry with the name assigned to each device. 
    // This is done in a specific function since it requires a separate
    // request to each S20 (see function getName() and updNamesAndTimerAfterOn below).
    //
    // Returns the $s20Table array
    //
    //    echo "Searching S20<p>";
    $s = createSocketAndSendMsg(DISCOVERY_MSG,IP_BROADCAST);
    $recIP="";
    $recPort=0;
    $s20Table=array();
    $loop_count = 0;
    while ( 1 ){
        $n=@socket_recvfrom($s,$binRecMsg,BUFFER_SIZE,0,$recIP,$recPort);
        $now = time();
        if($n == 0){
            if(++$loop_count > 3){
                if(count($s20Table) == 0){
                    error_log("Giving up searching for sockets");
                    echo "<h2>No sockets found</h2>\n\n";
                    echo " Please check if all sockets are on-line and assure that they\n";
                    echo " they are not locked:\n (check WiWo app -> select socket -> more -> advanced).<p>\n\n";
                    echo " In this version, locked or password protected devices are not supported.\n\n<p>";
                    exit(1);
                }
                else{
                    break;
                }
            }
            sendHexMsg($s,DISCOVERY_MSG,IP_BROADCAST);
            continue;
        }
        if($n >= 42){
            $recMsg = hex_byte2str($binRecMsg,$n);
            if((substr($recMsg,0,4) == MAGIC_KEY) && (substr($recMsg,8,4) == "7161")){
                $mac = substr($recMsg,14,12);
                $status = (int) substr($recMsg,-1);
                $s20Table[$mac]=array();
                $s20Table[$mac]['ip']=$recIP;
                $s20Table[$mac]['st']=$status;
                $s20Table[$mac]['imac']=invMac($mac);
                $s20Table[$mac]['time']=getSocketTime($recMsg);
                $s20Table[$mac]['serverTime'] = $now;
            }
            
        }
    }
    socket_close($s);
    return $s20Table;
}

function subscribe($mac,&$s20Table){
    //
    // Sends a subscribe message to the S20 specified by mac address 
    // $mac, using global device information in $s20Table.
    // 
    // Returns the socket status 
    //
    if(!isset($s20Table)){
        echo "<h1>Internal server error</h1>";
        error_log("Found null s20Table in subscribe\n");
    }
    $imac = $s20Table[$mac]['imac'];

    $hexMsg = SUBSCRIBE.$mac.TWENTIES.$imac.TWENTIES;

    $hexRecMsg = createSocketSendHexMsgWaitReply($mac,$hexMsg,$s20Table);
    $status = (int) hexdec(substr($hexRecMsg,-2,2));
    return $status;
}

function getTable($mac,$table,$vflag,$s20Table){
    $tableHex = dechex($table);
    if(strlen($tableHex) == 1)
        $tableHex = "0".$tableHex;
    $hexMsg = "6864001D7274".$mac.TWENTIES."00000000".$tableHex."00".$vflag."00000000";    

    $hexRec = createSocketSendHexMsgWaitReply($mac,$hexMsg,$s20Table);
    return $hexRec;
}


function setTimeZone($mac,$tz,&$s20Table){
    //
    // Sets the timezone in table 4 to $tz
    //
    $table = 4; $vflag = "17";
    $recTable = getTable($mac,$table,$vflag,$s20Table);
   
    // Set timezone

    $tzSetString = "00";

    $tzValString    = padHex(dechex($tz),2);
    $tzString = $tzSetString.$tzValString;
    
    echo "################".$tzString."##############\n";

    $newTableAux = substr_replace($recTable,$tzString,2*162,2*2);

    // replace receive code with send code
    $newTableAux = substr_replace($newTableAux,WRITE_SOCKET_CODE,2*4,4);

    //
    // Delete byte 18 (??)
    // Wireshark shows...
    //
    $newTable = substr_replace($newTableAux,"",18*2,2);
    //
    // Delete byte 25 and 26 of resulting (??)
    // Wireshark shows...
    //
    $newTable = substr_replace($newTable,"",25*2,4);

    // Update msg size, just in case it has changed
    $newTable = adjustMsgSize($newTable);

    $reply = createSocketSendHexMsgWaitReply($mac,$newTable,$s20Table);
}


function setSwitchOffTimer($mac,$sec,&$s20Table){
    //
    // Sets the automatic switch off timer in table 4 to $sec
    //
    subscribe($mac,$s20Table); 
    $table = 4; $vflag = "17";
    $recTable = getTable($mac,$table,$vflag,$s20Table);
   

    $switchOffData = substr($recTable,164*2,8);

    // This substring defines  the format for 
    // "automatic switch off after switch on" according to the following
    //
    //  XXYYZZZZ
    //
    //  XX, Enabled satus: XX=00 Disabled,XX=01 - Enabled
    //  YY, Action to be performed after switch on: 00-turn off, 01-turn on
    //  ZZZZ Initial countdown in seconds, little endian
    //
    //  Not sure what is the purpose of YY=01 (switch on after switch on), 
    //  but we have seen this configuration ocasionaly, possibly  as 
    //  a side effect of some other operation.
    //
    $switchOffData = (($sec == 0) ? "00" : "01")."00";
    $switchOffData = $switchOffData.secToHexLE($sec);

    // Set timer
    $newTableAux = substr_replace($recTable,$switchOffData,2*164,8);

    // replace receive code with send code
    $newTableAux = substr_replace($newTableAux,WRITE_SOCKET_CODE,2*4,4);

    //
    // Delete byte 18 (??)
    // Wireshark shows...
    //
    $newTable = substr_replace($newTableAux,"",18*2,2);
    //
    // Delete byte 25 and 26 of resulting (??)
    // Wireshark shows...
    //
    $newTable = substr_replace($newTable,"",25*2,4);

    // Update msg size, just in case it has changed
    $newTable = adjustMsgSize($newTable);

    $reply = createSocketSendHexMsgWaitReply($mac,$newTable,$s20Table);
    $newSec = getSwitchOffTimer($mac,$s20Table);
    return $newSec;
}

function getSwitchOffTimer($mac,&$s20Table){
    //
    // Returns the switch off timer for device $mac
    //
    updNameAndTimerAfterOnDevice($mac,$s20Table);
    return $s20Table[$mac]['switchOffTimer'];
}

function updNameAndTimerAfterOnDevice($mac,&$s20Table){
    //
    // Updates the name and "switch Off timer" on 
    // $s20Table. Information retrieved from table 4.
    //
    subscribe($mac,$s20Table);
    $table = 4; $vflag = "17";
    $recTable = getTable($mac,$table,$vflag,$s20Table);

    // Upd Name
    $binTable = hex_str2byte($recTable);
    $name = substr($binTable,70,16);
    $s20Table[$mac]['name'] = trim($name);

    // Upd automatic timer switch off after on
    $timerSetString = substr($recTable,164*2,2);
    $timerValString = substr($recTable,166*2,4);


    $timerValString = invertEndian($timerValString);
    if($timerSetString == "00")
        $timerVal = 0;
    else
        $timerVal = hexdec($timerValString);

    if(DEBUG)
        echo "Timer Set=".$timerSetString." Val= ".$timerValString." dec = ".$timerVal."\n";

    $s20Table[$mac]['switchOffTimer']=$timerVal;    

    $tzS = hexdec(substr($recTable,162*2,2));
    $tz  = hexdec(substr($recTable,163*2,2));

    $s20Table[$mac]['timeZone'] = $tz;
    $s20Table[$mac]['timeZoneSet'] = $tzS;
}





function getName($mac,$s20Table){
    //
    // Returns the registered name in S20 specified by the mac $mac.
    // Uses previous device information available in $s20Table.
    //
    updNameAndTimerAfterOn($mac,$s20Table);
    return $s20Table[$mac]['name'];
}

function updNamesAndTimerAfterOn($s20Table){
    //
    // Loos through all S20 regiestered in $s20Table and
    // fills the name in each entry
    // 
    // 
    foreach($s20Table as $mac => $devData){
        updNameAndTimerAfterOnDevice($mac,$s20Table);
    }    
    return $s20Table;
}

function initS20Data(){
    //
    // Search all sockets in the network, and returns 
    // an associative array with all collected data,
    // including names
    //
    // echo "Init S20<p>";
    $s20Table = searchS20();
    $s20Table = updNamesAndTimerAfterOn($s20Table);
    updTableTimers($s20Table);
    return $s20Table;
}

function checkStatus($mac,&$s20Table){
    //
    // Checks the power status of the S20 speciifed by
    // mac adresss $mac using available information in 
    // $s20Table. This is basically done with a subscribe 
    // function (see above)
    // 
    return subscribe($mac,$s20Table);
}

function updateAllStatus($s20Table){
    //
    // This function updates the power status of all S20 in $allAllS20Data.
    //
    // InitS20Data also fills the power status when it is called.
    // However, this function is more efficient if the $s20Table
    // was already initialized and one just wants to update the 
    // power status of all S20s
    //
    // echo "Update all status <p>";
    foreach($s20Table as $mac => $devData){
        $s20Table[$mac]['st'] = checkStatus($mac,$s20Table);
    }
    updTableTimers($s20Table);
    return $s20Table;
}

function sendAction($mac,$action,&$s20Table){
    //
    // Sends an $action (ON=1, OFF = 0) to S20 specified by $mac
    // It retries until a proper reply is received with the desired 
    // power status
    // However, we have detected that the reported power status just
    // after an action fails sometimes, and therefore you should not
    // use this function alone. Prefer switchAndCheck() below, which 
    // performs a double check of the final state.
    //
    subscribe($mac,$s20Table);
    $msg = ACTION.$mac.TWENTIES; 
    if($action)
        $msg .= ON;
    else
        $msg .= OFF;

    $hexRecMsg = createSocketSendHexMsgWaitReply($mac,$msg,$s20Table);

    $status = (int) hexdec(substr($hexRecMsg,-2,2));    
}

function actionAndCheck($mac,$action,&$s20Table){
    /*
      This function implements a switch and check satus.
      The check is in fact a double check and should not 
      be required, since the sendAction function checks the 
      power status itself in the reply command and only gives up
      when the correct reply is received. 
      Nevertheless, we have seen the S20 fail abd report the 
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
        sendAction($mac,$action,$s20Table);
        $st = checkStatus($mac,$s20Table);
        if($st == $action){
            $stay = 0;
        }
        else{
            $logmsg = "switch action FAILED, repeating:\n".
                    " (ordered=".$action." checked=".$st.")\n";
            error_log($logmsg);
        }
    } 
    if(DEBUG) 
        error_log("Number of retries actionAndCheck() = ".$loop_count."\n");
    return $st;
}

function getMacFromName($name,$s20Table){
//
// Returns the $mac address of the S20 with name $name
//
    $count = 0;
    foreach($s20Table as $imac => $devData){
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

    return $mac;
}

function sendActionByDeviceName($name,$action,$s20Table){
    //
    // Sends an action to device designates with $name
    //    
    $mac = getMacFromName($name,$s20Table);
    return actionAndCheck($mac,$action,$s20Table);
}
?>

