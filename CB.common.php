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

function CBdisplaymenu( $print_on = true )
{
	global $function;
	
	$currentuser = CBgetcurrentuser();

	$out = "\n\n".'<!-- MENU START --><div class="menu"><a href="."><img class="logo" alt="Logo" src="./img/valby.png" /></a><a class="button home" href=".">Home</a>';

	if($currentuser) {	
		if($function == 'users') {
			$out .= "\n<a class=\"activebutton\" href=\"?function=users\">Users</a>";
		} else {
			$out .= "\n<a class=\"button\" href=\"?function=users\">Users</a>";
		}
	}

	if( ( !$currentuser ) && ( $function <> 'login' ) ) {
		$out .= "\n<a class=\"button\" href=\"?function=login\">Login</a>";
	}
	if( ( !$currentuser ) && ( $function == 'login' ) ) {
		$out .= "\n<a class=\"activebutton\" href=\"?function=login\">Login</a>";
	}

	$out .= "\n</div><div class=\"inlineclear\"></div>";

	return $out;
}

// ============================================================================

function CBdisplaymain( $id ) {
	global $function;

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
	}

	// ======================================

	if($frontpage) {
		$out .= CBdisplayfrontpage( false );
	}

	$out .= "\n</div>";
	return $out;
}

// ============================================================================

function CBdisplayend( $print_on = true )
{
	global $Version;

	$out = "\n\n".'<!-- END START -->
<div class="inlineclear"></div>
<div class="end"><a href="https://github.com/mchlgade/ChessBase">github.com/mchlgade/ChessBase</a> <b>'.$Version.'</b><br />
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
	global $pagename;
	global $id;

	//default
	$title = "~ " . $pagename . " ~";

	$out = '<p class="pagetitle">';

	switch($function) {
	case 'login':
		$title = 'Login';
	break;
	case 'user':
		$title = CBgetuserhandle(CBgetcurrentuserID());
	break;
	}

	$out .= $title .'</p>';
	return $out;
}

// ============================================================================

function CBdisplayfrontpage( ) {
	global $out;
	global $currentposition;
	global $boardsize;
	global $reversed;
	global $dark;
	global $lite;

	$out .= '<img style="float:right;margin-left:20px;margin-bottom:10px" src="./img/about.png" />';

	// output currentposition as image
	ob_start();
        CBdisplayboard($currentposition,$boardsize,$reversed,$dark,$lite);
        $rawImageBytes = ob_get_clean();

        $out .= '<img style="float:left;margin-left:20px;margin-bottom:10px" src="data:image/png;base64,' 
        . base64_encode( $rawImageBytes ) 
        . '" />';
	
	$out .= '<p class="BoxText" style="text-align:center">';

	$result = CBfiresql("SELECT id FROM game WHERE status=3 ORDER BY posted_on DESC LIMIT 20");
	for($row=0;$row<pg_numrows($result);$row++) {
		$thisrow = pg_Fetch_Object($result,$row);
		$thisid = $thisrow->id;

		$out .= "\n<a href=\"?game=view&amp;id=$thisid\">
<img class=\"FrontCover\" alt=\"Cover\" src=\"./covers/cover$thisid\" /></a>";
	}
	$out .= "\n</p>";

	return $out;
}

// ============================================================================

function CBgetlatestcomment( $print_on = true ) {
	$result = CBfiresql("SELECT author,body,level,thread_id,posted_on FROM forum WHERE level > 0 ORDER BY posted_on DESC LIMIT 1");
	$thisrow = pg_Fetch_Object($result,0);
	$thishandle = $thisrow->author;
	$thisuserID = CBgetuserID( $thishandle );
	$thisbody = nl2br($thisrow->body);
	$thisrating = $thisrow->level;
	$thisgame = $thisrow->thread_id;
	$thisdate = CBfixdate($thisrow->posted_on);
	
	if( !file_exists( './players/'.$thisuserID.'.png' ) ) {
		$image = 'Anonymous';
	} else {
		$image = $thisuserID;
	}
	
	$result = "<div class=\"box\"><div class=\"boxheader\"><a href=\"?game=view&amp;id=$this\"><img class=\"FrontCover\" style=\"float : right;margin : 0;margin-left : 10px;margin-bottom : 5px\" src=\"./covers/cover$thisdocument\" /></a><img class=\"docicon\" src=\"./users/$image.png\" /> &nbsp;" . getRatingDisplay($thisrating) . "</div><div class=\"boxtext\"><sup>Added by : <b>$thishandle</b> (<i>$thisdate</i>)</sup><br />$thisbody</div><div class=\"inlineclear\"></div></div>";
	return $result;
}

