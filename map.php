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
		<style>
			html,
			body,
			#map {
			    display: block;
			    width: 100%;
			    height: 100%;
			}
			
			#map {
			    background: #58B;
			}
		</style>
	</head>
	<body>
		<?php require_once('inc/navbar.php');?>
		<div class="container"><div id="map" style="width:100%; min-height:100%; height: 600px;"></div></div>
		<?php
			
			// define the markers array
			$markers = array();
			
			// get transactions
			
			// set the amount spent counter
			$total = 0;
			
			// set the transaction counter
			$i = 0;
			
			// loop through the transactions
			foreach($transactions as $transaction) {
				// coordinates must be present
				// must be a physical location transaction
				// must be negative amount
				// check if the location is there, if it's not an online transaction (physical locations only), and 
				if(!empty($transaction['merchant']['address']['latitude']) && !$transaction['merchant']['online'] && strpos($transaction['amount'], '-') !== false) {
					// add to the array
					array_push($markers, $transaction);
					// add to the total
					$total = $total + $transaction['amount'];
					$i++;
				}
			}
			
			// calc the average amount
			$average = $total / $i;
			
		?>
		<script src="https://maps.google.com/maps/api/js?sensor=true&.js"></script>
		<script src="https://rawgit.com/HPNeo/gmaps/master/gmaps.js"></script>
		<script>
			var map = new GMaps({
				div: '#map',
				lat: 51.5237984,
				lng: -0.0861244,
				zoom: 13
			});
			<?php
				foreach($markers as $transaction) { 
					// find out the logo
					if(empty($transaction['merchant']['logo'])) {
						switch($transaction['transaction']['category']) {
							case 'bills':
								$src = '/cat-bills-big@2x.png';
								break;
							case 'cash':
								$src = '/cat-cash-big@2x.png';
								break;
							case 'eatingout':
								$src = '/cat-eatingout-big@2x.png';
								break;
							case 'entertainment':
								$src = '/cat-entertainment-big@2x.png';
								break;
							case 'expenses':
								$src = '/cat-expenses-big@2x.png';
								break;
							case 'general':
								$src = '/cat-general-big@2x.png';
								break;
							case 'groceries':
								$src = '/cat-groceries-big@2x.png';
								break;
							case 'holidays':
								$src = '/cat-holidays-big@2x.png';
								break;
							case 'shopping':
								$src = '/cat-shopping-big@2x.png';
								break;
							case 'transport':
								$src = '/cat-transport-big@2x.png';
								break;
							case 'mondo':
								$src = '/mondo-big@2x.png';
								break;
							default:
								$src = '/cat-general-big@2x.png';
						}
					}
					else {
						// show the real img
						$src = $transaction['merchant']['logo'];
					}
					
					// calculate the logo size from the average size
					$relsize = $transaction['amount'] / $average;
					$size = round($relsize*50);
					if($size>50) {
						$size = 50;
					}
					if($size<10) {
						$size = 10;
					}
					
			?>
			var myIcon = new google.maps.MarkerImage("<?php echo $src;?>", null, null, null, new google.maps.Size(<?php echo $size.','.$size;?>));
			map.addMarker({
				lat: <?php echo $transaction['merchant']['address']['latitude'];?>,
				lng: <?php echo $transaction['merchant']['address']['longitude'];?>,
				title: '<?php echo addslashes($transaction['merchant']['name']);?>',
				icon: myIcon,
				click: function(e) {
					//alert('You clicked in this marker');
				},
				infoWindow: {
					content: '<p><?php echo addslashes($transaction['merchant']['name']);?></p><p><b><?php echo '&pound;'.($transaction['amount'] / 100)*-1;?></b></p>'
				}
			});
			<?php }?>
		</script>
		<?php require_once('inc/foot.php');?>
	</body>
</html>