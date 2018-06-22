<?php
//require_once __DIR__ . "/../vendor/autoload.php";
$chdir = chdir(substr($_SERVER['SCRIPT_FILENAME'], 0, strpos($_SERVER['SCRIPT_FILENAME'], '/Customizing')));

require_once("Services/Init/classes/class.ilInitialisation.php");
ilInitialisation::initILIAS();

//TODO validate authcode

global $DIC;

require './Customizing/global/plugins/Services/Repository/RepositoryObject/Panopto/vendor/autoload.php';
$expiration = $_GET['expiration'];
$servername = xpanConfig::getConfig(xpanConfig::F_HOSTNAME);
$applicationkey = xpanConfig::getConfig(xpanConfig::F_APPLICATION_KEY);
$instancename = xpanConfig::getConfig(xpanConfig::F_INSTANCE_NAME);
$userkey = $instancename . '\\' . (xpanConfig::getConfig(xpanConfig::F_USER_ID) == xpanConfig::SUB_F_LOGIN) ? $DIC->user()->getLogin() : $DIC->user()->getExternalAccount();

$payload = 'serverName=' . $servername . '&externalUserKey=' . $userkey . '&expiration=' . $expiration;

$signedpayload = $payload . "|" . $applicationkey;
$authcode = strtoupper(sha1($signedpayload));

$url = $_GET['callbackURL'] . '&serverName=' . urlencode($servername) . '&externalUserKey=' . urlencode($userkey) . '&expiration=' . $expiration . '&authCode=' . urlencode($authcode);
header('Location: ' . $url);
