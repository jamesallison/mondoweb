<?php
	// check the date is valid
	if(empty($_GET['date'])) {
		exit('Please define a date.');
	}
	if(strtotime($_GET['date'])>time()) {
		// in future, invalid.
		exit('The date is in the future - unfortunately the Mondo API doesn\'t quite allow you to time-travel yet.');
	}
	
	session_start();
	if(empty($_SESSION['accesstoken'])) {
		exit(header('location: /login.php'));
	}
	
	// check the access token is still ive
	require_once('../inc/settings.php');
	require_once('../scripts/checkAccessToken.php');
	/*if(tokenExpired($_SESSION['accesstoken'],$api_root)) {
		// make them re-auth, their token has expired.
		exit(header('location: /login.php?expired'));
	}*/
	
	header("Content-Type: text/html; charset=utf-8");
	
	// find start and end timestamps
	$d_start = new DateTime($_GET['date']);
    $d_start->modify('first day of this month');
    $d_end = new DateTime($_GET['date']);
    $d_end->modify('last day of this month');
?>
<!DOCTYPE html>
<html>
	<head>
		<?php require_once('../inc/head.php');?>
		<title><?php echo date('F Y',strtotime($_GET['date']));?> Statement - Mondo Online Banking</title>
	</head>
	<body>
		<div class="container">
			<div class="row">
				<div class="col-sm-4 col-xs-6">
					<img class="logo" src="/assets/img/logo.svg" height="60em">
				</div>
				<div class="col-sm-8 col-xs-6"> 
					<h1 class="pull-right"><?php echo strtoupper(date('F Y',strtotime($_GET['date'])));?></h1>
				</div>
			</div>
			<div class="row">
				<div class="col-sm-10">
					<h3>Your account summary from <b><?php echo $d_start->format('jS F Y');?></b> to <b><?php echo $d_end->format('jS F Y');?></b></h3>
					<table class="table table-striped">
						<tr>
							
						</tr>
					</table>
				</div>
				<div class="col-sm-2">
					<div class="pull-right" style="text-align: right;">
						<?php
							require_once('accountInfo.php');
							$accountInfo = getAccountInfo($_SESSION['accesstoken'], $api_root, true);
						?>
						<b><?php echo $_SESSION['fullname'];?></b><br/>
						<?php echo $accountInfo['sort_code'];?>
					</div>
				</div>
			</div>
			<div class="row">
				<div class="col-sm-12">
					<?php
						// get transactions
						require_once('transactions.php');
						$transactions = getTransactions($_SESSION['accesstoken'], $_SESSION['account_number'], false, $api_root, $d_start->format('Y-m-d'), $d_end->format('Y-m-d'), false);
						
						// init statistical variables
						$totalspent = 0;
						$totalin = 0;
						$totalnet = 0;
						
						// loop through the transactions
						foreach($transactions as $transaction) {
							if($transaction['amount']<0) {
								$totalspent = $totalspent + $transaction['amount'];
							}
							else {
								$totalin = $totalin + $transaction['amount'];
							}
							$totalnet = $totalnet + $transaction['amount'];
						}
					?>
					<h3>Balance Summary</h3>
					<table class="table">
						<tr>
							<td>
								<b>Balance brought forward from <?php $lastmonth = date_create($d_start->format('Y-m-d').' first day of last month'); echo $lastmonth->format('F Y');?>:</b>
								<?php
									$brought_forward = $transactions[0]['account_balance']-$transactions[0]['amount'];
								?>
							</td>
							<td style="text-align: right;">
								<span style="font-size: 1em;" class="label label-default"><?php echo '&pound;'.number_format($brought_forward/100, 2);?></span>
							</td>
						</tr>
						<tr>
							<td>
								<b>Total Spent: </b>
							</td>
							<td style="text-align: right;">
								<span style="font-size: 1em;" class="label label-warning"><?php echo '&pound;'.trim(number_format($totalspent/100, 2),'-');?></span>
							</td>
						</tr>
						<tr>
							<td>
								<b>Total In: </b>
							</td>
							<td style="text-align: right;">
								<span style="font-size: 1em;" class="label label-info"><?php echo '&pound;'.number_format($totalin/100, 2);?></span>
							</td>
						</tr>
						<tr>
							<td>
								<b>End of month balance: </b>
								<?php
									$end_of_month = $brought_forward + $totalnet;
								?>
							</td>
							<td style="text-align: right;">
								<span style="font-size: 1em;" class="label label-<?php if($totalnet<0) {echo 'danger';} else {echo 'success';}?>"><?php echo '&pound;'.number_format($end_of_month/100,2);?></span>
							</td>
						</tr>
					</table>
				</div>
			</div>
			<hr>
			<div class="row">
				<div class="col-sm-12">
					<div class="table-responsive">
						<table class="table table-hover">
							<thead>
								<tr>
									<th class="no-sort">Date</th>
									<th class="no-sort">Retailer</th>
									<th class="no-sort">Money in</th>
									<th class="no-sort">Money out</th>
									<th class="no-sort">Balance</th>
								</tr>
							</thead>
							<tbody>
								<?php
									// loop through transactions
									foreach($transactions as $transaction) {
										if($transaction['amount']<0) {
											$class = 'text-danger';
											$amount = number_format($transaction['amount']*-1/100,2,'.','\'');
											$type = '<span class="label label-danger"><i class="fa fa-arrow-down"></i></span>';
											$direction = 'out';
										}
										else if($transaction['amount']>0) {
											$class = 'text-success';
											$amount = number_format($transaction['amount']/100,2,'.','\'');
											$type = '<span class="label label-success"><i class="fa fa-arrow-up"></i></span>';
											$direction = 'in';
										}
										else {
											$class = 'text-muted';
											$amount = 0;
											$type = '<span class="label label-default"><i class="fa fa-check"></i></span>';
										}
								?>
								<tr>
									<td><?php echo date('jS M', strtotime($transaction['created'])).' <span class="text-muted">at</span> '.date('g:ia', strtotime($transaction['created']));?></td>
									<td>
										<?php
											if(empty($transaction['merchant']['name'])) {
												echo $transaction['description'];
											}
											else {
												echo $transaction['merchant']['emoji'].' '.$transaction['merchant']['name'];
											}
										?>
									</td>
									<td><?php if($direction == 'in') {echo "<span class='$class'>$amount</span>";}?></td>
									<td><?php if($direction == 'out') {echo "<span class='$class'>".$amount.'</span>';}?></td>
									<td><?php echo '&pound;'.number_format($transaction['account_balance']/100,2);?></td>
								</tr>
								<?php }?>
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>
		<script>
			/*function calendarSVG(date) {
			  return (
			    '<svg xmlns="http://www.w3.org/2000/svg" version="1.1" x="0px" y="0px" viewBox="0 0 141 146" enable-background="new 0 0 141 146" xml:space="preserve"><g><text text-anchor="middle" x="70" y="120" font-family="Helvetica" font-size="80">'
			    + date // Assuming it's safe to encode...
			    + '</text><path d="M13.3 126.4V37.4c0-2.4 0.9-4.5 2.6-6.3 1.7-1.8 3.8-2.6 6.2-2.6h8.8v-6.7c0-3.1 1.1-5.7 3.2-7.9 2.2-2.2 4.7-3.3 7.8-3.3h4.4c3 0 5.6 1.1 7.8 3.3 2.2 2.2 3.2 4.8 3.2 7.9v6.7h26.4v-6.7c0-3.1 1.1-5.7 3.2-7.9 2.2-2.2 4.7-3.3 7.8-3.3h4.4c3 0 5.6 1.1 7.8 3.3 2.2 2.2 3.2 4.8 3.2 7.9v6.7h8.8c2.4 0 4.4 0.9 6.2 2.6 1.7 1.8 2.6 3.8 2.6 6.3v88.9c0 2.4-0.9 4.5-2.6 6.3 -1.7 1.8-3.8 2.6-6.2 2.6H22.1c-2.4 0-4.4-0.9-6.2-2.6C14.2 130.8 13.3 128.8 13.3 126.4zM22.1 126.4h96.8V55.2H22.1V126.4zM39.7 41.9c0 0.6 0.2 1.2 0.6 1.6 0.4 0.4 0.9 0.6 1.6 0.6h4.4c0.6 0 1.2-0.2 1.6-0.6 0.4-0.4 0.6-0.9 0.6-1.6v-20c0-0.6-0.2-1.2-0.6-1.6 -0.4-0.4-0.9-0.6-1.6-0.6h-4.4c-0.6 0-1.2 0.2-1.6 0.6 -0.4 0.4-0.6 1-0.6 1.6V41.9zM92.5 41.9c0 0.6 0.2 1.2 0.6 1.6 0.4 0.4 0.9 0.6 1.6 0.6h4.4c0.6 0 1.2-0.2 1.6-0.6 0.4-0.4 0.6-0.9 0.6-1.6v-20c0-0.6-0.2-1.2-0.6-1.6 -0.4-0.4-0.9-0.6-1.6-0.6h-4.4c-0.6 0-1.2 0.2-1.6 0.6 -0.4 0.4-0.6 1-0.6 1.6V41.9z"/></g></svg>'
			  )
			}
			
			function calendarDataURI(date) {
			  return 'data:image/svg+xml;utf8,' + calendarSVG(date);
			}
			
			for (var i = 1; i <= 31; i++) {
			  var img = new Image();
			  img.src = calendarDataURI(i);
			  img.setAttribute('width', 100);
			  document.body.appendChild(img);
			}*/
		</script>
	</body>
</html>