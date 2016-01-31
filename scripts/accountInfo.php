<?php
	function getAccountInfo($accesstoken, $api_root, $return = false) {
		// find out their account details
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
		
		// we can change the once mondo adds multiple bank accounts per login, but for the moment we can just go for account #0
		if($return) {
			// values should be returned
			return $json['accounts'][0];
		}
		else {
			$_SESSION['account_id'] = $json['accounts'][0]['id'];
			$_SESSION['account_number'] = $json['accounts'][0]['number'];
			$_SESSION['fullname'] = $json['accounts'][0]['description'];
		}
	}
?>