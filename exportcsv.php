<?php
	session_start();
	if(empty($_SESSION['accesstoken'])) {
		exit(header('location: /login.php'));
	}
	
	
	
	// check the access token is still ive
	require_once('inc/settings.php');
	require_once('scripts/checkAccessToken.php');
	if(tokenExpired($_SESSION['accesstoken'],$api_root)) {
		// make them re-auth, their token has expired.
		exit(header('location: /login.php?expired'));
	}
	
	require_once('scripts/transactions.php');
	$transactions = getTransactions($_SESSION['accesstoken'], $_SESSION['account_id'], false, $api_root);
	
	// open the output stream
	$f = fopen('php://output', 'w');
	
	// Start output buffering (to capture stream contents)
	ob_start();
	
	// set the headers
	$column_headers = array('ID', 'Time', 'Description', 'Amount', 'Currency', 'Notes', 'Balance', 'Category', 'Local Amount', 'Local Currency', 'lat', 'long');
	fputcsv($f, $column_headers);
	
	foreach ($transactions as $transaction) {
		// extract the country data
		if (isset($transaction['merchant']['address']['latitude']) && isset($transaction['merchant']['address']['longitude'])) {
			$transaction['lat'] = $transaction['merchant']['address']['latitude'];
			$transaction['long'] = $transaction['merchant']['address']['longitude'];
		}
		
		// remove columns we don't want
		unset($transaction['merchant']);
		unset($transaction['metadata']);
		unset($transaction['attachments']);
		unset($transaction['is_load']);
		unset($transaction['settled']);
		unset($transaction['updated']);
		unset($transaction['account_id']);
		unset($transaction['counterparty']);
		unset($transaction['scheme']);
		unset($transaction['dedupe_id']);
		unset($transaction['originator']);
		unset($transaction['metadata']);
		unset($transaction['decline_reason']);
		fputcsv($f, $transaction);
	}
	
	// Get the contents of the output buffer
	$string = ob_get_clean();
	
	$filename = 'csv_' . date('Ymd') .'_' . date('His');
	
	// Output CSV-specific headers
	header("Pragma: public");
	header("Expires: 0");
	header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
	header("Cache-Control: private",false);
	header("Content-Type: application/octet-stream");
	header("Content-Disposition: attachment; filename=\"transactions.csv\";" );
	header("Content-Transfer-Encoding: binary");
	
	exit($string);
?>