<?php

/**
 * Class xpanContentGUI
 *
 * @author Theodor Truffer <tt@studer-raimann.ch>
 *
 * @ilCtrl_isCalledBy xpanContentGUI: ilObjPanoptoGUI
 */
class xpanContentGUI extends xpanGUI {

    /**
     * @var \Panopto\stdClass
     */
    protected $auth;
    /**
     * @var \Panopto\Client
     */
    protected $panopto_client;
    /**
     * @var String
     */
    protected $folder_id;

    /**
     * xpanContentGUI constructor.
     * @param ilObjPanoptoGUI $parent_gui
     */
    public function __construct(ilObjPanoptoGUI $parent_gui) {
        parent::__construct($parent_gui);
        $user_key = xpanConfig::getConfig(xpanConfig::F_INSTANCE_NAME) . "\\" . xpanConfig::getConfig(xpanConfig::F_API_USER);

        $arrContextOptions=array("ssl"=>array( "verify_peer"=>false, "verify_peer_name"=>false));
        $this->panoptoclient = new \Panopto\Client(xpanConfig::getConfig(xpanConfig::F_HOSTNAME), array('trace' => 1, 'stream_context' => stream_context_create($arrContextOptions)));
//        $panoptoclient->setAuthenticationInfo(xpanConfig::getConfig(xpanConfig::F_API_USER), xpanConfig::getConfig(xpanConfig::F_API_PASSWORD));
        $this->panoptoclient->setAuthenticationInfo($user_key, '', xpanConfig::getConfig(xpanConfig::F_APPLICATION_KEY));
        $this->auth = $this->panoptoclient->getAuthenticationInfo();


        /** @var \Panopto\UserManagement\UserManagement $user_management */
        $user_management = $this->panoptoclient->UserManagement();
        $params = new \Panopto\UserManagement\SyncExternalUser(
            $this->auth,
            $this->user->getFirstname(),
            $this->user->getLastname(),
            $this->user->getEmail(),
            false,
            array($this->parent_gui->getObject()->getTitle() . ' (ID: ' . $_GET['ref_id'] . ')::Viewer')
        );
        $sync = $user_management->SyncExternalUser($params);

        /** @var \Panopto\Auth\Auth $auth_management */
//        $auth_management = $this->panoptoclient->Auth();
//        $params = new \Panopto\Auth\LogOnWithExternalProvider($user_key, $this->generateAuthCode($user_key, xpanConfig::getConfig(xpanConfig::F_HOSTNAME), xpanConfig::getConfig(xpanConfig::F_APPLICATION_KEY)));
//        $logon = $auth_management->LogOnWithExternalProvider($params);

        /** @var \Panopto\AccessManagement\AccessManagement $access_management */
//        $access_management = $this->panoptoclient->AccessManagement();
//        $access_management-
//        $user_ids = new \Panopto\AccessManagement\ArrayOfguid();
//        $user_ids->setGuid(array((xpanConfig::getConfig(xpanConfig::F_USER_ID) == xpanConfig::SUB_F_LOGIN) ? $this->user->getLogin() : $this->user->getExternalAccount()));
//        $params = new \Panopto\AccessManagement\GrantUsersAccessToFolder(
//            $this->auth,
//            $this->folder_id,
//            $user_ids,
//            \Panopto\AccessManagement\AccessRole::Viewer);
//        $access_management->GrantUsersAccessToFolder($params);

    }

    protected function index() {
        $this->loadFolder();
        $sessions = $this->getSessions();

        if (!$sessions) {
            ilUtil::sendInfo($this->pl->txt('msg_no_videos'));
            return;
        }

        $tpl = new ilTemplate('tpl.content_list.html', true, true, $this->pl->getDirectory());

        foreach ($sessions as $session) {
            $tpl->setCurrentBlock('list_item');
            $tpl->setVariable('SID', $session->getId());
            $tpl->setVariable('THUMBNAIL', 'https://' . xpanConfig::getConfig(xpanConfig::F_HOSTNAME) . $session->getThumbUrl());
            $tpl->setVariable('TITLE', $session->getName());
            $tpl->setVariable('DESCRIPTION', $session->getDescription());
            $tpl->setVariable('DURATION', $this->formatDuration($session->getDuration()));
            $tpl->parseCurrentBlock();
        }

        $this->tpl->addCss($this->pl->getDirectory() . '/templates/default/content_list.css');
        $this->tpl->addJavaScript($this->pl->getDirectory() . '/js/Panopto.js');
//        $this->tpl->addOnLoadCode('$("div.box").each(function(k, e) {$(e).delay(500).width("100%");});');
        $this->tpl->setContent($tpl->get() . $this->getModalPlayer());
    }

