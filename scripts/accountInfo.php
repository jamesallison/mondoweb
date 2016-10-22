<?php
	function getAccountInfo($accesstoken, $api_root, $return = false) {
		// Find out their account details.
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
		
		// We can change this once monzo adds multiple bank accounts per login, but for the moment we can just go for account #0.
		if($return) {
			// Values should be returned.
			return $json['accounts'][0];
		}
		else {
			$_SESSION['account_id'] = $json['accounts'][0]['id'];
			$_SESSION['fullname'] = $json['accounts'][0]['description'];
		}
	}
?>