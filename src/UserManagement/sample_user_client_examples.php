<?php
require_once("./sample_user_soap_client.php");


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

$panoptouserclient = new panopto_user_soap_client($servername, $userkey, $authcode);

//Get a user by userkey\\
$getuserbykeyresult = $panoptouserclient->get_user_by_key($userkey);
print_r($getuserbykeyresult);

//Create a user in Panopto\\
$createuserresult = $panoptouserclient->create_user(
	"johnny.fakeface@fakedummy.com", 
	false, 
	"johnny", 
	null, 
	"fakeface",
	null,
	null,
	null,
	"johnny.fakeface",
	null,
	null
);
print_r($createuserresult);

//Delete a user in Panopto\\
// Use the ID of the just created user to clean them up, comment this call out if you wish to inspect the user before deletion.
$deleteusersresult = $panoptouserclient->delete_users($createuserresult->CreateUserResult);
print_r($deleteusersresult);
?>