// ============================================================================

function CBdisplay( $text , $type, $print_on = true ) {
	$out = '';
	switch($type) {
	case '0':
		$out .= "\n<p class=\"Error\">$text</p>";
	break;
	case '1':
		$out .= "\n<p class=\"Head1\">$text</p>";
	break;
	case '2':
		$out .= "\n<p class=\"Head2\">$text</p>";
	break;
	case '3':
		$out .= "\n<p class=\"Head3\">$text</p>";
	break;
	case '4':
		$out .= "\n<p class=\"ParaIndent\">$text</p>";
	break;
	case '5':
		$out .= "\n<p class=\"ParaBlankOver\">$text</p>";
	break;
	case '6':
		$out .= "\n<p class=\"QuoteIndent\">$text</p>";
	break;
	case '7':
		$out .= "\n<p class=\"QuoteBlankOver\">$text</p>";
	break;
	case '8':
		$out .= "\n<p class=\"ParaNoIndent\">$text</p>";
	break;
	case '9':
		$out .= "\n<p class=\"QuoteNoIndent\">$text</p>";
	break;
	case '17':
		$out .= "\n<p class=\"PreBlankOver\">$text</p>";
	break;
	case '18':
		$out .= "\n<p class=\"PreNoIndent\">$text</p>";
	break;
	case '20':
		$out .= "\n<p class=\"Picture\">$text</p>";
	break;
	case '21':
		$out .= "\n<table class=\"main\">\n<tr>\n<td>$text</td>";
	break;
	case '22':
		$out .= "\n<td>$text</td>";
	break;
	case '23':
		$out .= "</tr>\n<tr>\n<td>$text</td>";
	break;
	case '24':
		$out .= "\n<td>$text</td>\n</tr>\n</table>";
	break;
	case '25':
		$out .= "\n<ul><li>$text";
	break;
	case '26':
		$out .= "</li>\n<li>$text";
	break;
	case '27':
		$out .= "</li>\n<li>$text</li></ul>";
	break;
	case '28':
		$out .= "\n<ol><li>$text";
	break;
	case '29':
		$out .= "</li>\n<li>$text";
	break;
	case '30':
		$out .= "</li>\n<li>$text</li></ol>";
	break;
	case '31':
		$out .= "\n<p class=\"HangingBlankOver\">$text</p>";
	break;
	case '32':
		$out .= "\n<p class=\"HangingIndent\">$text</p>";
	break;
	case '33':
		$out .= "\n<p class=\"ParaVignet\">$text</p>";
	break;
	case '34':
		$out .= "\n<div class=\"BoxStart\">";
	break;
	case '35':
		$out .= "\n</div>";
	break;
	case '36':
		$out .= "\n<p class=\"BoxHead\">$text</p>";
	break;
	}
	return $out;
}


// ============================================================================

function CBdisplaymanual( $print_on = true )
{
	setTimeZone();
	$out = '';
	CBdisplay( 'A helpful documentation for all of you that are willing to rise from ordinary Reader to Librarian or are eager to know sligtly more about this place and how it works.', 8, false );
	return $out;
}

// ============================================================================

