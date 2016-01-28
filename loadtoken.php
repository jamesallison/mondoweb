<?php
	// this is for offline dev purposes for the hackathon, DELETE ON PUBLIC LAUNCH.
	session_start();
	$_SESSION['accesstoken'] = $_GET['accesstoken'];
	require_once('scripts/accountInfo.php');
	require_once('inc/settings.php');
	getAccountInfo($_GET['accesstoken'], $api_root);
?>