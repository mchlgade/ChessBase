<?php
/*============================================================================
// Helper functions for ChessBase
// Copyright (C) Michael Gade
//
// This program is free software; you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation; either version 2 of the License, or
// (at your option) any later version.
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
// ============================================================================*/

function CBgetpagetitle()
{
	global $function, $message, $game, $player, $id, $step, $news, $forum;

	$title = "Yet Another ChessBase Clone";

	switch( $player ) {
	case 'view':
		$authorname = CBgetplayername( $id );
		$title = "$authorname";
	break;
	}

	switch( $game ) {
	case 'view':
		$title = CBgetgametitle( $id );
	break;
	}

	switch( $function ) {
	case 'players':
		$title = "Players";
	break;
	}

	switch( $news ) {
	case 'view':
		$title = "Latest News";
	break;
	case 'add':
		$title = "Add news";
	break;
	case 'edit':
		$title = "Edit news";
	break;
	}

	switch( $forum ) {
	case 'view':
		$title = "Forum";
	break;
	}

	return "$title";
}

// ============================================================================

function RMLgetplayername( $id )
{
	if( $id == 0 ) {
		return "Players";
	}
	if ( $result = CBfireSQL( "SELECT name FROM player WHERE id=$id" ) ) {
		$thisrow = pg_Fetch_Object( $result, 0 );
		return $thisrow->name;
	} else {
		return 'Invalid Player ID: '.$id;
	}
}

// ============================================================================

function CBgetgametitle( $id, $print_on = false )
{
	$result = CBfiresql("SELECT white, black FROM game WHERE id=$id");
	if( !( $thisrow = pg_Fetch_Object( $result, 0 ) ) ){
		$out = 'ERROR: No Title for Game ID.';
	} else {
		$out = $thisrow->white . ' vs. ' . $thisrow->black;
	}
	return $out;
}

// ============================================================================

function CBfixdate( $date, $f = 'DD MMM YYYY' )
{
	setTimeZone();
	$date = strtotime( $date );
	switch ( $f ) {
		case 'DD MMM YYYY':
			return strftime( '%d %b %Y', $date );
		case 'U':// seconds since January 1st 1970
			return strftime( '%s', $date );			
		break;
		default:
			return false;
	}
}

// ============================================================================

function CBgetplayerid($player)
{
	$result = CBfiresql( "SELECT id FROM player WHERE name='$player'" );
	if( pg_numrows( $result ) > 0 ) {
		$tmp = pg_Fetch_Object( $result );
		$result = $tmp->id;
	} else {
		$result = CBcreateplayer( $player );
	}
	return $result;
}

// ============================================================================

function CBcreateplayer( $player )
{
	$user = CBgetcurrentuser();

	RMLfiresql( "INSERT INTO player (id,name,maintainer) values(DEFAULT,'$player','$user')" );
	return $authorid;
}

// ============================================================================

function CBdisplaynews( $print_on = true )
{
	$out = '';
	$sql = CBfiresql("SELECT id,headline,body,author,posted FROM news ORDER BY posted DESC LIMIT 20");
	for($row=0;$row<pg_numrows($sql);$row++) {
		$thisrow = pg_Fetch_Object($sql,$row);
		$thisid = $thisrow->id;
		$thishead = $thisrow->headline;
		$thisbody = nl2br($thisrow->body);
		$thisauthor = $thisrow->author;
		$date = RMLfixdate( $thisrow->posted );

		$out .= "\n".'<div class="box">
<div class="boxheader"><b>'.$thishead.'</b></div><div style="text-align:right;padding-right:15px"><small><i>by</i> : <b>'.$thisauthor.'</b> (<i>'.$date.'</i>)</small>'
		.( ( hasRights( 'delnews', array( $thisauthor ) ) )
			? "\n".'<a href="?news=delete&amp;id='.$thisid.'"><img style="float : right;margin-top:-28px" alt="Delete" src="img/delete.png" /></a><br/>'
			//.' <a class="button edit" href="?news=edit">Edit News</a>'
			: ''
		)
		.'</div><div class="boxtext">'.$thisbody.'</div>
</div>'	;
	}
	if( hasRights( 'addnews' ) ) {
		$out .=
		"\n".'<a class="button add" href="?news=add">Add News</a>'
		;
	}
	return $out;
}

// ============================================================================

function CBaddnews( $print_on = true )
{
	if( ! hasRights( 'addnews' ) ) {
		$out = "ERROR: in Add News, you have no right to do this.";
		return false;
	} else {
		$author = RMLgetcurrentuser();
		$out = "\n".'<p class="ParaNoIndent">Hello '.$author.'<br/>
Please keep news to something that is actually news. Other than that, go nuts...<br/>
&nbsp;</p>
<form method="post" action="?news=save"><table class="form">
<tr><td valign="top">Headline : </td><td><input type="text" name="headline" size="60"></td></tr>
<tr><td valign="top">Body : </td><td><textarea class="norm" rows="10" cols="41" wrap="none" name="body"></textarea></td></tr>
<tr><td></td><td><input type="submit" value="Post news"></td></tr></table>
<input type="hidden" name="news" value="save"></form>';
	}
	return out;
}

