<!DOCTYPE html>
<html>
	<head>
		<?php require_once('includes/head.php');?>
		<link href="/assets/css/login.css" rel="stylesheet">
	</head>
	<body>
        <div class="wrapper">
            <div class="container">
                <form class="form-signin" action="/scripts/login.php" method="post">
                    <div class="form-signin-heading">
                        <div class="row">
                            <div class="col-xs-12">
                                <img id="logo" src="/assets/img/mondo.png">
                                <h2 class="text-center">Log In to Mondo</h2>
                            </div>
                        </div>
                        <?php
                        if(isset($_GET['expired'])) {
                            echo '<div class="alert alert-warning">Your access token expired, please login again.</div>';
                        }
                        ?>
                    </div>
                    <label for="inputEmail" class="sr-only">Email address</label>
                    <input type="email" id="inputEmail" name="username" class="form-control" placeholder="Email address" required autofocus>
                    <label for="inputPassword" class="sr-only">Password</label>
                    <input type="password" id="inputPassword" name="password" class="form-control" placeholder="Password" required>
                    <button class="btn btn-lg btn-primary btn-block" type="submit">Sign in</button>
                </form>
            </div>
        </div>
	</body>
</html>