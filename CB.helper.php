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

function CBcreatenewplayer()
{
	global $player, $club, $fideid;

	CBfiresql("INSERT INTO player (id, player, club, fideid) values(DEFAULT,'$player','$club','$fideid')");	
	return true;
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

/* put timezone in a central point, could be configured in a setting via DB or config file as well
 * */
function setTimeZone( $z = 'Europe/Copenhagen' )
{
	// idea: e.g. if $z == '' load config file
	return date_default_timezone_set( $z );
}

