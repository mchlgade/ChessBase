<?php
// ============================================================================
//  User functions for ChessBase
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

function CBgetuserID( $username )
{
	$username = CBgetcurrentuser();
	$result = CBfiresql( "SELECT id FROM \"user\" WHERE handle='$username'" );
	$thisrow = pg_Fetch_Object( $result, 0 );

	if ( is_numeric( $thisrow->id ) ) {
		return $thisrow->id;
	} else {
		return false;
	}
}

// ============================================================================

function CBgetuserhandle ( $userid )
{
	$result = CBfiresql( "SELECT user_name FROM \"user\" WHERE id=$userid" );
	if(pg_numrows($result) == 0) { return; }
	$thisrow = pg_Fetch_Object( $result, 0 );
	return $thisrow->user_name;
}

// ============================================================================

function CBgetcurrentuserID()
{
	return CBgetuserID( CBgetcurrentuser() );
}

// ============================================================================

function getPwdHash( $password )
{	
	global $secret_salt;
	return sha1($secret_salt . $password . $secret_salt);
}

// ============================================================================

function CBgetcurrentuser()
{
	global $cookie;

	if( $cookie ) {
		list( $thisuser, $cookie_hash ) = preg_split( '@,@', $cookie );
		
		if ( getPwdHash( $thisuser ) == $cookie_hash ) {
			return $thisuser;
		} else {
			return null;
		}
	}
}

// ============================================================================

function CBlogin()
{
	global $pass1, $pass2;

	$username = CBgetusername($pass1, $pass2);
	    
    if ( CBvalidateuser( $pass1, $pass2 ) ) {
		setcookie ("CB", $username . ',' . getPwdHash( $username ) );
	} else {
		die ("Login failed...");
	}

	header( 'Location: ?function=user' );
}

// ============================================================================

function CBlogout()
{
	setcookie( 'CB', '', time() - 86400 );
}

// ============================================================================

function CBvalidateuser($pass1,$pass2)
{
	$username = CBgetusername($pass1, $pass2);

	$result = CBfiresql("SELECT id FROM \"user\" WHERE handle='$username'");
	
	if(pg_num_rows($result) == 1) {
		return true;
	} else {
		return false;
	}	
}

// ============================================================================

function CBdisplaysignup( ) {
	global $id;
	$out = "\n".'<div class="box"><table><form method="post" action="?function=login"><input type="hidden" name="id" value="' .$id .'"><fieldset>
<tr><td>Login </td><td>: <input type="password" size="40" name="pass1" /></td></tr>
<tr><td>Logon </td><td>: <input type="password" size="40" name="pass2" /></td></tr>
<tr><td></td><td><input class="formbutton" type="submit" value="Turn On" /></td></tr>
</fieldset></form></table></div>'

.'<div class="box"><div class="boxheader"><b>Sign Up</b></div>
<div class="boxtext">'."We take great pride in not knowing who our users are, so please don't use any identifying information to log on. This is NOT your 'username' and 'password', it's just two words used to identify you. (Hint: Use a password manager).<br><br><b>NOBODY WILL EVER CONTACT YOU ABOUT THIS FOR ANY REASON.</b><br><br><big><b>It is impossible to restore lost accounts.</b></big>".'
<table><form method="post" action="?function=newuser"><input type="hidden" name="id" value="' .$id .'"><fieldset>
<tr><td>Name </td><td>: <input type="text" size="40" name="user_name"/></td></tr>
<tr><td>Login </td><td>: <input type="password" size="40" name="pass1"/></td></tr>
<tr><td>Logon </td><td>: <input type="password" size="40" name="pass2"/></td></tr>
<tr><td></td><td><input class="formbutton" type="submit" value="Sign Up"/></td></tr>
</fieldset></form></table></div></div>';
	return $out;
}

// ============================================================================

function CBcreatenewuser()
{
	global $pass1, $pass2, $user_name;

	$username = CBgetusername($pass1, $pass2);
	
	$result = CBfiresql("SELECT id FROM \"user\" WHERE handle='$username'");

	if(pg_num_rows($result) == 0) {
		CBfiresql("INSERT INTO \"user\" (id,handle,user_name,karma) VALUES(DEFAULT,'$username','$user_name',DEFAULT)");
	} else {
		die("Signup failed...");
	}
    
	CBlogin();
}

// ============================================================================

function CBgetusername($string1, $string2) {
	global $secret_salt;
	
	$result = crypt($string1, $string2);
	$result = getPwdHash($secret_salt . $result . $secret_salt);
	
	$result = substr($result,29);
	return $result;
}

// ============================================================================

function CBdisplayuserpage( ) {
	$result = CBfiresql("SELECT id,user_name,karma FROM \"user\" WHERE handle='". CBgetcurrentuser() ."'");

	if(!$result) { return; }
	if(pg_numrows($result) == 0) { return; }

	$thisrow = pg_Fetch_Object( $result, 0 );
	$thisid = $thisrow->id;
	$username = $thisrow->user_name;
	$karma = $thisrow->karma;
	$out = null;
	
	$out .= "<div class=\"inlineclear\"> </div>"
	.CBdisplaymygames( )
	.CBdisplaymessages( );
	return $out;
}

