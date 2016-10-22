<?php
	function listCards($accesstoken, $account_id, $api_root) {
		// find out their account details
		$ch = curl_init();
		
		$headr = array(
			'Content-type: application/json',
			'Authorization: Bearer '.$accesstoken
		);
		
		curl_setopt($ch, CURLOPT_URL, "$api_root/card/list?account_id=$account_id");
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headr);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		$rest = curl_exec($ch);
		
		curl_close($ch);
		
		$json = json_decode($rest, true);
		
		// We can change the once monzo adds multiple bank accounts per login, but for the moment we can just go for account #0.
		return $json['cards'];
	}
?>