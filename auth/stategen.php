<?php
	// This is the first step in the auth process. generate new session, generate state string, and save that to the server-side session for checking once we get the callback response from monzo.
	
	session_start();
	if(isset($_SESSION['accesstoken'])) {
		// User already logged in, they aren't welcome here.
		header('location: /');
		exit('You\'re already logged in.');
	}
	
	// Generate the string.
	function gen_uuid() {
	    return sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
	        // 32 bits for "time_low"
	        mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),
	
	        // 16 bits for "time_mid"
	        mt_rand( 0, 0xffff ),
	
	        // 16 bits for "time_hi_and_version",
	        // four most significant bits holds version number 4
	        mt_rand( 0, 0x0fff ) | 0x4000,
	
	        // 16 bits, 8 bits for "clk_seq_hi_res",
	        // 8 bits for "clk_seq_low",
	        // two most significant bits holds zero and one for variant DCE1.1
	        mt_rand( 0, 0x3fff ) | 0x8000,
	
	        // 48 bits for "node"
	        mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff )
	    );
	}
	$state = gen_uuid();
	$_SESSION['state'] = $state;
	
	// Pull settings.
	require_once('../inc/settings.php');
	
	// Send the user off to monzo to login.
	header("location: $api_auth_root/?client_id=$clientid&redirect_uri=$rooturl/auth/callback.php&response_type=code&state=$state");
?>