<?php

/**
 * Class xpanUtil
 *
 * @author Theodor Truffer <tt@studer-raimann.ch>
 */
class xpanUtil {

	/**
	 * @return mixed
	 */
	public static function getServerName() {
        return xpanConfig::getConfig(xpanConfig::F_HOSTNAME);
    }


	/**
	 * @return mixed
	 */
	public static function getApplicationKey() {
        return xpanConfig::getConfig(xpanConfig::F_APPLICATION_KEY);
    }


	/**
	 * @return mixed
	 */
	public static function getInstanceName() {
        return xpanConfig::getConfig(xpanConfig::F_INSTANCE_NAME);
    }


	/**
	 * @param int $user_id
	 *
	 * @return mixed
	 */
	public static function getUserIdentifier($user_id = 0) {
        global $DIC;
        $user = $user_id ? new ilObjUser($user_id) : $DIC->user();
        return (xpanConfig::getConfig(xpanConfig::F_USER_ID) == xpanConfig::SUB_F_LOGIN) ? $user->getLogin() : $user->getExternalAccount();
    }


	/**
	 * @param int $user_id
	 *
	 * @return string
	 */
	public static function getUserKey($user_id = 0) {
        return self::getInstanceName() . '\\' . self::getUserIdentifier($user_id);
    }


	/**
	 * @return string
	 */
	public static function getApiUserKey() {
        return xpanConfig::getConfig(xpanConfig::F_INSTANCE_NAME) . "\\" . xpanConfig::getConfig(xpanConfig::F_API_USER);
    }


	/**
	 * @param $payload
	 *
	 * @return string
	 */
	public static function generateAuthCode($payload) {
        $signedpayload = $payload . "|" . self::getApplicationKey();
        return strtoupper(sha1($signedpayload));
    }


	/**
	 * @param $payload
	 * @param $auth_code
	 *
	 * @return bool
	 */
	public static function validateAuthCode($payload, $auth_code) {
        return (self::generateAuthCode($payload) == $auth_code);
    }


	/**
	 * @param ilObjPanopto $object
	 * @param int          $ref_id
	 *
	 * @return string
	 */
	public static function getExternalIdOfObject(ilObjPanopto $object, $ref_id = 0) {
        $ref_id = $ref_id ? $ref_id : $_GET['ref_id'];
        return $object->getTitle() . ' (ID: ' . $ref_id . ')';
    }


	/**
	 * @param int $ref_id
	 *
	 * @return string
	 */
	public static function getExternalIdOfObjectById($ref_id = 0) {
        $ref_id = $ref_id ? $ref_id : $_GET['ref_id'];
        return ilObjPanopto::_lookupTitle(ilObjPanopto::_lookupObjId($_GET['ref_id'])) . ' (ID: ' . $ref_id . ')';
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