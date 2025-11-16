<?php
// ============================================================================
//  Database functions for ChessBase
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

$conn;
$SQLcounter = 0;
$SQLtime = 0;
$SQLsize = 0;

// ============================================================================

function CBopendatabase()
{
	global $dbname, $dbuser, $dbhost, $dbpass, $conn;

	if ( isset( $dbpass ) && $dbpass !== '' ) {
		$conn = pg_pconnect( "user=$dbuser dbname=$dbname host=$dbhost password=$dbpass" )
			or die( "<h1>Opendatabase ERROR : Fuck Me...</h1>" );

		return $conn;
	} else {
		die( "<h1>Opendatabase ERROR : No DB password set...</h1>" );
	}
}

// ============================================================================

function CBgrabconnection()
{
	global $conn;

	if( !$conn ) {
		$conn = CBopendatabase();
	}

	return $conn;
}

// ============================================================================

function CBfireSQL( $SQL )
{
	global $conn;
	global $SQLcounter;
	global $SQLsize;

	$conn = CBgrabconnection();

	$result = pg_exec( $conn, $SQL );

	for( $row=0; $row < pg_numrows( $result ); $row++ ) {
		$thisrow = pg_Fetch_Object( $result, $row );
		$i = pg_num_fields( $result );
		for( $j = 0; $j < $i; $j++ ) {
			$SQLsize += pg_field_prtlen( $result, $j );
		}
	}

	$SQLcounter++;
	return $result;
}

function CBclosedb() 
{
	global $conn;

	if( !$conn ) {
		return false;
	} else {
		return pg_close( $conn );
	}
}