// ============================================================================

function CBsavenews( $print_on = true )
{
	global $body, $headline;

	$out = '';
	if( ! hasRights( 'addnews' ) ) {
		$out = "ERROR: News Save : Cookie baaaaaaaad...";
	} else {

		$author = CBgetcurrentuser();

		CBfiresql("INSERT INTO news (id,headline,body,author,posted) VALUES(DEFAULT,'$headline','$body','$author',NOW())");
	}
	return $out;
}

// ============================================================================

function CBdeletenews( $id, $print_on = true )
{
	$sql = CBfiresql("SELECT author FROM news WHERE id=$id");
	$thisrow = pg_Fetch_Object($sql,0);

	$thisauthor = $thisrow->author;
	if( ! hasRights( 'delnews', array( $thisauthor ) ) ) {
		$out = 'ERROR: No Rights to delte news';
	} else {
		CBfiresql("DELETE FROM news WHERE id=$id");
	}
	return $out;
}

// ============================================================================

function CBeditnews( $id, $print_on = true )
{
	$out = '';
	if( ! hasRights( 'editnews' ) ) {
		$out = "ERROR : No rights for you.";
	} else {
		if( ! hasRights( 'test' ) ) {
			$out = 'No code in function yet.';
		} else {
			//id,headline,body,author,posted
			$sql = CBfiresql("SELECT * FROM news WHERE id=$id");
			$thisrow = pg_Fetch_Object($sql,0);
			$cu = CBgetcurrentuser();
			$out = "\n".'<p class="ParaNoIndent">Hello ' .$cu .'<br/>
Please keep news to something that is actually news. Other than that, go nuts...<br/>
&nbsp;</p>
<form method="post" action="?news=update&id='.$id.'"><table class="form">
<tr><td valign="top">Headline : </td><td><input type="text" name="headline" size="60" value="'.$thisrow->headline.'"></td></tr>
<tr><td valign="top">Body : </td><td><textarea class="norm" rows="10" cols="41" wrap="none" name="body">'.$thisrow->body.'</textarea></td></tr>
<tr><td></td><td><input type="submit" value="Save news"></td></tr></table>
<input type="hidden" name="news" value="save"></form>'
			;
		}
	}
	return $out;
}

// ============================================================================

function CBupdatenews( $print_on = true )
{
	$out = '';
	if( ! hasRights( 'editnews' ) ) {
		$out = "ERROR : No rights for you.";
	}
	if( ! hasRights( 'test' ) ) {
		$out = 'ERROR: No code in function yet.';
	}
	CBfiresql("UPDATE news SET headline='".$headline."', body='".$body."' WHERE id='$id'");
	return $out;
}

// ============================================================================

function CBgetrating( $number ) {
	if( $number > 1336 ) return "Elite";
	if( $number > 750 ) return "Jedi Master";
	if( $number > 500 ) return "Jedi";
	if( $number > 250 ) return "Zen Master";
	if( $number > 100 ) return "Master";
	if( $number > 75 ) return "Expert";
	if( $number > 50 ) return "Adept";
	if( $number > 25 ) return "Apprentice";
	if( $number > 10 ) return "Novice";
	if( $number > 5 ) return "Amateur";
	if( $number > 1 ) return "Mostly Harmless";
	return "Harmless";
}

// ============================================================================

function CBgeneraterss($print_on = true)
{
	$out = '<?xml version="1.0" encoding="UTF-8" ?>';
	$out .= '<rss xmlns:dc="http://purl.org/dc/elements/1.1/" version="2.0">';
	$out .= '<channel>';
	$out .= '<title>Chess For the Win</title>';
	$out .= '<link>http://valbyskakklub.dk</link>';
	$out .= '<image>';
	$out .= '<url>./img/logo.png</url>';
	$out .= '<link>http://valbyskakklub.dk</link>';
	$out .= '</image>';
	$out .= '<description>All Your Games Are Belong To Us !!!</description>';
	
	$sql = CBfiresql("SELECT id,title,player_id,teaser,posted_on FROM document WHERE status > 2 ORDER BY posted_on DESC LIMIT 20");
	for($row=0;$row<pg_numrows($sql);$row++) {
		$thisrow = pg_Fetch_Object($sql,$row);
		$thisid = $thisrow->id;
		$thistitle = $thisrow->title;
		$thissubtitle = $thisrow->subtitle;
		$thisplayer = CBgetplayername($thisrow->player_id);
		$thisteaser = strip_tags($thisrow->teaser);
		$thisdate = $thisrow->posted_on;
	
		$out .= '<item>';
		$out .= '<title>'.$thistitle.' - '.$thisauthor.'</title>';
		$out .= '<link>http://ncjeamtnv4vpao5cop2lgezdyemopk3bwvzigv2zry4bw2qk6va3e2yd.onion/?document=view&amp;id='.$thisid.'</link>';
		$out .= '<description>'."\n".$thisteaser.'</description>';
		$out .= '<pubDate>'.$thisdate.'</pubDate>';
		$out .= '</item>';
	}	
	
	$out .= '</channel>';
	$out .= '</rss>';
	return $out;
}

