<?php
// ============================================================================
//  "Frontpage" for ChessBase
//  Copyright (C) Michael Gade
//
//  This program is free software; you can redistribute it and/or modify
//  it under the terms of the GNU General Public License as published by
//  the Free Software Foundation; either version 3 of the License, or
//  (at your option) any later version.
//
//  This program is distributed in the hope that it will be useful,
//  but WITHOUT ANY WARRANTY; without even the implied warranty of
//  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//  GNU General Public License for more details.
// ============================================================================

error_reporting(E_ALL ^E_NOTICE ^E_DEPRECATED);
//error_reporting(E_ALL ^E_STRICT);
ini_set('display_errors', '1');

$pagename = "Valby Skakklub";
$Version = "";

require 'settings.php';
require 'CB.common.php';
require 'CB.database.php';
require 'CB.helper.php';
require 'CB.user.php';
require 'CB.forum.php';
require 'CB.chess.php';

$id = 0;
$function = null;
$player = null;
$pass1 = null;

$currentposition = 'rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR w KQkq - 0 1'; // FEN start
$step = 0;

$boardsize = 450;
$reversed = 0;
$dark = '#769656';
$lite = '#eeeed2';

if (isset($_COOKIE['CB'])) $cookie = $_COOKIE['CB'];
if (isset($_REQUEST['function'])) $function = $_REQUEST['function'];
if (isset($_REQUEST['pass1'])) $pass1 = $_REQUEST['pass1'];
if (isset($_REQUEST['pass2'])) $pass2 = $_REQUEST['pass2'];
if (isset($_REQUEST['user_name'])) $user_name = $_REQUEST['user_name'];

switch( $function ) {
	case 'login':
		if($pass1 <> '') {
			CBlogin();
			break;
		}
		break;
	break;
	case 'logout':
		CBlogout();
		header("Location: .");
	break;
	case 'newuser':
		CBcreatenewuser();
	break;
}

$out = CBdisplayhead( )
 .CBdisplaytop( )
 .CBdisplaymain( $id )
 .CBdisplayend( );
 
print $out;


