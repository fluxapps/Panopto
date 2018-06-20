<?php
require_once("./sample_auth_soap_client.php");


/*
 *Function to create an api auth code for use when calling methods from the Panopto API.
 */
function generate_auth_code($userkey, $servername, $applicationkey) {
	$payload = $userkey . "@" . $servername;
	$signedpayload = $payload . "|" . $applicationkey;
	$authcode = strtoupper(sha1($signedpayload));
	return $authcode;
}

// Server name of the target panopto server
$servername = "demo.hosted.panopto.com";

// User name of the user calling the API.
$userkey = "<USER NAME HERE>"; // e.g. <instanceName>\<userName>

// Application key the user is tied to on Panopto.
$applicatoinkey = "xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx";

$authcode = generate_auth_code($userkey, $servername, $applicatoinkey);

$panoptoauthclient = new panopto_auth_soap_client($servername, $userkey, $authcode);

// Get the version of the Panopto server;
$serverversionresult = $panoptoauthclient->get_server_version();

print_r($serverversionresult);
?>