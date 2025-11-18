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
// error_reporting(E_ALL ^E_STRICT);
ini_set('display_errors', '1');

$starttime = microtime();
$Version = "0.1";
$itemprpage = 20;

require 'settings.php';
require 'CB.database.php';
require 'CB.helper.php';
require 'CB.common.php';
require 'CB.user.php';

// set timezone to prevent warning
setTimezone();

$id = 0;
$message = null;
$function = null;
$game = null;
$comment = null;
$currentposition = 'rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR w KQkq - 0 1'; // FEN start
$step = 0;
$out = '';

$cookie = CBpreparestring($_COOKIE['CB']);

if ( isset($_REQUEST) && count($_REQUEST) > 0 ) {

	$id = ( is_numeric($_REQUEST['id']) && $_REQUEST['id'] > 0 ) ? $_REQUEST['id'] : 0 ;
	if(is_numeric($_REQUEST['step'])) { $step = $_REQUEST['step']; }
	
	$game = CBpreparestring($_REQUEST['game']);
 	$function = CBpreparestring($_REQUEST['function']);
	$message = CBpreparestring($_REQUEST['message']);
	$blog = CBpreparestring($_REQUEST['blog']);
	$body = CBpreparestring($_REQUEST['body']);
	$login = CBpreparestring($_REQUEST['login']);
	$logon = CBpreparestring($_REQUEST['logon']);
	$comment = CBpreparestring($_REQUEST['comment']);
	$messageto = CBpreparestring($_REQUEST['messageto']);
	$messagesubject = CBpreparestring($_REQUEST['messagesubject']);
	$forum = CBpreparestring($_REQUEST['forum']);
}

switch( $function ) {
	case 'login':
		if($login <> '') {
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
	case 'flush':
		CBflushgame( $id );
		header("Location: ?game=view&id=$id");
	break;
	case 'delete':
		CBdeletegame( $id );
		$user = CBgetcurrentuser();
		header( 'Location: ?function=user&user='.$user );
	break;
	case 'download':
		if ( !in_array( $format, array( 'pgn','html' ) ) ) {
			$format = 'pgn';
		}
		CBdownloadgame( $id, $format );
		header( 'Location: ?game=view&id='.$id );
	break;
	case 'rss':
		CBgeneraterss(true);
		die();
	break;
	case 'favourite':
		CBaddtofavourite($id);
		header( 'Location: ?document=view&id='.$id );
	break;
}

switch( $message ) {
	case 'delete':
		CBdeletemessage( $id );
	break;
	case 'send':
		$out .= CBsendmessage( $messageto, $body, CBgetcurrentuser(), $messagesubject, false );
		if( $out == '' ) {
			header( 'Location: ?function=user' );
		} 
	break;
}

switch ( $game ) {
	case 'create':
		CBcreategame();
		header( 'Location: ?function=user' );
	break;
	case 'update':
		CBgetcurrentuser();
		CBupdategame( $id );
		header( 'Location: ?game=view&id='.$id );
	break;
	case 'avatar':
		CBuploadavatar();
		header( 'Location: ?function=user' );
	break;
}

switch ($comment) {
	case 'save':
		CBsaveforum( $id, false );
		header( 'Location: ?game=view&id='.$id );
	break;
} 


$out .= CBdisplayhead( false )
	.CBdisplaytop( false )
	.CBdisplaymain( $id, false )
	.CBdisplayend( false );
	
return processOutput( $out, true );


