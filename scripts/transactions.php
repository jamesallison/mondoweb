<?php
	// get the current date for default parameters
	
    
	function getTransactions($accesstoken, $accountnumber, $findWeekDay = false, $api_root, $since, $before, $reverse = true) {
		// GET accounts
		$ch = curl_init();
		
		$headr = array(
			'Content-type: application/json',
			'Authorization: Bearer '.$accesstoken
		);
		
		if(!empty($since) && !empty($before)) {
			// they have defined pagination by date
			$before = $before.'T23:59:59Z';
			$since = $since.'T00:00:01Z';
			curl_setopt($ch, CURLOPT_URL, "$api_root/transactions?account_id=$accountnumber&expand[]=merchant&before=$before&since=$since");
		}
		else {
			// just show them all
			curl_setopt($ch, CURLOPT_URL, "$api_root/transactions?account_id=$accountnumber&expand[]=merchant");
		}
		
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headr);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		$rest = curl_exec($ch);
		
		curl_close($ch);
		
		$json = json_decode($rest, true);
		
		// change the declined transaction amounts to zero
		foreach($json['transactions'] as &$transaction) {
			if(!empty($transaction['decline_reason'])) {
				// declined transaction, set amount to zero
				$transaction['amount_declined'] = $transaction['amount'];
				$transaction['amount'] = 0;
			}
		}
		
		// get week day of transaction 
		if($findWeekDay) {
			// loop through the transactions
			foreach($json['transactions'] as $key => $transaction) {
				$json['transactions'][$key]['weekDay'] = date('l', strtotime($transaction['created']));
			}
		}
		
		if($reverse) {
			// return the reversed array (so newest transaction is at the top)
			return array_reverse($json['transactions']);
		}
		else {
			return $json['transactions'];
		}
	}
?>