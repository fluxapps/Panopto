<?php

/**
 * Class xpanContentGUI
 *
 * @author Theodor Truffer <tt@studer-raimann.ch>
 *
 * @ilCtrl_isCalledBy xpanContentGUI: ilObjPanoptoGUI
 */
class xpanContentGUI extends xpanGUI {

    protected function index() {
        $user_key = xpanConfig::getConfig(xpanConfig::F_INSTANCE_NAME) . "\\" . xpanConfig::getConfig(xpanConfig::F_API_USER);
        $auth_code = $this->generate_auth_code($user_key, 'fh-muenster.cloud.panopto.eu', xpanConfig::getConfig(xpanConfig::F_APPLICATION_KEY));
        $client = new panopto_session_soap_client(xpanConfig::getConfig(xpanConfig::F_HOSTNAME), $user_key, $auth_code);

        $panoptoclient = new \Panopto\Client('fh-muenster.cloud.panopto.eu');
//        $panoptoclient->setAuthenticationInfo(xpanConfig::getConfig(xpanConfig::F_API_USER), xpanConfig::getConfig(xpanConfig::F_API_PASSWORD));
        $panoptoclient->setAuthenticationInfo($user_key, '', xpanConfig::getConfig(xpanConfig::F_APPLICATION_KEY));
        $auth = $panoptoclient->getAuthenticationInfo();

        $page = 0;
        $perpage = 10;
        $pagination = new \Panopto\RemoteRecorderManagement\Pagination();
        $pagination->setPageNumber($page);
        $pagination->setMaxNumberResults($perpage);

        $request = new \Panopto\SessionManagement\ListFoldersRequest();
//        $request->setWildcardSearchNameOnly(true);
        $request->setPagination($pagination);

//        $param = new \Panopto\SessionManagement\GetFoldersList($auth, $request, ilObjPanopto::_lookupTitle(ilObjPanopto::_lookupObjId($_GET['ref_id'])));
        $param = new \Panopto\SessionManagement\GetFoldersList($auth, $request, ilObjPanopto::_lookupTitle(ilObjPanopto::_lookupObjId($_GET['ref_id'])) . ' \(ID: ' . $_GET['ref_id'] . '\)');

        /** @var \Panopto\SessionManagement\SessionManagement $session_client */
        $session_client = $panoptoclient->SessionManagement();
        $result = $session_client->GetFoldersList($param)->getGetFoldersListResult();

//        echo "123";
//        var_dump($result);exit;
//        $folder = $client->get_folders_by_id(array('1304e1c9-aced-41dd-9370-a905009656e2'));
//        echo 'hello';
//        var_dump($client->get_session_list("e0ccd7a4-7c4f-4003-921f-a8f900c00bf9"));exit;
//        var_dump($client->get_folders_by_id(array("e0ccd7a4-7c4f-4003-921f-a8f900c00bf9")));exit;
    }


    /*
 *Function to create an api auth code for use when calling methods from the Panopto API.
 */
    protected function generate_auth_code($userkey, $servername, $applicationkey) {
        $payload = $userkey . "@" . $servername;
        $signedpayload = $payload . "|" . $applicationkey;
        $authcode = strtoupper(sha1($signedpayload));
        return $authcode;
    }
}