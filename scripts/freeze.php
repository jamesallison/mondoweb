<?php
	function freezeToggle($accesstoken, $cardid, $status, $api_root) {
		// find out their account details
		$ch = curl_init();
		
		$headr = array(
			'Content-type: application/json',
			'Authorization: Bearer '.$accesstoken
		);
		
		curl_setopt($ch, CURLOPT_URL, "$api_root/card/toggle?card_id=$cardid&status=$status");
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headr);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		$rest = curl_exec($ch);
		
		curl_close($ch);
	}
	
	session_start();
	freezeToggle($_SESSION['accesstoken'], $_GET['cardid'], $_GET['status'], $_GET['api_root']);
	// return them back
	header('location: '.$_GET['return']);
?>