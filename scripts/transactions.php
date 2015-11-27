<?php
	function getTransactions($accesstoken, $accountnumber, $findWeekDay = false) {
		// GET accounts
		$crl = curl_init();
		
		$headr = array(
			'Content-type: application/json',
			'Authorization: Bearer '.$accesstoken
		);
		
		curl_setopt($crl, CURLOPT_URL, "https://production-api.gmon.io/transactions?account_id=$accountnumber&expand[]=merchant");
		curl_setopt($crl, CURLOPT_HTTPHEADER, $headr);
		curl_setopt($crl, CURLOPT_RETURNTRANSFER, TRUE);
		$rest = curl_exec($crl);
		
		curl_close($crl);
		
		$json = json_decode($rest, true);
		
		// get week day of transaction 
		if($findWeekDay) {
			// loop through the transactions
			foreach($json['transactions'] as $key => $transaction) {
				$json['transactions'][$key]['weekDay'] = date('l', strtotime($transaction['created']));
			}
		}
		
		// return the reversed array (so newest transaction is at the top)
		return array_reverse($json['transactions']);
	}
?>