<?php
// ============================================================================
//  Common functions for ChessBase
//  Copyright (C) Michael Gade
//
//  This program is free software; you can redistribute it and/or modify
//  it under the terms of the GNU General Public License as published by
//  the Free Software Foundation; either version 2 of the License, or
//  (at your option) any later version.
//
//  This program is distributed in the hope that it will be useful,
//  but WITHOUT ANY WARRANTY; without even the implied warranty of
//  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//  GNU General Public License for more details.
// ============================================================================

function CBdisplayhead( $print_on = true ) {
	$pagetitle = CBgetpagetitle();
	$out = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head><title>'.$pagetitle.'</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="Description" content="Scimus quae legis, et non dicimus" />
<link rel="stylesheet" type="text/css" href="./style/default.css"/>
</head>
<body>';
	return $out;
}

// ============================================================================

function CBdisplaytop( $print_on = true )
{
	$out = "\n\n".'<!-- TOP START -->'
	.CBdisplaymenu( false )
	.CBdisplaytitle( false )
	."\n";
	return $out;
}

// ============================================================================

// assemble button for navigation
function CBMenuButton( $btntile, $href = "", $class = "", $decoration = 'star' ) {
	return  "\n<a class=\"$class $decoration\" href=\"$href\">$btntitle</a>";
}

// ============================================================================

function CBdisplaymenu( ) {
	global $function;
	global $game;
	
	$currentuser = CBgetcurrentuser();
	$out = "<!-- MENU START --><div class=\"menu\">&nbsp;";
	$out .= '<img class="logo" src="img/logo.svg"/>';
	if(($function == "") && ($game <> 'new')) {
		$out .= "\n\n".'<a class="activebutton home" href=".">Nyheder</a>';
	} else {
		$out .= "\n\n".'<a class="button home" href=".">Nyheder</a>';
	}

	if($function == 'members' || $function == 'addmember') {
		$out .= "\n<a class=\"activebutton spark\" href=\"?function=members\">Medlemmer</a>";
	} else {
		$out .= "\n<a class=\"button spark\" href=\"?function=members\">Medlemmer</a>";
	}
	
	if($function == 'games') {
		$out .= "\n<a class=\"activebutton spark\" href=\"?function=games\">Partier</a>";
	} else {
		$out .= "\n<a class=\"button spark\" href=\"?function=games\">Partier</a>";
	}
	
	if($currentuser) {
		$title = CBgetuserhandle(CBgetcurrentuserID());
		if(($function == 'user') || ($game == 'new')) {
			$out .= "\n<a class=\"activebutton email\" href=\"?function=user\">" . $title . "</a>";
		} else {
			$out .= "\n<a class=\"button email\" href=\"?function=user\">" . $title . "</a>";
		}
		$out .= "\n<a class=\"button prev\" href=\"?function=logout\">Logout</a>";
	}

	if( ( !$currentuser ) && ( $function <> 'login' ) ) {
		$out .= "\n<a class=\"button next\" href=\"?function=login\">Login</a>";
	}
	if( ( !$currentuser ) && ( $function == 'login' ) ) {
		$out .= "\n<a class=\"activebutton next\" href=\"?function=login\">Login</a>";
	}

	return $out;
}

// ============================================================================

function CBdisplaymain( $id ) {
	global $function;
	global $game;

	$out = "\n\n".'<!-- MAIN START -->'."\n\n".'<div class="main">';

	$frontpage = true; // HACK

	// ======================================

	switch( $function ) {
	case 'login':
		$out .= CBdisplaysignup( );
		$frontpage = false;
	break;
	case 'newuser':
		CBcreatenewuser();
		$frontpage = false;
	break;
	case 'user':
		$out .= CBdisplayuserpage( );
		$frontpage = false;
	break;
	case 'members':
		$out .= CBdisplaymembers();
		$frontpage = false;
	break;
	case 'games':
		$out .= CBdisplaygames();
		$frontpage = false;
	break;
	case 'addmember':
		$out .= CBdisplayaddmember();
		$frontpage = false;
	}
	
	// ======================================
	
	switch ($game) {
	case 'new':
		$out .= CBdisplaynewgame();
		$frontpage = false;
	
	}
	
	// ======================================

	if($frontpage) {
		$out .= CBdisplayfrontpage( false );
	}

	$out .= "\n</div>";
	return $out;
}

// ============================================================================

function CBdisplayend( )
{
	$out = "\n\n".'<!-- END START -->
<div class="inlineclear"></div>
<div class="end"><a class="button like" href="https://github.com/mchlgade/ChessBase">&nbsp;&nbsp;GitHub.com/mchlgade/ChessBase&nbsp&nbsp;</a><br />
<div class="inlineclear"></div>
<a href="https://www.catb.org/hacker-emblem/"><img style="border: 0; margin : 5px;" src="./img/hacker.png"/></a>
</div>
</body>
</html>
<!-- END OF LINE -->';
	return $out;
}

// ============================================================================

