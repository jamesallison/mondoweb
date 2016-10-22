<?php
	// Monzo will now send back a code, which can exchange for a token, which we can then use for a few hours to do our stuff.
	session_start();
	if(isset($_SESSION['accesstoken'])) {
		// User already logged in, they aren't welcome here.
		header('location: /');
		exit('You\'re already logged in.');
	}
	
	// Let's make sure we have a state too.
	if(empty($_SESSION['state'])) {
		session_destroy();
		exit(header('location: /'));
	}
	
	// All good to go, let's check the states match first so we can see if something has been tampered with somewhere along the way.
	if($_SESSION['state'] == $_GET['state']) {
		// Good, the states match, now exchange the code for a token.
		require_once('../inc/settings.php');
		$ch = curl_init();
		$fields = array(
			'grant_type' => urlencode('authorization_code'),
			'client_id' => urlencode($clientid),
			'client_secret' => urlencode($clientsecret),
			'redirect_uri' => urlencode("$rooturl/auth/callback.php"),
			'code' => urlencode($_GET['code'])
		);
		
		// Url-ify the data for the POST
		foreach($fields as $key=>$value) { $fields_string .= $key.'='.$value.'&'; }
		rtrim($fields_string, '&');
		curl_setopt($ch, CURLOPT_URL, "$api_root/oauth2/token");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);
		$rest = curl_exec($ch);
		curl_close($ch);
		$json = json_decode($rest, true);
		
		// Put the access token in the session.
		$_SESSION['accesstoken'] = $json['access_token'];
		
		// Get the account's info.
		require_once('../scripts/accountInfo.php');
		getAccountInfo($json['access_token'], $api_root);
		
		// All done with logging in, send them to the homepage.
		header('location: /');
	}
	else {
		// States don't match, throw them out.
		session_destroy();
		exit(header('location: /'));
	}
?>