// ============================================================================

/* reimplemented
 *
 * suppressing warning for errno 13: 'Permission denied'
 * suppressing warning for filetype: 'Lstat failed'
 * counting filetype 'dir' and 'file' only
 */
function getDirectorySize( $path, $delim='/' )
{
	$r = array('size' => 0, 'count' => 0, 'dircount' => 0);
	if( !file_exists( $path )
		|| false === ( $dirlist = @scandir( $path ) )
	) {
		return false;
	}
	foreach( array_diff( $dirlist, array('.', '..') ) as $file ) {
		$nextpath = $path . $delim . $file;
		switch( @filetype( $nextpath ) ) {
			case 'dir':
				$result = getDirectorySize( $nextpath );
				$r['size'] += $result['size'];
				$r['count'] += $result['count'];
				$r['dircount'] += $result['dircount']+1;
				break;
			case 'file':
				$r['size'] += filesize( $nextpath );
				$r['count']++;
				break;
			default:
			/* not counting other file types as file here
			 * possible are:
			 *		'link', 'char', 'block', 'socket', 'fifo', ''
			 */
		}
	}
	return $r;
}

/* reimplemented
 *
 * used to show file size with unit
 *
 * n - number, feasible is (platform dependant) PHP_INT_SIZE
 * s - space character, e.g. for non breaking html space
 * b - base for correct numbers and unit names
 * u - units as array[base][exponent]
 * e - calculated exponent
 * i - index/exponent of biggest unit
 *
 * IEC prefix, properly explained:
 *   https://en.wikipedia.org/wiki/Binary_prefix
 * remember to use
 *   + decimal -  is data transfer (base 1000: in kB, MB, GB, ...)
 *   + binary -  is data storage (base 1024: in KiB, MiB, GiB, ... )
 */
function sizeFormat( $n, $d = array( 'decnum' => -1 ), $s='&nbsp;', $b=1024, $u = array(
		// YiB is 2^80, x64 has PHP_INT_SIZE
		// => 2^60 is max feasible for now
		'1024' => array("bytes", "KiB", "MiB", "GiB",
			"TiB", "PiB", "EiB", "ZiB", "YiB"/**/ ),
		'1000' => array("bytes", "kB", "MB", "GB",
			"TB", "PB", "EB", "ZB", "YB"/**/ ),
	) )
{

	if($n == 0) { return 0; }

	$e =	(int)log( $n, $b ) ;
	$n /=	( ( $b == 0 && $e != 0 ) ? pow( $b, $e ) : 1 );
	$i =	sizeof( $u[$b] ) - 1;
	if( $e > $i ) {
		$e -=	$e - $i ;
	}
	$n /=	pow( $b, $e );
	return getNumberFormatted( $n, $d['decnum'] ) . $s . $u[$b][$e];
}

/* reimplemented
 *
 * dirname - directory to delete recursively
 *
 * suppressing warning for errno 13: 'Permission denied'
 * no extended validation for dirname herein yet
 * list of more files or directories that shall not be deleted might be handy to add
*/
function del_dir( $dirname, $delim='/' )
{
	if( ! is_dir( $dirname ) ) {
		//return unlink($dirname); //in case that is what you want
		return false;
	}
	if( false === ( $dirlist = @scandir( $path ) ) ) {
		return false;
	}
	foreach( array_diff( $dirlist, array( '.', '..' ) ) as $file ) {
		if( is_dir( $dirname . $delim . $file ) ) {
			del_dir( $dirname . $delim . $file );
		} else {
			unlink( $dirname . $delim . $file );
		}
	}
	return del_dir( $dirname );
}

// ============================================================================

function CBaddtofavourite( $gameid ) {
	$user = CBgetuserid(CBgetcurrentuser());
	if($user && $gameid) {
		RMLfiresql("INSERT INTO favourite VALUES($user,$gameid)");
	}
}

// ============================================================================

/* ewa: optimization, displaying level/rating might be changed here centrally
 * formatting/alignment should be done in style best as a class or a container calling this */
function getRatingDisplay( $score, $styleclass='rating-elm', $max = 10, $round = 0 )
{
	$score = round( $score, 0 );
	return str_repeat ( '<img class="'.$styleclass.'" alt="On" src="./img/on.png"/>', $score )
	. str_repeat ( '<img class="'.$styleclass.'" alt="Off" src="./img/off.png"/>', ( $max - $score ) );
}

/* put timezone in a central point, could be configured in a setting via DB or config file as well
 * */
function setTimeZone( $z = 'Europe/Copenhagen' )
{
	// idea: e.g. if $z == '' load config file
	return date_default_timezone_set( $z );
}