function CBdisplayusers( $print_on = true )
{
	$result = CBfiresql("SELECT id,user_name,karma,irc,xmpp,diaspora,mastodon,ricochet FROM \"user\" WHERE karma > 1 ORDER BY karma DESC");
	
	for( $row=0; $row < pg_numrows( $result ); $row++ ) {
		$thisrow = pg_Fetch_Object( $result, $row );
		$thisid = $thisrow->id;	
		$thisusername = $thisrow->user_name;
		$thiskarma = $thisrow->karma;
		$thiskarma = '(' . CBgetrating($thiskarma) . ')';
		$diaspora = $thisrow->diaspora;
		$mastodon = $thisrow->mastodon;
		$xmpp = $thisrow->xmpp;
		$irc = $thisrow->irc;
		$ricochet = $thisrow->ricochet;
		
		$out .= "\n".'<div class="boxheader"><b><a href="./?function=user&id='.$thisid.'">'.$thisusername.'</a></b> '.$thiskarma.'</div><div class="boxtext"><small>';
		
		if(file_exists("./users/".$thisid.".png")) {	
			$out .= "<img style=\"float:left\" src=\"./users/".$thisid.".png\">";
		} else {
			$out .= "<img style=\"float:left\" src=\"./users/Anonymous.png\">";
		}
				
		if($xmpp) $out .= "<b>XMPP</b>&nbsp;:&nbsp;$xmpp ";
		if($irc) $out .= "<br/><b>IRC</b>&nbsp;:&nbsp;$irc ";
		if($diaspora) $out .= "<br/><b>Diaspora*</b>&nbsp;:&nbsp;$diaspora ";
		if($mastodon) $out .= "<br/><b>Mastodon</b>&nbsp;:&nbsp;$mastodon ";
		if($ricochet) $out .= "<br/><b>Ricochet</b>&nbsp;:&nbsp;$ricochet ";
		$out .= "</small></div><div class=\"inlineclear\"> </div>";		
	}
	return $out;
}

// ============================================================================

function CBdisplayplayers( $print_on = true )
{
	$out = '';
	$sql = CBfiresql( "SELECT DISTINCT(handle) AS owner, COUNT(handle) AS docs, MIN(posted_on) AS first, MAX(posted_on) AS last FROM game WHERE status=3 GROUP BY owner ORDER BY docs DESC, first DESC" );
	for( $row=0; $row < pg_numrows( $sql ); $row++ ) {
		$thisrow = pg_Fetch_Object( $sql, $row );
		$thisuser = $thisrow->owner;
		$thisuserID = CBgetuserID( $thisuser );
		$numdocs = CBgetrating( $thisrow->docs );
		$daysactive = abs((strtotime($thisrow->last) - strtotime($thisrow->first)) / (60*60*24)) + 1;
		// +1 because from today to today is 1 day and not 0
		// avoids division by zero on users active for just 1 day (Michael)
		$gamesperweek = getNumberFormatted( ($thisrow->docs / $daysactive)*7 ,1);

		if( !file_exists( './users/'.$thisuserID.'.png' ) ) {
			$image = 'Anonymous';
		} else {
			$image = $thisuserID;
		}

		$out .= "\n".'<div class="librarian box">
<div class="boxheader"><img class="docicon" src="./users/'.$image.'.png" /><b>'.$thisuser.'</b> ('.$numdocs.')</div>
<div class="boxtext">Added <b>' .$thisrow->docs .'</b> games between <b>' .CBfixdate( $thisrow->first ) .'</b> and <b>' .CBfixdate( $thisrow->last ) .'</b> (~<b>' .$gamesperweek .'</b>&nbsp;games/week)</div><div class="inlineclear"></div></div>';

	}
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

// ============================================================================

function checkSettings( $settingsFilename ) {
  if (! is_readable( $settingsFilename ) ) {
      die('ERROR: Configuration in settings.php not readable or missing!');
  }
// todo: more tests on settings
// e.g. check if salt is set properly, if not die('settings: need the salt to be set properly')
// if (empty($secret_salt)) { die('ERROR: Setting: need salt to be set properly. Current value:'.$secret_salt); }
}
