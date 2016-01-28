<div class="container">
	<div class="row">
		<div class="col-sm-6 hidden-xs">
			<a href="/">
				<img class="logo" src="/assets/img/logo.svg" height="60em">
			</a>
		</div>
		<div class="col-sm-6 col-xs-12">
			<div class="top-links pull-right">
				<ul class="nav nav-pills">
					<li role="presentation" class="active"><a href="#"><i class="fa fa-home"></i> Home</a></li>
					<li role="presentation"><a href="#"><i class="fa fa-info-circle"></i> Profile</a></li>
					<li role="presentation"><a href="https://twitter.com/getmondo" target="_blank"><i class="fa fa-twitter"></i> Contact Mondo</a></li>
				</ul>
			</div>
		</div>
	</div>
	<div class="row">
		<nav class="navbar navbar-default">
			<div class="container-fluid">
				<!-- Brand and toggle get grouped for better mobile display -->
				<div class="navbar-header">
					<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1" aria-expanded="false">
						<span class="sr-only">Toggle navigation</span>
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
					</button>
					<a class="navbar-brand brand-custom" href="/"><img src="/assets/img/mondo-bw.png" style="height:100%;"></a>
				</div>
		
				<!-- Collect the nav links, forms, and other content for toggling -->
				<div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
					<ul class="nav navbar-nav">
						<li><a href="/">Transactions</a></li>
						<li><a href="/map.php">Map</a></li>
						<li class="dropdown">
							<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Statements <span class="caret"></span></a>
							<ul class="dropdown-menu">
								<?php
									// get transactions
									require_once('scripts/transactions.php');
									$transactions = getTransactions($_SESSION['accesstoken'], $_SESSION['account_number'], false, $api_root);
									$first_transaction = array_reverse($transactions);
									
									// first month
									$start    = new DateTime($first_transaction[0]['created']);
									$start->modify('first day of this month');
									$end      = new DateTime(date('Y-m-d'));
									$end->modify('first day of next month');
									$interval = DateInterval::createFromDateString('1 month');
									$period   = new DatePeriod($start, $interval, $end);
									
									foreach ($period as $dt) {
										echo '<li><a href="/scripts/pdfGen.php?date='.$dt->format('Y-m-d').'">'.$dt->format('F Y').'</a></li>';
									}
								?>
							</ul>
						</li>
						<li><a href="/exportcsv.php">Export CSV</a></li>
					</ul>
					<ul class="nav navbar-nav navbar-right">
						<li>
							<?php
								require_once('scripts/freezeStatus.php');
								$cards = listCards($_SESSION['accesstoken'], $_SESSION['account_number'], $api_root);
							?>
							<a href="/scripts/freeze.php?cardid=<?php echo $cards[0]['id'];?>&status=<?php if($cards[0]['status']=='ACTIVE') {echo 'INACTIVE';} else {echo 'ACTIVE';}?>&return=<?php echo "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]".'&api_root='.$api_root;?>" style="color: #<?php if($cards[0]['status']=='ACTIVE') {echo '9ABBA8';} else {echo 'e64b5f';}?> !important;">
								<?php if($cards[0]['status']=='ACTIVE') {echo 'Card active';} else {echo 'Card frozen';}?>
							</a>
						</li>
						<li class="dropdown">
							<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false"><?php echo $_SESSION['fullname'];?> <span class="caret"></span></a>
							<ul class="dropdown-menu">
								<li><a href="/logout.php"><i class="fa fa-sign-out"></i> Logout</a></li>
							</ul>
						</li>
					</ul>
				</div><!-- /.navbar-collapse -->
			</div><!-- /.container-fluid -->
		</nav>
	</div>
</div>
