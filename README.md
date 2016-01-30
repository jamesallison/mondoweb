# MondoWeb
### An online banking client built on the Mondo API

Hoping to release this open source Mondo online banking client once Mondo becomes more publicly available. For now, you may host this on your own webserver.

## Installation
1. Create a file called settings.php in the inc directory.
2. Paste in the following code
3. Place your Mondo OAuth credentials in the file

```
<?php
	// your client id
	$clientid = 'YOUR_CLIENT_ID_HERE';
	// your client secret
	$clientsecret = 'YOUR_CLIENT_SECRET_HERE';
	// root url without the trailing slash!
	$rooturl = 'http://yourdomain.com';
	// set the api auth root url - *you don't need to touch this*
	$api_auth_root = 'https://auth.getmondo.co.uk';
	// api root url - *you don't need to touch this*
	$api_root = 'https://api.getmondo.co.uk';
?>
```
