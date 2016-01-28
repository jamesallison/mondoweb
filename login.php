<?php
	// they're logging in, destroy session
	session_start();
	session_destroy();
?>
<!DOCTYPE html>
<html>
	<head>
		<?php require_once('inc/head.php');?>
		<link href="/assets/css/login.css" rel="stylesheet">
	</head>
	<body>
        <div class="wrapper">
            <div class="container">
                <center>
                	<?php
	                	if(isset($_GET['expired'])) {
		                	echo '<div class="alert alert-warning">Your session expired, please login to Mondo again.</div>';
	                	}
                	?>
                	<a class="mondoLogin" href="/auth/stategen.php">Login with Mondo</a>
                	<p class="login-tip">This will take you to Mondo where you can login.</p>
                </center>
            </div>
        </div>
	</body>
</html>