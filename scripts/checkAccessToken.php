<?php
	function tokenExpired($accesstoken) {
		// GET accounts
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
		
		if(empty($json['accounts'][0]['account_number'])) {
			return true;
		}
		else {
			return false;
		}
	}
?>