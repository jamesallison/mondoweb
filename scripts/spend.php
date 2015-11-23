<?php
	function spend($transactions) {
		// set counter
		$total = 0;
		
		// loop through transactions
		foreach($transactions as $transaction) {
			if(strtotime($transaction['created']) > strtotime('today')) {
				// only include if it's negative
				if($transaction['amount']<0) {
					$total = $total.$transaction['created'];
				}
			}
		}
		return $total;
	}
?>