function CBdisplaytitle( ) {
	global $function;
	global $game;
	global $pagename;
	global $id;
	global $currentposition;
	global $currenttournament;
	global $currentround;
	global $currentdate;

	//default
	$title = $pagename;

	$out = '<p class="pagetitle">';

	switch($function) {
	case 'login':
		$title = 'Login';
	break;
	case 'user':
		$title = CBgetuserhandle(CBgetcurrentuserID());
	break;
	case 'members':
		$title = 'Medlemmer';
	break;
	case 'addmember':
		$title = 'Tilføj Spiller';
	break;
	case 'games':
		$title = $currenttournament;
	break;
	}

	switch($game) {
	case 'new':
		$title = 'Tilføj Parti';
	break;
	}
	
	$out .= $title .'</p></div>';
	return $out;
}

// ============================================================================

function CBdisplayfrontpage( ) {
	$user = CBgetcurrentuser();
	$out = "\n<div class=\"box\"><div class=\"boxheader\"><b>Nyheder</b></div>";
	$out .= "\n<div class=\"boxtext\">Nyheder fra Valby Skakklub ... tilgår.</div></div>";

	if($user <> '') {
		$out .= "\n<p class=\"boxtext\"><a class=\"button add\" href=\"?function=addnews\">Add News</a></p></div>";
	}
	return $out;
}

// ============================================================================

function CBdisplaymembers() {
	$user = CBgetcurrentuser();
	
	$out = "\n<div class=\"box\"><div class=\"boxheader\"><b>Valby Skakklub</b></div>";
		
	$result = CBfiresql("SELECT id,player,club,fideid FROM player WHERE club='Valby Skakklub' ORDER BY player");
	
	for( $row=0; $row < pg_numrows( $result ); $row++ ) {
		$thisrow = pg_Fetch_Object( $result, $row );
		$thisid = $thisrow->id;	
		$thisplayer = $thisrow->player;
		$thisclub = $thisrow->club;
		$fideid = $thisrow->fideid;
		
		$out .= "\n".'<div class="boxtext"><a class="move" href="https://ratings.fide.com/profile/'.$fideid.'">'.$fideid.'</a> : <b><a class="move" href="./?function=members&id='.$thisid.'">'.$thisplayer.'</a></b></div>';
				
	}
	$out .= "\n</div>";
	
	$out .= "\n<div class=\"box\"><div class=\"boxheader\"><b>Andre Klubber</b></div>";

	$result = CBfiresql("SELECT id,player,club,fideid FROM player WHERE club<>'Valby Skakklub' ORDER BY player");
	
	for( $row=0; $row < pg_numrows( $result ); $row++ ) {
		$thisrow = pg_Fetch_Object( $result, $row );
		$thisid = $thisrow->id;	
		$thisplayer = $thisrow->player;
		$thisclub = $thisrow->club;
		$fideid = $thisrow->fideid;
		
		$out .= "\n".'<div class="boxtext"><a class="move" href="https://ratings.fide.com/profile/'.$fideid.'">'.$fideid.'</a> : <b><a class="move" href="./?function=members&id='.$thisid.'">'.$thisplayer.'</a></b> : '. $thisclub .'</div>';		
	}
	$out .= "\n</div>";
	
	if($user <> '') {
		$out .= "\n<p class=\"boxtext\"><a class=\"button add\" href=\"?function=addmember\">Add Member</a></p>";
	}
	return $out;
}

// ============================================================================

