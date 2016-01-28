<?php
	require_once('../includes/api_creds.php');
	
	// authenticate
	$url = 'https://production-api.gmon.io/oauth2/token';
	$fields = array(
		'grant_type' => urlencode('password'),
		'client_id' => urlencode($creds_id),
		'client_secret' => urlencode($creds_secret),
		'username' => urlencode($_POST['username']),
		'password' => urlencode($_POST['password'])
	);
	
	//url-ify the data for the POST
	foreach($fields as $key=>$value) { $fields_string .= $key.'='.$value.'&'; }
	rtrim($fields_string, '&');
	
	//open connection
	$ch = curl_init();
	
	//set the url, number of POST vars, POST data
	curl_setopt($ch,CURLOPT_URL, $url);
	curl_setopt($ch,CURLOPT_POST, count($fields));
	curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
	
	//execute post
	$result = curl_exec($ch);
	
	//close connection
	curl_close($ch);
	
	// get the access token
	$json_auth = json_decode($result, true);
	$accesstoken = $json_auth['access_token'];
	
	// new session
	session_start();
	
	// assign access token to session
	$_SESSION['accesstoken'] = $accesstoken;
	
	// find out their account details
	$crl = curl_init();
	
	$headr = array(
		'Content-type: application/json',
		'Authorization: Bearer '.$accesstoken
	);
	
	curl_setopt($crl, CURLOPT_URL, "https://production-api.gmon.io/accounts");
	curl_setopt($crl, CURLOPT_HTTPHEADER, $headr);
	curl_setopt($crl, CURLOPT_RETURNTRANSFER, TRUE);
	$rest = curl_exec($crl);
	
	curl_close($crl);
	
	$json = json_decode($rest, true);
	
	$_SESSION['account_number'] = $json['accounts'][0]['id'];
	$_SESSION['fullname'] = $json['accounts'][0]['description'];
	
	// redirect them to the homepage
	header('location: /');
?>