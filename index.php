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

$pagename = "ChessBase til Valby Skakklub";

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
$map = null;
$select = null;
$currentmap = '<map name="workmap">';
$currentgame = 'e2e4 e7e5 g1f3 b8c6 d2d4 e5d4 f1c4 g8f6 e1g1';
$currentpgn = CBgetpgn($currentgame);
$currentposition = 'rnbqkbnr/pppppppp/11111111/11111111/11111111/11111111/PPPPPPPP/RNBQKBNR'; // FEN Start
$maxstep = count(explode(' ', $currentgame));
$step = 0;

$boardsize = 560;
$flip = 0;
$dark = '#769656';
$lite = '#eeeed2';
$highlite = '#fdff15';
$action = '#eaaeaa';

if (isset($_COOKIE['CB'])) $cookie = $_COOKIE['CB'];
if (isset($_REQUEST['function'])) $function = $_REQUEST['function'];
if (isset($_REQUEST['pass1'])) $pass1 = $_REQUEST['pass1'];
if (isset($_REQUEST['pass2'])) $pass2 = $_REQUEST['pass2'];
if (isset($_REQUEST['user_name'])) $user_name = $_REQUEST['user_name'];
if (isset($_REQUEST['flip'])) $flip = $_REQUEST['flip'];
if (isset($_REQUEST['step'])) $step = $_REQUEST['step'];
if (isset($_REQUEST['select'])) $select = $_REQUEST['select'];

if($select) $step = $maxstep;
if($step % 2 == 1) {
	$movecolour = 'Black';
} else {
	$movecolour = 'White';
}

$moves = explode(' ', $currentgame);
for($i=0;$i<$step;$i++) {
	$thismove = $moves[$i];
	$thisfrom = $thismove[0] . $thismove[1];
	$thisto = $thismove[2] . $thismove[3];
	CBmovepiece($thisfrom, $thisto);
}

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


