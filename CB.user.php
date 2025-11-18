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

/* users ID should be used for identifying connected resources */
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

function CBgetuserhandle ( $userid )
{
	$result = CBfiresql( "SELECT user_name FROM \"user\" WHERE id=$userid" );
	if(pg_numrows($result) == 0) { return; }
	$thisrow = pg_Fetch_Object( $result, 0 );
	return $thisrow->user_name;
}

// todo:
// return ID of a user named, use pq_prepare (http://php.net/manual/en/function.pg-prepare.php)
// sanetize RMLfiresql to use prepared statements, for now just aside each other
/*/ 
function RMLgetuserID( $handle = '' )
{
	//needs $conn for db connection handle, so better do that in a db function
	if ( $handle == '' ) {
		return false;
	} else {
		$u_query = 'SELECT id FROM "user" where handle = $1;'
		$p_query_args = array( $handle );
		$p_query = pq_prepare( $conn, $u_query );
		$result = pg_execute( $conn, $p_query, $p_query_args );
	}
}
/* todo: swap with RMLgetcurrentuser when all usage is moved to id only */
function CBgetcurrentuserID()
{
	return CBgetuserID( CBgetcurrentuser() );
}
/* handle should not be used for any comparison, part of filenames without proper validation */
// todo: make use of id for cookie as well
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

function CBgetkarma($userhandle) {
	if($userhandle == '') return 0;
	
	$result = CBfiresql("SELECT karma FROM \"user\" WHERE handle='$userhandle'");
	$thisrow = pg_Fetch_Object( $result, 0 );
	return $thisrow->karma;
}

// ============================================================================

function CBgivekarma($userid) {
	if($userid == '') return 0;
	$result = RMLfiresql("UPDATE \"user\" SET karma=karma+1 WHERE id='$userid' AND karma < 1337");
}

// ============================================================================

