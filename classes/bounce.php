<?php
//require_once __DIR__ . "/../vendor/autoload.php";
$chdir = chdir(substr($_SERVER['SCRIPT_FILENAME'], 0, strpos($_SERVER['SCRIPT_FILENAME'], '/Customizing')));

require_once("Services/Init/classes/class.ilInitialisation.php");
ilInitialisation::initILIAS();

require './Customizing/global/plugins/Services/Repository/RepositoryObject/Panopto/vendor/autoload.php';
$expiration = $_GET['expiration'];
$request_authcode = $_GET['authCode'];
$servername = xpanUtil::getServerName();
$application_key = xpanUtil::getApplicationKey();
$instance_name = xpanUtil::getInstanceName();
$user_key = xpanUtil::getUserKey();

$request_auth_payload = 'serverName=' . $servername . '&expiration=' . $expiration;
$valid_authcode = xpanUtil::validateAuthCode($request_auth_payload, $request_authcode);

if ($valid_authcode && $request_auth_payload) {
    $payload = 'serverName=' . $servername . '&externalUserKey=' . $user_key . '&expiration=' . $expiration;
    $authcode = xpanUtil::generateAuthCode($payload);

    $url = $_GET['callbackURL'] . '&serverName=' . urlencode($servername) . '&externalUserKey=' . urlencode($user_key) . '&expiration=' . $expiration . '&authCode=' . urlencode($authcode);
    header('Location: ' . $url);
} else {
    echo 'Invalid Auth Code.';
}
