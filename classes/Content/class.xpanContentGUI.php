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

        $arrContextOptions=array("ssl"=>array( "verify_peer"=>false, "verify_peer_name"=>false));
        $panoptoclient = new \Panopto\Client(xpanConfig::getConfig(xpanConfig::F_HOSTNAME), array('trace' => 1, 'stream_context' => stream_context_create($arrContextOptions)));
//        $panoptoclient->setAuthenticationInfo(xpanConfig::getConfig(xpanConfig::F_API_USER), xpanConfig::getConfig(xpanConfig::F_API_PASSWORD));
        $panoptoclient->setAuthenticationInfo($user_key, '', xpanConfig::getConfig(xpanConfig::F_APPLICATION_KEY));
        $auth = $panoptoclient->getAuthenticationInfo();

        $page = 0;
        $perpage = 10;
        $pagination = new \Panopto\RemoteRecorderManagement\Pagination();
        $pagination->setPageNumber($page);
        $pagination->setMaxNumberResults($perpage);

        $params = new \Panopto\SessionManagement\GetAllFoldersByExternalId($auth, [$_GET['ref_id']], array(xpanConfig::getConfig(xpanConfig::F_INSTANCE_NAME)));
        /** @var \Panopto\SessionManagement\SessionManagement $session_client */
        $session_client = $panoptoclient->SessionManagement();
        $folder_result = $session_client->GetAllFoldersByExternalId($params);

        $request = new \Panopto\SessionManagement\ListSessionsRequest();
        $request->setPagination($pagination);
        $request->setFolderId(array_shift($folder_result->getGetAllFoldersByExternalIdResult()->getFolder())->getId());
        $params = new \Panopto\SessionManagement\GetSessionsList($auth, $request, '');
        $sessions_result = $session_client->GetSessionsList($params);
        $sessions = $sessions_result->getGetSessionsListResult()->getResults()->getSession();

        $tpl = new ilTemplate('tpl.content_list.html', true, true, $this->pl->getDirectory());

        foreach ($sessions as $session) {
            $tpl->setCurrentBlock('list_item');
            $tpl->setVariable('SID', $session->getId());
            $tpl->setVariable('THUMBNAIL', 'https://' . xpanConfig::getConfig(xpanConfig::F_HOSTNAME) . $session->getThumbUrl());
            $tpl->setVariable('TITLE', $session->getName());
            $tpl->setVariable('DESCRIPTION', $session->getDescription());
            $tpl->setVariable('DURATION', $session->getDuration());
            $tpl->parseCurrentBlock();
        }

        $this->tpl->addCss($this->pl->getDirectory() . '/templates/default/content_list.css');
        $this->tpl->setContent($tpl->get());
    }




    /**
     *Function to create an api auth code for use when calling methods from the Panopto API.
     * @param $userkey
     * @param $servername
     * @param $applicationkey
     * @return string
     */
    protected function generateAuthCode($userkey, $servername, $applicationkey) {
        $payload = $userkey . "@" . $servername;
        $signedpayload = $payload . "|" . $applicationkey;
        $authcode = strtoupper(sha1($signedpayload));
        return $authcode;
    }
}