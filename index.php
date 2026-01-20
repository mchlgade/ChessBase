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

$pagename = "ChessWeb";

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
$notationknight = 'S';
$notationbishop = 'L';
$notationrook = 'T';
$notationqueen = 'D';
$notationking = 'K';

// Michael Gade vs. Katrine Cæcilie Rosenkilde
$currentgame = 'e2e4 e7e5 g1f3 b8c6 d2d4 e5d4 c2c3 d4c3 f1c4 f8c5 e1g1 h7h6 c4f7 e8f7 d1d5 f7e8 d5c5 d7d6 c5h5 e8f8 e4e5 d6e5 f3e5 c6e5 h5e5 g8f6 b1c3 d8e7 c1f4 e7e5 f4e5 f6e8 a1d1 f8g8 c3d5 c7c6 d5e7 g8h7 d1d8 e8f6 f1d1 c8e6 d8h8 h7h8 b2b3 a8e8 e7g6 h8h7 g6f4 e6b3 a2b3 e8e5 g2g3 f6d5 f4d3 d5c3 d1a1 e5b5 a1a7 b5b3 d3c5 b3b1 g1g2 b7b6 c5e6 b6b5 e6g7 h7h8 g7e6 b5b4 e6c5 b4b3 a7b7 b3b2 c5d3 c3d1 f2f4 c6c5 f4f5 d1e3 g2h3 e3f5 d3b2 c5c4 b7f7 c4c3 b2d3 f5d4 f7c7 c3c2 c7c4 h8h7 c4d4 c2c1 d3c1 b1c1 d4g4 c1c6 g4h4 c6g6 g3g4 h7g7 h3g3 g7h7 h4h5 h7g7 g3f4 g7h7 h2h4 h7g7 g4g5 h6g5 h4g5 g7g8 f4f5 g8g7 h5h4 g6b6 h4c4 b6b5 f5g4 g7g8 g4h5 g8g7 c4c7 g7g8 h5h6 b5b8 g5g6 b8a8 c7h7 a8a6 h7b7 a6a8 g6g7 a8a6 h6g5 a6a8 g5f6 a8a6 f6g5 a6g6';
$currentresult = '1/2 - 1/2';
$startposition = 'rnbqkbnr/pppppppp/11111111/11111111/11111111/11111111/PPPPPPPP/RNBQKBNR'; // FEN Start
$currentposition = $startposition;
$step = 0;

$boardsize = 500;
$flip = 0;
$dark = '#769656';
$lite = '#eeeed2';
$highlite = '#fdfd33';
$action = '#fd3333';

if (isset($_COOKIE['CB'])) $cookie = $_COOKIE['CB'];
if (isset($_REQUEST['function'])) $function = $_REQUEST['function'];
if (isset($_REQUEST['pass1'])) $pass1 = $_REQUEST['pass1'];
if (isset($_REQUEST['pass2'])) $pass2 = $_REQUEST['pass2'];
if (isset($_REQUEST['user_name'])) $user_name = $_REQUEST['user_name'];
if (isset($_REQUEST['flip'])) $flip = $_REQUEST['flip'];
if (isset($_REQUEST['step'])) $step = $_REQUEST['step'];
if (isset($_REQUEST['select'])) $select = $_REQUEST['select'];

$maxstep = count(explode(' ', $currentgame));
$currentpgn = CBgetsan($currentgame);

if($select) $step = $maxstep;
if($step % 2 == 1) {
	$movecolour = 'Black';
} else {
	$movecolour = 'White';
}

if($step > 0) {
	$moves = explode(' ', $currentgame);
	for($i=0;$i<$step;$i++) {
		$thismove = $moves[$i];
		$thisfrom = $thismove[0] . $thismove[1];
		$thisto = $thismove[2] . $thismove[3];
		CBmovepiece($thisfrom, $thisto);
	}
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
		header("Location: ./index.php");
	break;
	case 'newuser':
		CBcreatenewuser();
	break;
}

$out = CBdisplayhead() . CBdisplaytop() . CBdisplaymain($id) . CBdisplayend();
echo $out;


