<?php
require_once("./sample_session_soap_client.php");


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

$panoptosessionclient = new panopto_session_soap_client($servername, $userkey, $authcode);

//Get a list of folders that are either public or the user has at least viewer access.\\
$sessiongrouplists = $panoptosessionclient->get_folders_list();
print_r($sessiongrouplists);

//Add a folder to Panopto.\\
$addfolderresult = $panoptosessionclient->add_folder("test_add_folder_1");
print_r($addfolderresult);

//Deletes a folder to Panopto.\\
// Get the Id of the folder made above and delete it.
//comment this call out if you want to see the folder you created above before deleting it.
// The response for this is usually empty, and any errors will be logged inside the client call function.
$deletefoldersresult = $panoptosessionclient->delete_folders($addfolderresult->AddFolderResult->Id);
print_r($deletefoldersresult)
?>