// ============================================================================

function CBdisplaymessages( ) {
	$user = CBgetcurrentuser();
	if(!$user) { return; }
	$result = CBfiresql("SELECT id,posted_on,subject,sender_handle FROM message WHERE handle='$user' ORDER BY posted_on DESC");

	$numrows = pg_numrows($result) - 1;

	$out = "\n<div class=\"box\"><div class=\"boxheader\"><b>Messages</b></div>
<div class=\"boxtext\">";

	if( pg_numrows( $result ) > 0 ) {

		for( $row=0; $row < pg_numrows( $result ); $row++ ) {
			$thisrow = pg_Fetch_Object( $result, $row );
			$id = $thisrow->id;
			$subject = $thisrow->subject;
			$posted = $thisrow->posted_on;
			$posted = RMLfixdate( $posted );
			$sender = $thisrow->sender_handle;
			$out .= "\n<a href=\"?message=view&amp;id=$id\"><img style=\"width:36px;\" align=\"left\" alt=\"Comment\" src=\"./img/Messages.png\"/>
<b>$subject</b></a><br/><small><i>from</i>: <b>$sender</b>, <i>$posted</i></small>";
			if($row < $numrows) {
				$out .= "\n<div class=\"clear\"><hr class=\"forumseperator\" /></div>";
			}
		}
	}
	if( pg_numrows( $result ) !== 0 ) {
		$out .= "\n<br/>";
	}
	$out .= "\n</div><p class=\"boxtext\"><a class=\"button add\" href=\"?message=new\">New Message</a></p></div>";
	return $out;
}

// ============================================================================

function CBdisplaymygames( ) {
	$user = CBgetcurrentuser();

	$out = '';
	if( $user ) {	
		$result = CBfiresql( "SELECT id,status,posted_on,whiteid,blackid,welo,belo,tourid FROM game WHERE posted_by_id='$user' AND status<3 ORDER BY posted_on" );

	$out .= "\n<div class=\"box\"><div class=\"boxheader\"><b>Games</b></div><div class=\"boxtext\">";

		$numrows = pg_numrows( $result ) - 1;
		for( $row=0; $row < pg_numrows( $result ); $row++ ) {
			$thisrow = pg_Fetch_Object( $result, $row );
			$id = $thisrow->id;
			$status = $thisrow->status;
			$posted = $thisrow->posted_on;
			$posted = CBfixdate( $posted );
			$whiteid = $thisrow->whiteid;
			$blackid = $thisrow->blackid;
			$whiteelo = $thisrow->welo;
			$blackelo = $thisrow->belo;
			$tournamentid = $thisrow->tourid;
			
			$whiteplayer = CBgetplayername( $whiteid );
			$blackplayer = CBgetplayername( $blackid );
			$tournament = CBgettournament( $tournamentid );
			
			$title = $whiteplayer . '(' . $whiteelo . ') vs.' . $blackplayer . '(' . $blackelo . ')';

			$out .= "\n<div class=\"box\"><div class=\"boxheader\"><a href=\"?game=view&amp;id=$id\"><b>$title</b></a></div>"
			."\n<div class=\"boxtext\"><small>Created: <b>$posted</b> in <b><a href=\"?tournament=view&amp;id=$tournamentid\">$tournament</a></b></small><br/></div><div class=\"clear\"></div></div>";
		}
		$out .= "\n</div><p class=\"boxtext\"><a class=\"button add\" href=\"?game=new\">New Game</a></p></div>";
	} else {
		CBlogout();//ensure cookie is unset
	}

	return $out;
}

// ============================================================================

function hasRights( $action = '', $arr = array() )
{
	$user = CBgetcurrentuser();
	if ( $user == '' || $user == null ) {
		return false;
	}
	if ( ! in_array( 'noadm', $arr ) ) {
		$admaccs = array( 'admin', 'SYSTEM' );
		if ( ! in_array( 'nomod', $arr ) ) {
			$modaccs = array( 'Michael Gade' );
		}
	}
	switch ( $action ) {
		case 'addgame':	return true;//for now everyone;
			break;
		case 'addplayer' :	
		case 'editplayer' :	
		case 'addnews':		
		case 'delnews':		
		case 'editnews':	
		case 'test':		
		case 'selfpublish':	
			if ( in_array( $user, array_merge( $arr, $admaccs, $modaccs ) ) ) {
				return true;
			}
			break;
		case 'delmsg':
		case 'readmsg':
		case 'delgame':
		case 'editgame':
			if ( in_array( $user, array_merge( $arr, $admaccs ) ) ) {
				return true;
			}
		break;
		case 'isuser':
			return true;
		break;
	}
	return false;
}