function CBdisplaygames() {
	global $out;
	global $currentposition;
	global $currentgame;
	global $currentresult;
	global $currentpgn;
	global $currentmap;
	global $movecolour;
	global $castleimg;
	global $maxstep;
	global $map;
	global $fieldsize;
	global $flip;
	global $dark;
	global $lite;
	global $step;
	global $select;
	global $lastfrom;
	global $lastto;
	global $whitename;
	global $whiteelo;
	global $whiteclub;
	global $blackname;
	global $blackelo;
	global $blackclub;
	
	// output currentposition as image
	ob_start();
        CBdisplayboard($currentposition,$fieldsize*8,$flip,$dark,$lite);
        $raw = ob_get_clean();
        $out .= '<img class="chessboard" src="data:image/png;base64,' 
        . base64_encode( $raw ) 
        . '" usemap="#workmap"/>';
        $out .= $currentmap;

	$out .= "\n<table style=\"height:".($fieldsize*8)."px;margin:0;padding:0\">";
	if($flip) {
		$out .= "\n" . '<tr style="height:'.$fieldsize.'px;margin:0;padding:0"><td><div class="playernames"><b>'.$whitename.'</b></div>&nbsp;&nbsp; ('.$whiteelo.') <b>'.$whiteclub.'</b></td></tr>';
	} else {
		$out .= "\n" . '<tr style="height:'.$fieldsize.'px;margin:0;padding:0"><td><div class="playernames"><b>'.$blackname.'</b></div>&nbsp;&nbsp; ('.$blackelo.') <b>'.$blackclub.'</b></td></tr>';
	}
	
	$out .= '<tr><td>'. "\n" . '<div class="moves">' . $currentpgn . '</div></td></tr>';
	
	if($flip) {
		$out .= "\n" . '<tr style="height:'.($fieldsize).'px;margin:0;padding:0"><td><div class="playernames"><b>'.$blackname.'</b></div>&nbsp;&nbsp; ('.$blackelo.') <b>'.$blackclub.'</b></td></tr>';
	} else {
		$out .= "\n" . '<tr style="height:'.($fieldsize).'px;margin:0;padding:0"><td><div class="playernames"><b>'.$whitename.'</b></div>&nbsp;&nbsp; ('.$whiteelo.') <b>'.$whiteclub.'</b></td></tr>';
	}
	$out .= "\n</table>";
	
	$out .= "\n" . '<div class="inlineclear"/>';
	if($step > 0) {
		$out .= "\n" . '<a class="button prev" href="?function=games&flip='.$flip.'&step=' .($step - 1). '">Prev</a>';
	} else {
		$out .= "\n" . '<a class="button prev" href="?function=games&flip='.$flip.'&step=0">Prev</a>';
	}
	if($flip) {
		$out .= "\n" . '<a class="button" href="?function=games&flip=0&step='.$step.'">Flip</a> ';
	} else {
		$out .= "\n" . '<a class="button" href="?function=games&flip=1&step='.$step.'">Flip</a> ';
	}
	if($step < $maxstep) {
		$out .= "\n" . '<a class="button next" href="?function=games&flip='.$flip.'&step=' .($step + 1). '">Next</a>';
	}
	
	$out .= "\n" . '</div>';
	
	return $out;
}

// ============================================================================

function CBdisplayaddmember() {
	$out = "\n" . '<div class="box"><div class="boxheader"><b>Add new player</b></div>' . '<table><form method="post" action="?function=newplayer"><fieldset>
<tr><td>Player name </td><td>: <input type="text" size="50" name="player"/></td></tr>
<tr><td>Club </td><td>: <input type="text" size="50" name="club"/></td></tr>
<tr><td>FIDE id </td><td>: <input type="text" size="10" name="fideid"/></td></tr>
<tr><td></td><td><input class="formbutton" type="submit" value="Add player"/></td></tr>
</fieldset></form></table></div></div>';
	return $out;
}

// ============================================================================

function CBdisplaynewgame() {
	$now = date_create('now')->format('Y-m-d H:i:s');
	$dato = CBfixdate($now);
	
	$result = CBfiresql("SELECT player,fideid FROM player ORDER BY player");
	$whiteplayer = '<option value="0">-- Select White Player --</option>';
	$blackplayer = '<option value="0">-- Select Black Player --</option>';
	for($row=0;$row<pg_numrows($result);$row++) {
		$thisrow = pg_Fetch_Object($result,$row);
		$thisplayer = $thisrow->player;
		$thisfide = $thisrow->fideid;
		$whiteplayer .= "\n".'<option value="'.$thisfide.'">'.$thisplayer.'</option>';
		$blackplayer .= "\n".'<option value="'.$thisfide.'">'.$thisplayer.'</option>';
	}	
	
	$out = "\n" . '<div class="box"><div class="boxheader"><b>Add game</b></div>' . '<table><form method="post" action="?function=newgame"><fieldset>
<tr><td>White</td><td> : <select class="norm" name="white">'.$whiteplayer.'</select></td>
<td> Rating</td><td> : <input type="text" size="5" name="whiterating"/></td></tr>
<tr><td>Black</td><td> : <select class="norm" name="black">'.$blackplayer.'</select></td>
<td> Rating</td><td> : <input type="text" size="5" name="blackrating"/></td></tr>
<tr><td>Tournament</td><td colspan=3> : <input type="text" size="50" name="tournament"/></td></tr>
<tr><td>Round</td><td> : <input type="text" size="10" name="round"/></td></tr>
<tr><td>Date</td><td> : <input type="text" size="10" name="date" value="'.$dato.'"/></td></tr>

<tr><td colspan=3></td><td><input class="formbutton" type="submit" value=" Add Game "/></td></tr>
</fieldset></form></table></div></div>';
	return $out;
}

// ============================================================================

/* ewa: single place to influence how a number looks like,
 *   negative $decplaces is for vanishing zeroes at the end as positive sets zeroes
 * */

function getNumberFormatted( $n, $decplaces = 2, $decsep = '.', $tsdsep = ',' ) {
	if( $decplaces < 0 ) {
		$decplaces = abs( $decplaces );
		$n = ''.round( $n , $decplaces );
		$n = number_format( $n, $decplaces, $decsep, $tsdsep );
		$n = preg_replace( array( '/\\'.$decsep.'+0+$/', '/(\\'.$decsep.'+[0-9]*)0+$/' ), array( '', '\1' ), $n/** /, -1, $cnt/**/ );
	} else {
		$n = number_format( $n, $decplaces, $decsep, $tsdsep );
	}
	return $n;
}