    /**
     *
     */
    protected function loadFolder() {
        $params = new \Panopto\SessionManagement\GetAllFoldersByExternalId($this->auth, [$_GET['ref_id']], array(xpanConfig::getConfig(xpanConfig::F_INSTANCE_NAME)));
        /** @var \Panopto\SessionManagement\SessionManagement $session_client */
        $session_client = $this->panoptoclient->SessionManagement();
        $folder_result = $session_client->GetAllFoldersByExternalId($params);
        $this->folder_id = array_shift($folder_result->getGetAllFoldersByExternalIdResult()->getFolder())->getId();
    }

    /**
     * @return mixed
     */
    protected function getSessions() {
        $session_client = $this->panoptoclient->SessionManagement();

        $page = 0;
        $perpage = 10;
        $pagination = new \Panopto\RemoteRecorderManagement\Pagination();
        $pagination->setPageNumber($page);
        $pagination->setMaxNumberResults($perpage);

        $request = new \Panopto\SessionManagement\ListSessionsRequest();
        $request->setPagination($pagination);
        $request->setFolderId($this->folder_id);
        $states = new \Panopto\SessionManagement\ArrayOfSessionState();
        $states->setSessionState(array(\Panopto\SessionManagement\SessionState::Complete));
        $request->setStates($states);

        $params = new \Panopto\SessionManagement\GetSessionsList($this->auth, $request, '');
        $sessions_result = $session_client->GetSessionsList($params);
        $sessions = $sessions_result->getGetSessionsListResult()->getResults()->getSession();
        return $sessions;
    }

    protected function formatDuration($duration_in_seconds) {
        $t = floor($duration_in_seconds);
        return sprintf('%02d:%02d:%02d', ($t/3600),($t/60%60), $t%60);
    }

    /**
     * @return String
     */
    protected function getModalPlayer() {
        $this->tpl->addCss($this->pl->getDirectory() . '/templates/default/modal.css');
        $modal = ilModalGUI::getInstance();
        $modal->setId('xpan_modal_player');
        $modal->setType(ilModalGUI::TYPE_LARGE);
//		$modal->setHeading('<div id="xoct_waiter_modal" class="xoct_waiter xoct_waiter_mini"></div>');
        $modal->setBody('<section><div id="xpan_video_container"></div></section>');
        return $modal->getHTML();
    }

//
//    /**
//     * ajax
//     */
//    public function fillModalPlayer() {
//        $mid = $_GET['mid'];
//        $video = xvmpMedium::find($mid);
//        $video_infos = "
//			<p>{$this->pl->txt(xvmpMedium::F_DURATION)}: {$video->getDurationFormatted()}</p>
//			<p>{$this->pl->txt(xvmpMedium::F_CREATED_AT)}: {$video->getCreatedAt('m.d.Y, H:i')}</p>
//
//		";
//        foreach (xvmpConf::getConfig(xvmpConf::F_FORM_FIELDS) as $field) {
//            if ($value = $video->getField($field[xvmpConf::F_FORM_FIELD_ID])) {
//                $video_infos .= "<p>{$field[xvmpConf::F_FORM_FIELD_TITLE]}: {$value}</p>";
//            }
//        }
//        $video_infos .= "<p class='xvmp_ellipsis'>{$this->pl->txt(xvmpMedium::F_DESCRIPTION)}: {$video->getDescription()}</p>";
//        $response = new stdClass();
//        $video_player = new xvmpVideoPlayer($video, xvmp::useEmbeddedPlayer($this->getObjId()));
//        $response->html = $video_player->getHTML() . $video_infos;
//        $response->video_title = $video->getTitle();
//        /** @var xvmpUserProgress $progress */
//        $progress = xvmpUserProgress::where(array(xvmpUserProgress::F_USR_ID => $this->user->getId(), xvmpMedium::F_MID => $mid))->first();
//        if ($progress) {
//            $response->time_ranges = json_decode($progress->getRanges());
//        } else {
//            $response->time_ranges = array();
//        }
//        echo json_encode($response);
//        exit;
//    }

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