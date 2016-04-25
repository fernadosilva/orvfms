<?php

/************************************************************************
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
  Generic constants for the orvfms S20 control library.

  This program was developed independently and it is not
  supported or endorsed in any way by Orvibo (C).

*/

//
// You should update the IP broadcast to your local network.
// If you are in a domestic network, it will be almost surely
// 192.168.XXX.255, with X probably being either 0 or 1.
// The broadcast address is required to find all S20 in your 
// network.
// Unhappily, there is no portable and "clean" way of
// finding it. Of course, there are several tricks that could 
// be done, but they would be system depedent and prone to
// failures.
//
define("IP_BROADCAST","192.168.1.255");

// We strongly advise to use here a permanent working directory in the
// line below, (for example, define("TMP_DIR","/mytmp"). See INSTALLATION 
// for more information on this subject.
define("TMP_DIR",""); 

define("PORT",10000);
define("TWENTIES","202020202020");
define("FOUR_ZEROS", "00000000");
define("DISCOVERY_MSG","686400067161");
define("SUBSCRIBE","6864001e636C");
define("ACTION","686400176463");
define("ON" ,"0000000001");
define("OFF","0000000000");
define("ONT" ,"01");
define("OFFT","00");
define("MAX_RETRIES",20);
define("TIMEOUT",0.3); // Max time to wait for a reply from S20, in seconds
define("MAGIC_KEY","6864");
define("BUFFER_SIZE",500);
define("WRITE_SOCKET_CODE","746D");
define("SEARCH_IP","7167");
define("DEBUG",0);
define("NUMBER_OF_NEXT_ACTIONS_DISPLAYED_IN_MAIN_PAGE",0);
define("LOCAL_FILE_NAME","orvfms.dat");
define("SCENE_FILE_NAME","scene_orvfms.dat");
define("NULL_MAC","000000000000");
?>



