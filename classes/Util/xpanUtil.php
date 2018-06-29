<?php

/**
 * Class xpanUtil
 *
 * @author Theodor Truffer <tt@studer-raimann.ch>
 */
class xpanUtil {

    public static function getServerName() {
        return xpanConfig::getConfig(xpanConfig::F_HOSTNAME);
    }

    public static function getApplicationKey() {
        return xpanConfig::getConfig(xpanConfig::F_APPLICATION_KEY);
    }

    public static function getInstanceName() {
        return xpanConfig::getConfig(xpanConfig::F_INSTANCE_NAME);
    }

    public static function getUserId() {
        global $DIC;
        return (xpanConfig::getConfig(xpanConfig::F_USER_ID) == xpanConfig::SUB_F_LOGIN) ? $DIC->user()->getLogin() : $DIC->user()->getExternalAccount();
    }

    public static function getUserKey() {
        return self::getInstanceName() . '\\' . self::getUserId();
    }

    public static function generateAuthCode($payload) {
        $signedpayload = $payload . "|" . self::getApplicationKey();
        return strtoupper(sha1($signedpayload));
    }

    public static function validateAuthCode($payload, $auth_code) {
        return (self::generateAuthCode($payload) == $auth_code);
    }

//    public static function getFolderIDForRefID($ref_id = 0) {
//        if (!$ref_id) {
//            $ref_id = $_GET['ref_id'];
//        }
//        $params = new \Panopto\SessionManagement\GetAllFoldersByExternalId($this->auth, [$_GET['ref_id']], array(xpanConfig::getConfig(xpanConfig::F_INSTANCE_NAME)));
//        /** @var \Panopto\SessionManagement\SessionManagement $session_client */
//        $session_client = $this->panoptoclient->SessionManagement();
//        $folder_result = $session_client->GetAllFoldersByExternalId($params);
//        $this->folder_id = array_shift($folder_result->getGetAllFoldersByExternalIdResult()->getFolder())->getId();
//    }
}