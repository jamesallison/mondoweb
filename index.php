<?php
	// must be logged in
	session_start();
	if(!isset($_SESSION['accesstoken'])) {
		exit(header('location: /login.php'));
	}
	
	// check if the access token is valid
	require_once('scripts/checkAccessToken.php');
	if(tokenExpired($_SESSION['accesstoken'])) {
		// token expired, must login again
		session_destroy();
		exit(header('location: /login.php?expired'));
	}
?>
<!DOCTYPE html>
<html>
	<head>
		<?php require_once('includes/head.php');?>
	</head>
	<body>
		<?php
			require_once('includes/navbar.php');
			
			// get transactions
			require_once('scripts/transactions.php');
			$transactions = getTransactions($_SESSION['accesstoken'], $_SESSION['account_number']);
		?>
		<div class="container-fluid">
			<div class="row">
				<div class="col-xs-6">
					<div class="well text-center">
						<h1>Current Balance (GBP)</h1>
						<div class="big">
							<?php
								// get current balance
								require_once('scripts/balance.php');
								echo '&pound;'.balance($transactions)/100;
							?>
						</div>
					</div>
				</div>
				<div class="col-xs-6">
					<div class="well text-center">
						<h1>Spent Today (GBP)</h1>
						<div class="big">
							<?php
								// get today's spend
								require_once('scripts/spend.php');
								echo '&pound;'.spend($transactions)/100;
							?>
						</div>
					</div>
				</div>
			</div>
			<div class="row">
				<div class="col-lg-12">
					<div class="table-responsive">
						<table class="table table-hover">
							<tbody>
								<?php
									// loop through transactions
									foreach($transactions as $transaction) {
										if($transaction['amount']<0) {
											$class = 'text-danger';
											$amount = '- &pound;'.$transaction['amount']*-1/100;
											$type = '<span class="label label-danger">OUT</span>';
										}
										else if($transaction['amount']>0) {
											$class = 'text-success';
											$amount = '+ &pound;'.$transaction['amount']/100;
											$type = '<span class="label label-success">TOP-UP</span>';
										}
										else {
											$class = 'text-muted';
											$amount = '&plusmn; &pound;0';
											$type = '<span class="label label-default">NOTHING</span>';
										}
								?>
								<tr>
									<td><?php echo $type;?></td>
									<td><?php echo "<span class='$class'>$amount</span>";?></td>
									<td>
										<?php
											if(empty($transaction['merchant']['name'])) {
												echo $transaction['description'];
											}
											else {
												echo $transaction['merchant']['name'];
											}
										?>
									</td>
									<td>
										<?php
											echo date('d\/m\/Y g:i a', strtotime($transaction['created']));
										?>
									</td>
									<td><?php echo '&pound;'.$transaction['account_balance']/100;?></td>
								</tr>
								<?php }?>
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>
		<?php require_once('includes/footer.php');?>
		<?php require_once('includes/foot.php');?>
	</body>
</html>