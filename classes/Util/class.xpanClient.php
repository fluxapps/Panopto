<?php

/**
 * Class xpanClient
 *
 * @author Theodor Truffer <tt@studer-raimann.ch>
 */
class xpanClient {

    const ROLE_VIEWER = \Panopto\AccessManagement\AccessRole::Viewer;
    const ROLE_VIEWER_WITH_LINK = \Panopto\AccessManagement\AccessRole::ViewerWithLink;
    const ROLE_CREATOR = \Panopto\AccessManagement\AccessRole::Creator;
    const ROLE_PUBLISHER = \Panopto\AccessManagement\AccessRole::Publisher;

    /**
     * @var xpanClient
     */
    protected static $instance;


    /**
     * @return xpanClient
     */
    public static function getInstance() {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }


    /**
     * @var \Panopto\Client
     */
    protected $panoptoclient;
    /**
     * @var \Panopto\stdClass
     */
    protected $auth;

    /**
     * xpanClient constructor.
     */
    public function __construct() {
        $arrContextOptions=array("ssl"=>array( "verify_peer"=>false, "verify_peer_name"=>false));
        $this->panoptoclient = new \Panopto\Client(xpanConfig::getConfig(xpanConfig::F_HOSTNAME), array('trace' => 1, 'stream_context' => stream_context_create($arrContextOptions)));
//        $panoptoclient->setAuthenticationInfo(xpanConfig::getConfig(xpanConfig::F_API_USER), xpanConfig::getConfig(xpanConfig::F_API_PASSWORD));
        $this->panoptoclient->setAuthenticationInfo(xpanUtil::getApiUserKey(), '', xpanConfig::getConfig(xpanConfig::F_APPLICATION_KEY));
        $this->auth = $this->panoptoclient->getAuthenticationInfo();
    }

    /**
     * @return \Panopto\SessionManagement\Folder[]
     */
    public function getAllFoldersByExternalId(array $ext_ids) {
        $params = new \Panopto\SessionManagement\GetAllFoldersByExternalId($this->auth, $ext_ids, array(xpanConfig::getConfig(xpanConfig::F_INSTANCE_NAME)));
        /** @var \Panopto\SessionManagement\SessionManagement $session_client */
        $session_client = $this->panoptoclient->SessionManagement();
        return $session_client->GetAllFoldersByExternalId($params)->getGetAllFoldersByExternalIdResult()->getFolder();
    }

    /**
     * @param $ext_id
     * @return \Panopto\SessionManagement\Folder
     */
    public function getFolderByExternalId($ext_id) {
        return array_shift($this->getAllFoldersByExternalId(array($ext_id)));
    }

    /**
     * @param string $user_key
     * @return \Panopto\UserManagement\User
     */
    public function getUserByKey($user_key = '') {
        /** @var \Panopto\UserManagement\UserManagement $user_management */
        $user_management = $this->panoptoclient->UserManagement();

        $params = new \Panopto\UserManagement\GetUserByKey(
            $this->auth,
            $user_key ? $user_key : xpanUtil::getUserKey()
        );
        $response = $user_management->GetUserByKey($params);
        return $response->getGetUserByKeyResult();
    }

    /**
     * @param array $user_ids
     * @param $folder_id
     * @param $role
     */
    public function grantUsersAccessToFolder(array $user_ids, $folder_id, $role) {
        $guids = array();
        foreach ($user_ids as $user_id) {
            $guids[] = $this->getUserByKey(xpanUtil::getUserKey($user_id))->getUserId();
        }

        $params = new \Panopto\AccessManagement\GrantUsersAccessToFolder(
            $this->auth,
            $folder_id,
            $guids,
            $role
        );

        $access_management = $this->panoptoclient->AccessManagement();
        $access_management->GrantUsersAccessToFolder($params);
    }

    /**
     * @param $folder_id
     * @param $role
     */
    public function grantCurrentUserAccessToFolder($folder_id, $role) {
        $this->grantUsersAccessToFolder(array(0), $folder_id, $role);
    }

    /**
     * @return mixed
     */
    public function getSessionsOfFolder($folder_id) {
        $session_client = $this->panoptoclient->SessionManagement();

        $page = 0;
        $perpage = 10;
        $pagination = new \Panopto\RemoteRecorderManagement\Pagination();
        $pagination->setPageNumber($page);
        $pagination->setMaxNumberResults($perpage);

        $request = new \Panopto\SessionManagement\ListSessionsRequest();
        $request->setPagination($pagination);
        $request->setFolderId($folder_id);
        $states = new \Panopto\SessionManagement\ArrayOfSessionState();
        $states->setSessionState(array(\Panopto\SessionManagement\SessionState::Complete));
        $request->setStates($states);

        $params = new \Panopto\SessionManagement\GetSessionsList($this->auth, $request, '');
        $sessions_result = $session_client->GetSessionsList($params);
        $sessions = $sessions_result->getGetSessionsListResult()->getResults()->getSession();
        return $sessions;
    }

}