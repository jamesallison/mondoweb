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
	
	header("Content-Type: text/html; charset=utf-8");
?>
<!DOCTYPE html>
<html>
	<head>
		<?php require_once('inc/head.php');?>
		<title>Mondo Online Banking</title>
	</head>
	<body>
		<div class="container">
			<?php require_once('inc/navbar.php');?>
			<div class="row">
				<div class="col-sm-9">
					<div class="table-responsive">
						<table class="table table-hover" id="transactions">
							<thead>
								<tr>
									<th></th>
									<th class="no-sort">Amount</th>
									<th class="no-sort">Retailer</th>
									<th class="no-sort">Date</th>
									<th class="no-sort">Balance</th>
								</tr>
							</thead>
							<tbody>
								<?php
									// loop through transactions
									foreach($transactions as $transaction) {
										if(!empty($transaction['decline_reason'])) {
											$class = 'text-danger';
											$styling = 'text-decoration: line-through;';
											$amount = '- &pound;'.number_format($transaction['amount_declined']*-1/100,2);
											$type = '<span class="label label-danger"><i class="fa fa-times fa-fw"></i></span>';
										}
										else if($transaction['amount']<0) {
											$class = 'text-warning';
											$styling = '';
											$amount = '- &pound;'.number_format($transaction['amount']*-1/100,2);
											$type = '<span class="label label-warning"><i class="fa fa-arrow-down fa-fw"></i></span>';
										}
										else if($transaction['amount']>0) {
											$class = 'text-success';
											$styling = '';
											$amount = '+ &pound;'.number_format($transaction['amount']/100,2);
											$type = '<span class="label label-success"><i class="fa fa-arrow-up fa-fw"></i></span>';
										}
										else {
											$class = 'text-muted';
											$styling = '';
											$amount = '&plusmn; &pound;0';
											$type = '<span class="label label-default"><i class="fa fa-check"></i></span>';
										}
								?>
								<tr>
									<td><?php echo $type;?></td>
									<td><?php echo "<span class='$class' style='$styling'>$amount</span>";?></td>
									<td>
										<?php
											if($transaction['is_load']) {
												echo '<img src="/assets/img/mondo.png" style="height: 1em;"> &nbsp;';
												$transaction_title = 'Top Up';
											}
											else if(empty($transaction['merchant']['name'])) {
												$transaction_title = $transaction['description'];
											}
											else {
												$transaction_title = $transaction['merchant']['emoji'].' '.$transaction['merchant']['name'];
											}
											echo $transaction_title;
										?>
									</td>
									<td>
										<a href="#" data-toggle="modal" data-target="#transactionModal" data-transaction_title="<?php echo $transaction_title;?>" data-transaction_amount="<?php echo "<span class='$class' style='$styling'>$amount</span>";?>" data-transaction_date="<?php echo date('d\/m\/Y g:i a', strtotime($transaction['created']));?>" data-transaction_notes="<?php echo $transaction['notes'];?>" data-transaction_category="<?php echo $transaction['category'];?>" data-transaction_declined="<?php if(!empty($transaction['amount_declined'])) {echo 'true';} else {echo 'false';}?>" style="color: #333;">
											<?php
												echo date('d\/m\/Y g:i a', strtotime($transaction['created']));
											?>
										</a>
									</td>
									<td><?php echo '&pound;'.number_format($transaction['account_balance']/100,2);?></td>
								</tr>
								<?php }?>
							</tbody>

							
						</table>
						<div class="modal fade" id="transactionModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel">
								<div class="modal-dialog" role="document">
									<div class="modal-content">
										<div class="modal-header">
											<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
											<h4 class="modal-title" id="exampleModalLabel">Transaction Name</h4>
										</div>
										<div class="modal-body">
											<center>
												<h1 class="transaction_amount"></h1>
												<p class="transaction_declined lead text-danger"></p>
												<h3 class="transaction_title"></h3>
												<p class="notes lead"></p>
												<span class="label label-info category"></span>
											</center>
										</div>
									</div>
								</div>
							</div>
					</div>
				</div>
				<div class="col-sm-3">
					<h3>Account Overview</h3>
					<?php
						require_once('scripts/expenditure.php');
						$balanceDetails = currentBalance($_SESSION['accesstoken'], $_SESSION['account_id'], $api_root);
						require_once('scripts/accountInfo.php');
						$accountInfo = getAccountInfo($_SESSION['accesstoken'], $api_root, true);
					?>
					<table class="table">
						<tr>
							<td>
								<b>Current Balance: </b>
							</td>
							<td style="text-align: right;">
								<span style="font-size: 1em;" class="label label-success"><?php echo '&pound;'.number_format($balanceDetails['balance']/100,2);?></span>
							</td>
						</tr>
						<tr>
							<td>
								<b>Spent Today: </b>
							</td>
							<td style="text-align: right;">
								<span style="font-size: 1em;" class="label label-danger"><?php echo '&pound;'.number_format(trim($balanceDetails['spend_today'],'-')/100,2);?></span>
							</td>
						</tr>
						<tr>
							<td>
								<b>Account Number: </b>
							</td>
							<td style="text-align: right;">
								<span style="font-size: 1em;" class="label label-info"><?php echo $accountInfo['account_number'];?></span>
							</td>
						</tr>
						<tr>
							<td>
								<b>Sort Code: </b>
							</td>
							<td style="text-align: right;">
								<span style="font-size: 1em;" class="label label-info"><?php echo $accountInfo['sort_code'];?></span>
							</td>
						</tr>
					</table>
				</div>
			</div>
		</div>
		<?php require_once('inc/foot.php');?>
		<script src="/assets/js/jquery.dataTables.min.js" type="text/javascript"></script>
		<script src="/assets/js/dataTables.bootstrap.min.js" type="text/javascript"></script>
		<script>
			$(document).ready(function() {
				$('#transactions').DataTable(
					{
						"aoColumnDefs" : [ {
						    "bSortable" : false,
						    "aTargets" : [ "no-sort" ]
						} ]
					}
				);
			});
		</script>
		<script>
			$('#transactionModal').on('show.bs.modal', function (event) {
			  var button = $(event.relatedTarget) // Button that triggered the modal
			  var modal = $(this)
			  modal.find('.modal-title').text(button.data('transaction_date'))
			  modal.find('.transaction_amount').html(button.data('transaction_amount'))
			  modal.find('.transaction_title').text(button.data('transaction_title'))
			  modal.find('.notes').text(button.data('transaction_notes'))
			  modal.find('.category').text(button.data('transaction_category')).css('textTransform', 'capitalize')
			  
			  // declined or not
			  if (button.data('transaction_declined') == true) {
			    modal.find('.transaction_declined').text("Transaction declined")
			  }
			})
		</script>
	</body>
</html>