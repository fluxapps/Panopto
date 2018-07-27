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
     * Grant multiple users access to folder.
     *
     * @param array $user_ids
     * @param $folder_id
     * @param $role
     */
    public function grantUsersAccessToFolder(array $user_ids, $folder_id, $role) {
        $guids = array();
        foreach ($user_ids as $user_id) {
            $guids[] = $this->getUserGuid($user_id);
        }

        $params = new \Panopto\AccessManagement\GrantUsersAccessToFolder(
            $this->auth,
            $folder_id,
            $guids,
            $role
        );

        /** @var \Panopto\AccessManagement\AccessManagement $access_management */
        $access_management = $this->panoptoclient->AccessManagement();
        $access_management->GrantUsersAccessToFolder($params);
    }

    /**
     * Grant single user access to folder. For current user, leave $user_id = 0
     *
     * @param $folder_id
     * @param $role
     * @param int $user_id
     */
    public function grantUserAccessToFolder($folder_id, $role, $user_id = 0) {
        $this->grantUsersAccessToFolder(array($user_id), $folder_id, $role);
    }

    /**
     * Grant multiple users viewer access to session.
     *
     * @param array $user_ids
     * @param $session_id
     */
    public function grantUsersViewerAccessToSession(array $user_ids, $session_id) {
        $guids = array();
        foreach ($user_ids as $user_id) {
            $guids[] = $this->getUserGuid($user_id);
        }

        $params = new \Panopto\AccessManagement\GrantUsersViewerAccessToSession(
            $this->auth,
            $session_id,
            $guids
        );

        /** @var \Panopto\AccessManagement\AccessManagement $access_management */
        $access_management = $this->panoptoclient->AccessManagement();
        $access_management->GrantUsersViewerAccessToSession($params);
    }

    /**
     *
     * Grant single user viewer access to session. For current user, leave $user_id = 0
     *
     * @param $session_id
     * @param int $user_id
     */
    public function grantUserViewerAccessToSession($session_id, $user_id = 0) {
        $this->grantUsersViewerAccessToSession(array($user_id), $session_id);
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

    /**
     * @param $folder_id
     * @return \Panopto\AccessManagement\FolderAccessDetails
     */
    public function getFolderAccessDetails($folder_id) {
        $params = new \Panopto\AccessManagement\GetFolderAccessDetails(
            $this->auth,
            $folder_id
        );

        /** @var \Panopto\AccessManagement\AccessManagement $access_management */
        $access_management = $this->panoptoclient->AccessManagement();
        return $access_management->GetFolderAccessDetails($params)->getGetFolderAccessDetailsResult();
    }

    /**
     * @param $user_id
     * @return \Panopto\AccessManagement\UserAccessDetails
     */
    public function getUserAccessDetails($user_id = 0) {
        static $user_access_details;
        if (!isset($user_access_details[$user_id])) {
            $params = new \Panopto\AccessManagement\GetUserAccessDetails(
                $this->auth,
                $this->getUserGuid($user_id)
            );

            /** @var \Panopto\AccessManagement\AccessManagement $access_management */
            $access_management = $this->panoptoclient->AccessManagement();
            $user_access_details[$user_id] = $access_management->GetUserAccessDetails($params)->getGetUserAccessDetailsResult();
        }
        return $user_access_details[$user_id];
    }

    /**
     * @param $session_id
     * @return \Panopto\AccessManagement\SessionAccessDetails
     */
    public function getSessionAccessDetails($session_id) {
        static $session_access_details;
        if (!isset($session_access_details[$session_id])) {
            $params = new \Panopto\AccessManagement\GetSessionAccessDetails(
                $this->auth,
                $session_id
            );

            /** @var \Panopto\AccessManagement\AccessManagement $access_management */
            $access_management = $this->panoptoclient->AccessManagement();
            $session_access_details[$session_id] = $access_management->GetSessionAccessDetails($params)->getGetSessionAccessDetailsResult();
        }
        return $session_access_details[$session_id];
    }

    /**
     * @param $folder_id
     * @param int $user_id
     * @return bool|string Creator, Viewer or false
     */
    public function getUserAccessOnFolder($folder_id, $user_id = 0) {
        $user_details = $this->getUserAccessDetails($user_id);
        $user_groups_details = $user_details->getGroupMembershipAccess()->getGroupAccessDetails();

        // fetch creator access folders from groups
        $folders_with_creator_access = array();
        foreach ($user_groups_details as $user_group_details) {
            $folder_ids = $user_group_details->getFoldersWithCreatorAccess()->getGuid();
            if (is_array($folder_ids)) {
                $folders_with_creator_access = array_merge($folders_with_creator_access, $folder_ids);
            }
        }
        $folder_ids = $user_details->getFoldersWithCreatorAccess()->getGuid();
        $folders_with_creator_access = is_array($folder_ids) ? array_merge($folders_with_creator_access, $folder_ids) : $folders_with_creator_access;

        if (in_array($folder_id, $folders_with_creator_access)) {
            return self::ROLE_CREATOR;
        }


        // fetch viewer access folders from groups
        $folders_with_viewer_access = array();
        foreach ($user_groups_details as $user_group_details) {
            $folder_ids = $user_group_details->getFoldersWithViewerAccess()->getGuid();
            if (is_array($folder_ids)) {
                $folders_with_viewer_access = array_merge($folders_with_creator_access, $folder_ids);
            }
        }
        $folder_ids = $user_details->getFoldersWithViewerAccess()->getGuid();
        $folders_with_viewer_access = is_array($folder_ids) ? array_merge($folders_with_viewer_access, $folder_ids) : $folders_with_viewer_access;

        if (in_array($folder_id, $folders_with_viewer_access)) {
            return self::ROLE_VIEWER;
        }
    }

    /**
     * @param int $user_id
     * @return String
     */
    public function getUserGuid($user_id = 0) {
        static $user_guids;
        if (!isset($user_guids[$user_id])) {
            global $DIC;
            $user_id = $user_id ? $user_id : $DIC->user()->getId();
            $user_guids[$user_id] = $this->getUserByKey(xpanUtil::getUserKey($user_id))->getUserId();
        }
        return $user_guids[$user_id];
    }

    /**
     * @param $session_id
     * @param int $user_id
     * @return bool
     */
    public function hasUserViewerAccessOnSession($session_id, $user_id = 0) {
        $user_details = $this->getUserAccessDetails($user_id);
        $session_details = $this->getSessionAccessDetails($session_id);
        $folder_details = $session_details->getFolderAccess();
        $user_groups_details = $user_details->getGroupMembershipAccess()->getGroupAccessDetails();
        $user_groups = array();
        foreach ($user_groups_details as $user_group_details) {
            $user_groups[] = $user_group_details->getGroupId();
        }

        $sessions_with_viewer_access = $user_details->getSessionsWithViewerAccess()->getGuid();
        $groups_with_direct_viewer_access = $session_details->getGroupsWithDirectViewerAccess()->getGuid();
        if (
            $this->hasUserViewerAccessOnFolder($folder_details->getFolderId(), $user_id)
            || (is_array($sessions_with_viewer_access) && in_array($session_id, $sessions_with_viewer_access))
            || (is_array($groups_with_direct_viewer_access) && array_intersect($user_groups, $groups_with_direct_viewer_access))
        ) {
            return true;
        }

        return false;
    }

    /**
     * @param $folder_id
     * @param int $user_id
     * @return bool
     */
    public function hasUserViewerAccessOnFolder($folder_id, $user_id = 0) {
        return in_array($this->getUserAccessOnFolder($folder_id, $user_id), array(self::ROLE_VIEWER, self::ROLE_CREATOR, self::ROLE_PUBLISHER));
    }


    /**
     * @param $folder_id
     * @param int $user_id
     * @return bool
     */
    public function hasUserCreatorAccessOnFolder($folder_id, $user_id = 0) {
        return in_array($this->getUserAccessOnFolder($folder_id, $user_id), array(self::ROLE_CREATOR));
    }



}