function CBlogin()
{
	global $login, $logon;

	$username = CBgetusername($login, $logon);
	    
    if ( CBvalidateuser( $login, $logon ) ) {
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

function CBvalidateuser($login,$logon)
{
	$username = CBgetusername($login, $logon);

	$result = CBfiresql("SELECT id FROM \"user\" WHERE handle='$username'");
	
	if(pg_num_rows($result) == 1) {
		return true;
	} else {
		return false;
	}	
}

// ============================================================================

function CBdisplaysignup( $print_on = true ) {
	$out = "\n".'<div class="box"><table><form method="post" action="?function=login"><input type="hidden" name="id" value="' .$_GET['id'] .'"><fieldset>
<tr><td>Login </td><td>: <input type="password" size="40" name="login" /></td></tr>
<tr><td>Logon </td><td>: <input type="password" size="40" name="logon" /></td></tr>
<tr><td></td><td><input class="formbutton" type="submit" value="Turn On" /></td></tr>
</fieldset></form></table></div></div>'

.'<div class="box"><div class="boxheader"><b>Sign Up</b></div>
<div class="boxtext">'."We take great pride in not knowing who our users are, so please don't use any identifying information to log on. This is NOT your 'username' and 'password', it's just two words used to identify you. (Hint: Use a password manager).<br><br><b>NOBODY WILL EVER CONTACT YOU ABOUT THIS FOR ANY REASON.</b><br><br><big><b>It is impossible to restore lost accounts.</b></big>".'
<table><form method="post" action="?function=newuser"><input type="hidden" name="id" value="' .$_GET['id'] .'"><fieldset>
<tr><td>Login </td><td>: <input type="password" size="40" name="login"/></td></tr>
<tr><td>Logon </td><td>: <input type="password" size="40" name="logon"/></td></tr>
<tr><td></td><td><input class="formbutton" type="submit" value="Sign Up"/></td></tr>
</fieldset></form></table></div></div>';
	return processOutput( $out, $print_on );
}

// ============================================================================

function CBcreatenewuser()
{
	global $login, $logon;

	if (CRYPT_BLOWFISH <> 1) {
		die( 'RMLcreatenewuser() : Blowfish not available ... FATAL' );
	}
	$username = CBgetusername($login, $logon);
	
	CBdisplay("USER : $username",5);
  
	$result = CBfiresql("SELECT id FROM \"user\" WHERE handle='$username'");

	if(pg_num_rows($result) == 0) {
		CBfiresql("INSERT INTO \"user\" (id,handle,user_name,karma) VALUES(DEFAULT,'$username',DEFAULT,DEFAULT)");
	} else {
		die("Signup failed...");
	}
    
	CBlogin();
}

// ============================================================================

function CBgetusername($string1, $string2) {
	global $secret_salt;

	$result = crypt($string1, $string2);
	
	$options = ['salt' => $secret_salt];
	$result = password_hash($result, PASSWORD_BCRYPT, $options);
  
	$result = substr($result,29);
	return $result;
}

// ============================================================================

function CBdisplayuserpage( $print_on = true ) {
	$result = CBfiresql("SELECT id,user_name,karma,xmpp,diaspora,mastodon,irc,ricochet FROM \"user\" WHERE handle='". CBgetcurrentuser() ."'");

	if(!$result) { return; }
	if(pg_numrows($result) == 0) { return; }

	$thisrow = pg_Fetch_Object( $result, 0 );
	$thisid = $thisrow->id;
	$username = $thisrow->user_name;
	$karma = $thisrow->karma;
	$xmpp = $thisrow->xmpp;
	$diaspora = $thisrow->diaspora;
	$mastodon = $thisrow->mastodon;
	$irc = $thisrow->irc;
	$ricochet = $thisrow->ricochet;
	
	if(file_exists("./users/".$thisid.".png")) {	
		$out = "<div class=\"order\"><img style=\"float:left\" src=\"./users/".$thisid.".png\">";
	} else {
		$out = "<div class=\"order\"><img style=\"float:left\" src=\"./users/Anonymous.png\">";
	}
	
	if($xmpp) $out .= "<b>XMPP</b>&nbsp;:&nbsp;$xmpp ";
	if($irc) $out .= "<br/><b>IRC</b>&nbsp;:&nbsp;$irc ";
	if($diaspora) $out .= "<br/><b>Diaspora*</b>&nbsp;:&nbsp;$diaspora ";
	if($mastodon) $out .= "<br/><b>Mastodon</b>&nbsp;:&nbsp;$mastodon ";
	if($ricochet) $out .= "<br/><b>Ricochet</b>&nbsp;:&nbsp;$ricochet ";
	$out .= "</small></div><div class=\"inlineclear\"> </div>"
	.CBdisplaygames( false )
	.CBdisplaymessages( false )
	.CBdisplaychpwd( false );
	return processOutput( $out, $print_on );
}

// ============================================================================

function CBdisplaychpwd( $print_on = true )
{
	$user = CBgetcurrentuser();
	if ( !isset( $user ) || $user === '' ) {
		return false;
	}

	define( CHANGE_PASSWORD_SUBMIT, 'Change Password' );
	$err = '';
	$out = '';

	//process data if form was sent
	if ( $_POST['submit'] === CHANGE_PASSWORD_SUBMIT ) {

		//validate input
		$pwd0 = ( isset( $_POST['password0'] ) ) ? $_POST['password0'] : '';
		$pwd1 = ( isset( $_POST['password1'] ) ) ? $_POST['password1'] : '';
		$pwd2 = ( isset( $_POST['password2'] ) ) ? $_POST['password2'] : '';

		//check authentication on known errors
		if ( $pwd0 === '' || !CBvalidateuser( $user, $pwd0 ) ) {
			$err .= 'Error in old password.<br>';
		} elseif ( $pwd1 === '' ||  $pwd2 === '' ||  $pwd2 !== $pwd1 ) {
			$err .= 'Error in new password<br>';
		} 
		//set new pass as long as no errors occured
		if ( $err === '' ) {
			$result = CBfiresql( "UPDATE \"user\" SET pass='" .getPwdHash( $pwd1 ) ."' WHERE handle='".$user."'" );
			if ( !$result ) {
				$err .= 'Error on password change.<br>';
			} else {
				$out .= 'Password successfully changed.<br>';
			}
		}

	} else {

		//or deploy form for data
		$out .= "\n" .'<form method="post" name="chpwd" id="chpwd" action="?function=user">
<table class="form">
<tr><td valign="top">Old Password:</td>
<td><input class="norm password" type="password" name="password0" id="password0" value=""></td></tr>
<tr><td valign="top">New Password:</td>
<td><input class="norm password" type="password" name="password1" id="password1" value=""></td></tr>
<tr><td valign="top">New Password again:</td>
<td><input class="norm password" type="password" name="password2" id="password2" value=""></td></tr>
<tr><td></td><td><input id="submit" name="submit" type="submit" value="' .CHANGE_PASSWORD_SUBMIT .'"></td></tr>
</table>
</form>';

	}
	return processOutput( $out, $print_on );
}

// ============================================================================

function CBdisplayfavourites( $user_id = 0, $print_on = true )
{
	if($user_id == 0) { return; }
	
	$result = CBfiresql("SELECT game_id FROM favourite WHERE user_id=$user_id");

	$out = "\n".'<div class="box">
<div class="boxheader"><b>Favourite games</b></div>
<div class="boxtext">';
	for( $row=0; $row < pg_numrows( $result ); $row++ ) {
		$thisrow = pg_Fetch_Object( $result, $row );
		$id = $thisrow->game_id;
		$out .= '<a href="?document=view&amp;id='.$id.'"><img class="FrontCover" src="./covers/cover'.$id.'"/></a>';
	}
	$out .= '</div></div>';
	return processOutput( $out, $print_on );
}

// ============================================================================

function CBdisplaymessages( $print_on = true ) {
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
	return processOutput( $out, $print_on );
}

// ============================================================================

function CBdisplaygames( $print_on = true ) {
	$user = CBgetcurrentuser();

	$out = '';
	if( $user ) {	
		$result = CBfiresql( "SELECT id,status,posted_on,title,player_id,year FROM game WHERE handle='$user' AND status<3 ORDER BY title" );

	$out .= "\n<div class=\"box\"><div class=\"boxheader\"><b>Documents</b></div><div class=\"boxtext\">";

		$numrows = pg_numrows( $result ) - 1;
		for( $row=0; $row < pg_numrows( $result ); $row++ ) {
			$thisrow = pg_Fetch_Object( $result, $row );
			$id = $thisrow->id;
			$status = $thisrow->status;
			$posted = $thisrow->posted_on;
			$posted = RMLfixdate( $posted );
			$title = $thisrow->title;
			$year = $thisrow->year;
			$playerid = $thisrow->author_id;
			$playername = CBgetplayername( $playerid );

			$out .= "\n<div class=\"box\"><div class=\"boxheader\"><a href=\"?document=view&amp;id=$id\"><img class=\"Cover\" style=\"width:100px\" src=\"./covers/cover$id\" alt=\"Document cover\"/><b>$title</b></a></div>"
			."\n<div class=\"boxtext\"><small>by <a href=\"?author=view&amp;id=$authorid\">$authorname</a>, <b>$year</b> Created: <b>$posted</b> in <b><a href=\"?subject=view&amp;id=$subjectid\">$subjecttitle</a></b></small><br/></div><div class=\"clear\"></div></div>";
		}
		$out .= "\n</div><p class=\"boxtext\"><a class=\"button add\" href=\"?game=new\">New Game/a></p></div>";
	} else {
		CBlogout();//ensure cookie is unset
	}

	return processOutput( $out, $print_on );
}

// ============================================================================

function CBdisplayavatar( $print_on = true )
{
	$id = CBgetcurrentuserID();//No more username plz

	$image = './users/';
	if( !file_exists( './users/' .$id .'.png' ) ) {//system call => do not use input from users side here
		$image .= 'Anonymous';
	} else {
		$image .= $id;
	}
	$image .= '.png';

	$out = "\n" .'<div class="boxtext"><img style="float : left;margin: 0 1ex 1ex 0;border-style : solid; border-color : black; border-width : 1px" src="' .$image .'">&nbsp;&nbsp;Please, no larger than a 96 x 96 PNG file.</div>
<div class="boxtext"><form enctype="multipart/form-data" method="post" action="?document=avatar">&nbsp;&nbsp;<input type="file" size="25" name="picture"><br/>&nbsp;&nbsp;<input type="submit" value="Change Avatar"><input type="hidden" name="document" value="avatar"></form></div>
<div class="clear"></div>';

	return processOutput( $out, $print_on );
}

// ============================================================================

function CBuploadavatar() {
	$id = CBgetcurrentuserID();//do not use input from user when making system calls!
	$target_path = './users/' . $id . '.png';
	move_uploaded_file( $_FILES['picture']['tmp_name'], $target_path );
}

// ============================================================================

function CBdisplaymessage( $id, $print_on = true ) {
	$result = CBfiresql( "SELECT handle,body,posted_on,sender_handle FROM message WHERE id=$id" );
	$thisrow = pg_Fetch_Object( $result, 0 );
	$handle = $thisrow->handle;
	$body = nl2br($thisrow->body);
	$posted = $thisrow->posted_on;
	$posted = RMLfixdate( $posted );
	$sender = $thisrow->sender_handle;

	$out = '';
	if( hasRights( 'readmsg', array( $handle ) ) ) {
		$out .= "\n".'<img class="docicon" src="./users/' .CBgetuserID( $sender ) .'.png" />
From : <b>' .$sender.'</b><br/>Sent : <b>' .$posted.'</b>
<div class="inlineclear"></div>'
		.CBdisplay( $body, 5, false )
		."\n".'<div class="bottom"><a class="button add" href="?message=reply
		&amp;id=' .$id.'">Reply</a>&nbsp;<a class="button delete" href="?message=delete&amp;id=' .$id.'">Delete</a></div>';
	} else {
		$out = "ERROR: Display Message : Cookiii baaaaaadddd...";
	}
	return processOutput( $out, $print_on );
}

// ============================================================================

function CBdeletemessage( $id ) {
	$result = CBfiresql( "SELECT handle FROM message WHERE id=$id" );
	$thisrow = pg_Fetch_Object( $result, 0 );
	$handle = $thisrow->handle;

	if( hasRights( 'delmsg', array( $handle ) ) ) {
		CBfiresql("DELETE FROM message WHERE id=$id");
	}

	header("Location: ?function=user");
}

// ============================================================================

function CBreplymessage( $id, $print_on = true ) {
	$user = CBgetcurrentuser();
	if( ( $user ) && ( $id ) ) {
		$result = CBfiresql( "SELECT handle,sender_handle,body,subject FROM message WHERE id=$id" );
		$thisrow = pg_Fetch_Object( $result, 0 );
		$thishandle = $thisrow->handle;
		$thissender = $thisrow->sender_handle;
		$thisbody = htmlspecialchars($thisrow->body);
		$thissubject = $thisrow->subject;
		
		if( $thishandle <> $user ) {
			$out = "ERROR: Cookie Bad : Not your message??";
		} else {

			$options = '';
			$result2 = CBfiresql( "SELECT handle FROM \"user\" ORDER BY handle" );
			for( $row=0; $row < pg_numrows( $result2 ); $row++ ) {
				$thisrow = pg_Fetch_Object( $result2, $row );
				$thisname = $thisrow->handle;

				if( $thisname == $thissender ) {
					$options .= "\n<option value=\"$thisname\" selected=\"yes\">$thisname</option>";
				} else if( in_array( $thisname, array( 'admin', 'SYSTEM'/** /, 'Anonymous'/**/ ) ) ) {
					$options .= "\n<option value=\"$thisname\">$thisname</option>";
				}
			}

			$out = "\n<form method=\"post\" action=\"?message=send\">
<table class=\"form\">
<tr><td><b>To : </b></td><td><select class=\"norm\" name=\"messageto\">"
			.$options
			."</select></td></tr>
<tr><td valign=\"top\"><b>Subject : </b></td><td><input class=\"norm\" type=\"text\" name=\"messagesubject\" value=\"Re: $thissubject\"></td></tr>
<tr><td valign=\"top\"><b>Message : </b></td><td><textarea class=\"norm\" rows=\"20\" cols=\"41\" wrap=\"none\" name=\"body\">".$thisbody."</textarea>
</td></tr><tr><td></td><td><input type=\"submit\" value=\"Send Reply\"></td></tr></table></form>";
		}
	}
	return processOutput( $out, $print_on );
}

// ============================================================================

function CBdisplaynewmessage( $print_on = true ) {
	$user = CBgetcurrentuser();
	$out = '';
	if( hasRights( 'isuser' ) ) {
		$result = CBfiresql("SELECT handle FROM \"user\" ORDER BY handle");

		if( !isset( $messageto ) || strlen( $messageto ) < 1 ) {
			$messageto = "Michael Gade";
		}
		$options_to = '';
		for( $row=0; $row < pg_numrows( $result ); $row++ ) {
			$thisrow = pg_Fetch_Object( $result, $row );
			$thisname = $thisrow->handle;

			$options_to .= "\n".'<option ';
			if( $thisname == $messageto ) {
				$options_to .= 'selected="yes" ';
			}
			$options_to .= 'value="'.$thisname.'">'.$thisname.'</option>';
		}
		$out .= "\n".'<form method="post" action="?message=send"><table class="form">
<tr><td><b>To : </b></td><td><select class="norm" name="messageto">'
				.$options_to
				."\n".'</select></td></tr>
<tr><td valign="top"><b>Subject : </b></td><td><input class="norm" type="text" name="messagesubject"></td></tr>
<tr><td valign="top"><b>Message : </b></td><td><textarea class="norm" rows="20" cols="41" wrap="none" name="body"></textarea>
</td></tr><tr><td></td><td><input type="submit" value="Send Message"></td></tr></table>
</form>';
	} else {
		$out = 'ERROR: You need to log in to send messages.';
	}
	return processOutput( $out, $print_on );
}

// ============================================================================

function CBsendmessage( $to, $msg, $from = 'SYSTEM', $subj = 'Message', $print_on = true ) {
	$out = '';
	$r = CBfiresql("INSERT INTO message (id,posted_on,handle,subject,body,sender_handle) VALUES (DEFAULT,NOW(),'$to','$subj','$msg','$from')");
	if( ! $r ) {
		$out = 'ERROR: Message not sent: FROM: '.$from.';TO:'.$to.'; SUBJ:'. $subj.'; MSG:'. $msg;
	}
	return processOutput( $out, $print_on );
}

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
