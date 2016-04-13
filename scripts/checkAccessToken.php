<?php
	function tokenExpired($accesstoken, $api_root) {
		// GET accounts
		$ch = curl_init();
		
		$headr = array(
			'Content-type: application/json',
			'Authorization: Bearer '.$accesstoken
		);
		
		curl_setopt($ch, CURLOPT_URL, "$api_root/accounts");
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headr);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		$rest = curl_exec($ch);
		
		curl_close($ch);
		
		$json = json_decode($rest, true);
		
		if(empty($json['accounts'][0]['id'])) {
			// token has expired, destroy the session so we aren't keeping an expired accesstoken in the session
			return true;
		}
		else {
			return false;
		}
	}
?>