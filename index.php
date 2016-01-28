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
		<title>Transactions - Mondo Online Banking</title>
	</head>
	<body>
		<?php
			require_once('includes/navbar.php');
			
			// get transactions
			require_once('scripts/transactions.php');
			$transactions = getTransactions($_SESSION['accesstoken'], $_SESSION['account_number'], true);
		?>
		<div class="container-fluid">
			<div class="row money-boxes">
				<div class="col-sm-6">
					<div class="well text-center">
						<h1>Current Balance (GBP)</h1>
						<div class="big">
							<?php
								// get current balance
								require_once('scripts/balance.php');
								echo '&pound;'.number_format(balance($transactions)/100,2);
							?>
						</div>
					</div>
				</div>
				<div class="col-sm-6">
					<div class="well text-center">
						<h1>Spent Today (GBP)</h1>
						<div class="big">
							<?php
								// get today's spend
								require_once('scripts/spend.php');
								echo '&pound;'.number_format(spend($transactions)/100,2);
							?>
						</div>
					</div>
				</div>
			</div>
			<div class="row charts">
				<div style="width:30%">
			<canvas id="canvas" height="450" width="450"></canvas>
		</div>
		<div id="legend"></div>
			</div>
			<div class="row">
				<div class="col-lg-12">
					<div class="table-responsive">
						<table class="table table-hover">
							<thead>
								<tr>
									<th></th>
									<th>Amount</th>
									<th>Retailer</th>
									<th>Date</th>
									<th>Balance</th>
								</tr>
							</thead>
							<tbody>
								<?php
									// loop through transactions
									foreach($transactions as $transaction) {
										if($transaction['amount']<0) {
											$class = 'text-danger';
											$amount = '- &pound;'.number_format($transaction['amount']*-1/100,2);
											$type = '<span class="label label-danger">OUT</span>';
										}
										else if($transaction['amount']>0) {
											$class = 'text-success';
											$amount = '+ &pound;'.number_format($transaction['amount']/100,2);
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
									<td><?php echo '&pound;'.number_format($transaction['account_balance']/100,2);?></td>
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
	
		<script src="/assets/js/Chart.js"></script>
		<?php
			/* generate each category's totals */
			$totals = array();
			$totals['Monday'] = array();
			$totals['Tuesday'] = array();
			$totals['Wednesday'] = array();
			$totals['Thursday'] = array();
			$totals['Friday'] = array();
			$totals['Saturday'] = array();
			$totals['Sunday'] = array();
			
			// monday
			foreach($transactions as $transaction) {
				// only add if the category is there and it's negative (expenditure)
				if(!empty($transaction['category']) && $transaction['amount']<0) {
					$totals[$transaction['weekDay']][$transaction['category']] = $totals[$transaction['weekDay']][$transaction['category']] + ($transaction['amount'] / 100 * -1);
				}
			}
		?>
		<script>
			var radarChartData = {
				labels: ["Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday"],
				datasets: [
					{
						label: "Transport",
						fillColor: "rgba(166,201,208,0.2)",
						strokeColor: "rgba(166,201,208,1)",
						pointColor: "rgba(166,201,208,1)",
						pointStrokeColor: "#fff",
						pointHighlightFill: "#fff",
						pointHighlightStroke: "rgba(166,201,208,1)",
						data: [<?php echo round($totals['Monday']['transport'],2).','.round($totals['Tuesday']['transport'],2).','.round($totals['Wednesday']['transport'],2).','.round($totals['Thursday']['transport'],2).','.round($totals['Friday']['transport'],2).','.round($totals['Saturday']['transport'],2).','.round($totals['Sunday']['transport'],2);  ?>]
					},
					{
						label: "Groceries",
						fillColor: "rgba(249,225,181,0.2)",
						strokeColor: "rgba(249,225,181,1)",
						pointColor: "rgba(249,225,181,1)",
						pointStrokeColor: "#fff",
						pointHighlightFill: "#fff",
						pointHighlightStroke: "rgba(244,183,191,1)",
						data: [<?php echo round($totals['Monday']['groceries'],2).','.round($totals['Tuesday']['groceries'],2).','.round($totals['Wednesday']['groceries'],2).','.round($totals['Thursday']['groceries'],2).','.round($totals['Friday']['groceries'],2).','.round($totals['Saturday']['groceries'],2).','.round($totals['Sunday']['groceries'],2);  ?>]
					},
					{
						label: "Eating Out",
						fillColor: "rgba(244,183,191,0.2)",
						strokeColor: "rgba(244,183,191,1)",
						pointColor: "rgba(244,183,191,1)",
						pointStrokeColor: "#fff",
						pointHighlightFill: "#fff",
						pointHighlightStroke: "rgba(244,183,191,1)",
						data: [<?php echo round($totals['Monday']['eating_out'],2).','.round($totals['Tuesday']['eating_out'],2).','.round($totals['Wednesday']['eating_out'],2).','.round($totals['Thursday']['eating_out'],2).','.round($totals['Friday']['eating_out'],2).','.round($totals['Saturday']['eating_out'],2).','.round($totals['Sunday']['eating_out'],2);  ?>]
					},
					{
						label: "Cash",
						fillColor: "rgba(215,228,221,0.2)",
						strokeColor: "rgba(215,228,221,1)",
						pointColor: "rgba(215,228,221,1)",
						pointStrokeColor: "#fff",
						pointHighlightFill: "#fff",
						pointHighlightStroke: "rgba(215,228,221,1)",
						data: [<?php echo round($totals['Monday']['cash'],2).','.round($totals['Tuesday']['cash'],2).','.round($totals['Wednesday']['cash'],2).','.round($totals['Thursday']['cash'],2).','.round($totals['Friday']['cash'],2).','.round($totals['Saturday']['cash'],2).','.round($totals['Sunday']['cash'],2);  ?>]
					},
					{
						label: "Bills",
						fillColor: "rgba(186,227,240,0.2)",
						strokeColor: "rgba(186,227,240,1)",
						pointColor: "rgba(186,227,240,1)",
						pointStrokeColor: "#fff",
						pointHighlightFill: "#fff",
						pointHighlightStroke: "rgba(186,227,240,1)",
						data: [<?php echo round($totals['Monday']['bills'],2).','.round($totals['Tuesday']['bills'],2).','.round($totals['Wednesday']['bills'],2).','.round($totals['Thursday']['bills'],2).','.round($totals['Friday']['bills'],2).','.round($totals['Saturday']['bills'],2).','.round($totals['Sunday']['bills'],2);  ?>]
					},
					{
						label: "Entertainment",
						fillColor: "rgba(242,203,182,0.2)",
						strokeColor: "rgba(242,203,182,1)",
						pointColor: "rgba(242,203,182,1)",
						pointStrokeColor: "#fff",
						pointHighlightFill: "#fff",
						pointHighlightStroke: "rgba(242,203,182,1)",
						data: [<?php echo round($totals['Monday']['entertainment'],2).','.round($totals['Tuesday']['entertainment'],2).','.round($totals['Wednesday']['entertainment'],2).','.round($totals['Thursday']['entertainment'],2).','.round($totals['Friday']['entertainment'],2).','.round($totals['Saturday']['entertainment'],2).','.round($totals['Sunday']['entertainment'],2);  ?>]
					},
					{
						label: "Shopping",
						fillColor: "rgba(246,212,213,0.2)",
						strokeColor: "rgba(246,212,213,1)",
						pointColor: "rgba(246,212,213,1)",
						pointStrokeColor: "#fff",
						pointHighlightFill: "#fff",
						pointHighlightStroke: "rgba(246,212,213,1)",
						data: [<?php echo round($totals['Monday']['shopping'],2).','.round($totals['Tuesday']['shopping'],2).','.round($totals['Wednesday']['shopping'],2).','.round($totals['Thursday']['shopping'],2).','.round($totals['Friday']['shopping'],2).','.round($totals['Saturday']['shopping'],2).','.round($totals['Sunday']['shopping'],2);  ?>]
					},
					{
						label: "Holidays",
						fillColor: "rgba(227,205,254,0.2)",
						strokeColor: "rgba(227,205,254,1)",
						pointColor: "rgba(227,205,254,1)",
						pointStrokeColor: "#fff",
						pointHighlightFill: "#fff",
						pointHighlightStroke: "rgba(227,205,254,1)",
						data: [<?php echo round($totals['Monday']['holidays'],2).','.round($totals['Tuesday']['holidays'],2).','.round($totals['Wednesday']['holidays'],2).','.round($totals['Thursday']['holidays'],2).','.round($totals['Friday']['holidays'],2).','.round($totals['Saturday']['holidays'],2).','.round($totals['Sunday']['holidays'],2);  ?>]
					}
				]
			};
		
			window.onload = function(){
				window.myRadar = new Chart(document.getElementById("canvas").getContext("2d")).Radar(radarChartData, {
					responsive: true,
					legendTemplate: "<ul class=\"<%=name.toLowerCase()%>-legend\"><% for (var i=0; i<datasets.length; i++){%><li><span style=\"background-color:<%=datasets[i].strokeColor%>\"><%if(datasets[i].label){%><%=datasets[i].label%><%}%></span></li><%}%></ul>"
				});
				
				document.getElementById("legend").innerHTML = myRadar.generateLegend()
			}
	
		</script>
	</body>
</html>