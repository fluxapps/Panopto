<?php

use Panopto\AccessManagement\AccessManagement;
use \Panopto\AccessManagement\AccessRole;
use Panopto\AccessManagement\FolderAccessDetails;
use Panopto\AccessManagement\GetFolderAccessDetails;
use Panopto\AccessManagement\GetSessionAccessDetails;
use Panopto\AccessManagement\GetUserAccessDetails;
use Panopto\AccessManagement\GrantUsersAccessToFolder;
use Panopto\AccessManagement\GrantUsersViewerAccessToSession;
use Panopto\AccessManagement\UserAccessDetails;
use Panopto\Client as PanoptoClient;
use Panopto\RemoteRecorderManagement\Pagination;
use Panopto\SessionManagement\ArrayOfSessionState;
use Panopto\SessionManagement\Folder;
use Panopto\SessionManagement\GetAllFoldersByExternalId;
use Panopto\SessionManagement\GetSessionsList;
use Panopto\SessionManagement\ListSessionsRequest;
use Panopto\SessionManagement\SessionManagement;
use Panopto\SessionManagement\SessionState;
use Panopto\UserManagement\CreateUser;
use Panopto\UserManagement\GetUserByKey;
use Panopto\UserManagement\SyncExternalUser;
use Panopto\UserManagement\User;
use Panopto\UserManagement\UserManagement;

/**
 * Class xpanClient
 *
 * @author Theodor Truffer <tt@studer-raimann.ch>
 */
class xpanClient {

	const ROLE_VIEWER = AccessRole::Viewer;
	const ROLE_VIEWER_WITH_LINK = AccessRole::ViewerWithLink;
	const ROLE_CREATOR = AccessRole::Creator;
	const ROLE_PUBLISHER = AccessRole::Publisher;

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
     * @var Client
     */
    protected $panoptoclient;
    /**
     * @var \Panopto\stdClass
     */
    protected $auth;
    /**
     * @var xpanLog
     */
    protected $log;

    /**
     * xpanClient constructor.
     */
    public function __construct() {
        $this->log = xpanLog::getInstance();

        $arrContextOptions=array("ssl"=>array( "verify_peer"=>false, "verify_peer_name"=>false));
        $this->panoptoclient = new PanoptoClient(xpanConfig::getConfig(xpanConfig::F_HOSTNAME), array('trace' => 1, 'stream_context' => stream_context_create($arrContextOptions)));
        $this->panoptoclient->setAuthenticationInfo(xpanUtil::getApiUserKey(), '', xpanConfig::getConfig(xpanConfig::F_APPLICATION_KEY));
        $this->auth = $this->panoptoclient->getAuthenticationInfo();
    }

    /**
     * @return Folder[]
     * @throws Exception
     */
    public function getAllFoldersByExternalId(array $ext_ids) {
        $this->log->write('*********');
        $this->log->write('SOAP call "GetAllFoldersByExternalId"');
        $this->log->write("folderExternalIds:");
        $this->log->write(print_r($ext_ids, true));
        $this->log->write("providerNames:");
        $this->log->write(print_r(array(xpanConfig::getConfig(xpanConfig::F_INSTANCE_NAME)), true));

        $params = new GetAllFoldersByExternalId(
            $this->auth,
            $ext_ids,
            array(xpanConfig::getConfig(xpanConfig::F_INSTANCE_NAME))
        );

        /** @var SessionManagement $session_client */
        $session_client = $this->panoptoclient->SessionManagement();
        try {
            $return = $session_client->GetAllFoldersByExternalId($params)->getGetAllFoldersByExternalIdResult()->getFolder();
        } catch (Exception $e) {
            $this->logException($e, $session_client);
            throw $e;
        }
        $this->log->write('Status: ' . substr($session_client->__last_response_headers, 0, strpos($session_client->__last_response_headers, "\r\n")));
        $return = is_array($return) ? $return : array();
        $this->log->write('Received ' . (int) count($return) . ' object(s).');
        return $return;
    }

    /**
     * @param $ext_id
     * @return Folder
     * @throws Exception
     */
    public function getFolderByExternalId($ext_id) {
        return array_shift($this->getAllFoldersByExternalId(array($ext_id)));
    }

    /**
     * @param string $user_key
     * @return User
     * @throws Exception
     */
    public function getUserByKey($user_key = '') {
        $user_key = $user_key ? $user_key : xpanUtil::getUserKey();

        $this->log->write('*********');
        $this->log->write('SOAP call "getUserByKey"');
        $this->log->write("userKey:");
        $this->log->write(print_r($user_key, true));

        /** @var UserManagement $user_management */
        $user_management = $this->panoptoclient->UserManagement();

        $params = new GetUserByKey(
            $this->auth,
            $user_key
        );

        try {
            $return = $user_management->GetUserByKey($params)->getGetUserByKeyResult();
        } catch (Exception $e) {
            $this->logException($e, $user_management);
            throw $e;
        }

        if ($return->getUserId() == '00000000-0000-0000-0000-000000000000') {
            $this->log->write('Status: User Not Found');
            $this->createUser($user_key);

            try {
                $this->log->write('*********');
                $this->log->write('SOAP call "getUserByKey"');
                $this->log->write("userKey:");
                $this->log->write(print_r($user_key, true));
                $return = $user_management->GetUserByKey($params)->getGetUserByKeyResult();
            } catch (Exception $e) {
                $this->logException($e, $user_management);
                throw $e;
            }
        }
        $this->log->write('Status: ' . substr($user_management->__last_response_headers, 0, strpos($user_management->__last_response_headers, "\r\n")));
        $this->log->write('Found user with id: ' . $return->getUserId());

        return $return;
    }

    /**
     * @param $user_key
     * @throws Exception
     */
    public function createUser($user_key) {
        global $DIC;
        $this->log->write('*********');
        $this->log->write('SOAP call "createUser"');
        $this->log->write("userKey:");
        $this->log->write(print_r($user_key, true));

        $user = new User();
        $user->setFirstName($DIC->user()->getFirstname());
        $user->setLastName($DIC->user()->getLastname());
        $user->setEmail($DIC->user()->getEmail());
        $user->setUserKey($user_key);

        $params = new CreateUser(
            $this->auth,
            $user,
            ''
        );

        /** @var UserManagement $user_management */
        $user_management = $this->panoptoclient->UserManagement();
        try {
            $user_management->CreateUser($params);
        } catch (Exception $e) {
            $this->logException($e, $user_management);
            throw $e;
        }

        $this->log->write('Status: ' . substr($user_management->__last_response_headers, 0, strpos($user_management->__last_response_headers, "\r\n")));
    }

    /**
     * Grant multiple users access to folder.
     *
     * @param array $user_ids
     * @param $folder_id
     * @param $role
     * @throws Exception
     */
    public function grantUsersAccessToFolder(array $user_ids, $folder_id, $role) {
        $guids = array();
        foreach ($user_ids as $user_id) {
            $guids[] = $this->getUserGuid($user_id);
        }

        $this->log->write('*********');
        $this->log->write('SOAP call "GrantUsersAccessToFolder"');
        $this->log->write("folderId:");
        $this->log->write(print_r($folder_id, true));
        $this->log->write("userIds:");
        $this->log->write(print_r($guids, true));
        $this->log->write("role:");
        $this->log->write(print_r($role, true));
        $params = new GrantUsersAccessToFolder(
            $this->auth,
            $folder_id,
            $guids,
            $role
        );

        /** @var AccessManagement $access_management */
        $access_management = $this->panoptoclient->AccessManagement();
        try {
            $access_management->GrantUsersAccessToFolder($params);
        } catch (Exception $e) {
            $this->logException($e, $access_management);
            throw $e;
        }

        $this->log->write('Status: ' . substr($access_management->__last_response_headers, 0, strpos($access_management->__last_response_headers, "\r\n")));
    }

    /**
     * Grant single user access to folder. For current user, leave $user_id = 0
     *
     * @param $folder_id
     * @param $role
     * @param int $user_id
     * @throws Exception
     */
    public function grantUserAccessToFolder($folder_id, $role, $user_id = 0) {
        $this->grantUsersAccessToFolder(array($user_id), $folder_id, $role);
    }

    /**
     * Grant multiple users viewer access to session.
     *
     * @param array $user_ids
     * @param $session_id
     * @throws Exception
     */
    public function grantUsersViewerAccessToSession(array $user_ids, $session_id) {
        $guids = array();
        foreach ($user_ids as $user_id) {
            $guids[] = $this->getUserGuid($user_id);
        }

        $this->log->write('*********');
        $this->log->write('SOAP call "GrantUsersViewerAccessToSession"');
        $this->log->write("sessionId:");
        $this->log->write(print_r($session_id, true));
        $this->log->write("userIds:");
        $this->log->write(print_r($guids, true));

        $params = new GrantUsersViewerAccessToSession(
            $this->auth,
            $session_id,
            $guids
        );

        /** @var AccessManagement $access_management */
        $access_management = $this->panoptoclient->AccessManagement();
        try {
            $access_management->GrantUsersViewerAccessToSession($params);
        } catch (Exception $e) {
            $this->logException($e, $access_management);
            throw $e;
        }

        $this->log->write('Status: ' . substr($access_management->__last_response_headers, 0, strpos($access_management->__last_response_headers, "\r\n")));

    }

    /**
     *
     * Grant single user viewer access to session. For current user, leave $user_id = 0
     *
     * @param $session_id
     * @param int $user_id
     * @throws Exception
     */
    public function grantUserViewerAccessToSession($session_id, $user_id = 0) {
        $this->grantUsersViewerAccessToSession(array($user_id), $session_id);
    }


    /**
     * @param      $folder_id
     * @param bool $page_limit Only returns a specific page if true, otherwise everything
     * @param int  $page
     *
     * @return mixed
     * @throws Exception
     */
    public function getSessionsOfFolder($folder_id, $page_limit = false, $page = 0)
    {
        $perpage = 10;
        $request = new ListSessionsRequest();
        $request->setFolderId($folder_id);

        $states = new ArrayOfSessionState();
        $states->setSessionState(array( SessionState::Complete));
        $request->setStates($states);

        $this->log->write('*********');
        $this->log->write('SOAP call "GetSessionsList"');
        $this->log->write("request:");
        $this->log->write(print_r($request, true));

        $params = new GetSessionsList(
            $this->auth,
            $request,
            ''
        );

        /** @var SessionManagement $session_client */
        $session_client = $this->panoptoclient->SessionManagement();
        try {
            $sessions_result = $session_client->GetSessionsList($params);
        } catch (Exception $e) {
            $this->logException($e, $session_client);
            throw $e;
        }

        $sessions = $sessions_result->getGetSessionsListResult();

        $this->log->write('Status: ' . substr($session_client->__last_response_headers, 0, strpos($session_client->__last_response_headers, "\r\n")));
        $this->log->write('Received ' . (int) count($sessions->getTotalNumberResults()) . ' object(s).');

        $array_sessions = array('count' => $sessions->getTotalNumberResults(), 'sessions' => $sessions->getResults()->getSession());
        $sorted_sessions = SorterEntry::generateSortedSessions($array_sessions);

        if ($page_limit) {
            // Implement manual pagination
            return array(
                "count"    => count($sorted_sessions["sessions"]),
                "sessions" => array_slice($sorted_sessions["sessions"], $page * $perpage, $perpage),
            );
        } else {
            return $sorted_sessions;
        }

    }

    /**
     * @param $folder_id
     * @return FolderAccessDetails
     * @throws Exception
     */
    public function getFolderAccessDetails($folder_id) {
        $this->log->write('*********');
        $this->log->write('SOAP call "GetFolderAccessDetails"');
        $this->log->write("folderId:");
        $this->log->write(print_r($folder_id, true));

        $params = new GetFolderAccessDetails(
            $this->auth,
            $folder_id
        );

        /** @var AccessManagement $access_management */
        $access_management = $this->panoptoclient->AccessManagement();
        try {
            $return = $access_management->GetFolderAccessDetails($params)->getGetFolderAccessDetailsResult();
        } catch (Exception $e) {
            $this->logException($e, $access_management);
            throw $e;
        }

        $this->log->write('Status: ' . substr($access_management->__last_response_headers, 0, strpos($access_management->__last_response_headers, "\r\n")));
        $this->log->write('Received ' . (int) count($return) . ' object(s).');

        return $return;
    }

    /**
     * @param $user_id
     * @return UserAccessDetails
     * @throws Exception
     */
    public function getUserAccessDetails($user_id = 0) {
        static $user_access_details;
        if (!isset($user_access_details[$user_id])) {
            $guid = $this->getUserGuid($user_id);
            $this->log->write('*********');
            $this->log->write('SOAP call "GetUserAccessDetails"');
            $this->log->write("userId:");
            $this->log->write(print_r($guid, true));

            $params = new GetUserAccessDetails(
                $this->auth,
                $guid
            );

            /** @var AccessManagement $access_management */
            $access_management = $this->panoptoclient->AccessManagement();
            try {
                $user_access_details[$user_id] = $access_management->GetUserAccessDetails($params)->getGetUserAccessDetailsResult();
            } catch (Exception $e) {
                $this->logException($e, $access_management);
                throw $e;
            }


            $this->log->write('Status: ' . substr($access_management->__last_response_headers, 0, strpos($access_management->__last_response_headers, "\r\n")));
            $this->log->write('Received ' . (int) count($user_access_details[$user_id]) . ' object(s).');
        }
        return $user_access_details[$user_id];
    }

    /**
     * @param $session_id
     * @return \Panopto\AccessManagement\SessionAccessDetails
     * @throws Exception
     */
    public function getSessionAccessDetails($session_id) {
        static $session_access_details;
        if (!isset($session_access_details[$session_id])) {
            $this->log->write('*********');
            $this->log->write('SOAP call "GetSessionAccessDetails"');
            $this->log->write("sessionId:");
            $this->log->write(print_r($session_id, true));

            $params = new GetSessionAccessDetails(
                $this->auth,
                $session_id
            );

            /** @var AccessManagement $access_management */
            $access_management = $this->panoptoclient->AccessManagement();
            try {
                $session_access_details[$session_id] = $access_management->GetSessionAccessDetails($params)->getGetSessionAccessDetailsResult();
            } catch (Exception $e) {
                $this->logException($e, $access_management);
                throw $e;
            }

            $this->log->write('Status: ' . substr($access_management->__last_response_headers, 0, strpos($access_management->__last_response_headers, "\r\n")));
            $this->log->write('Received ' . (int) count($session_access_details[$session_id]) . ' object(s).');
        }
        return $session_access_details[$session_id];
    }

    /**
     * @param $user_id
     * @throws Exception
     */
    public function syncExternalUser($user_id) {
        $this->log->write('*********');
        $this->log->write('SOAP call "SyncExternalUser"');
        $this->log->write("ilias user_id:");
        $this->log->write(print_r($user_id, true));

        $user = new ilObjUser($user_id);

        $params = new SyncExternalUser(
            $this->auth,
            $user->getFirstname(),
            $user->getLastname(),
            $user->getEmail(),
            false,
            array()
        );

        /** @var UserManagement $user_management */
        $user_management = $this->panoptoclient->UserManagement();
        try {
            $user_management->SyncExternalUser($params);
        } catch (Exception $e) {
            $this->logException($e, $user_management);
            throw $e;
        }

        $this->log->write('Status: ' . substr($user_management->__last_response_headers, 0, strpos($user_management->__last_response_headers, "\r\n")));
    }

    /**
     * @param $folder_id
     * @param int $user_id
     * @return bool|string Creator, Viewer or false
     * @throws Exception
     */
    public function getUserAccessOnFolder($folder_id, $user_id = 0) {
        $user_details = $this->getUserAccessDetails($user_id);
        $user_groups_details = $user_details->getGroupMembershipAccess()->getGroupAccessDetails();
        $user_groups_details = is_array($user_groups_details) ? $user_groups_details : array();

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
                $folders_with_viewer_access = array_merge($folders_with_viewer_access, $folder_ids);
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
     * @throws Exception
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
     * @throws Exception
     */
    public function hasUserViewerAccessOnSession($session_id, $user_id = 0) {
        $user_details = $this->getUserAccessDetails($user_id);
        $session_details = $this->getSessionAccessDetails($session_id);
        $folder_details = $session_details->getFolderAccess();

        $sessions_with_viewer_access = $user_details->getSessionsWithViewerAccess()->getGuid();
        $sessions_with_viewer_access = is_array($sessions_with_viewer_access) ? $sessions_with_viewer_access : array();

        $user_groups_details = $user_details->getGroupMembershipAccess()->getGroupAccessDetails();
        $user_groups_details = is_array($user_groups_details) ? $user_groups_details : array();
        foreach ($user_groups_details as $user_group_details) {
            $session_ids = $user_group_details->getSessionsWithViewerAccess();
            if (is_array($session_ids)) {
                $sessions_with_viewer_access = array_merge($sessions_with_viewer_access, $session_ids);
            }
        }

        if (
            $this->hasUserViewerAccessOnFolder($folder_details->getFolderId(), $user_id)
            || in_array($session_id, $sessions_with_viewer_access)
        ) {
            return true;
        }

        return false;
    }

    /**
     * @param $folder_id
     * @param int $user_id
     * @return bool
     * @throws Exception
     */
    public function hasUserViewerAccessOnFolder($folder_id, $user_id = 0) {
        return in_array($this->getUserAccessOnFolder($folder_id, $user_id), array(self::ROLE_VIEWER, self::ROLE_CREATOR, self::ROLE_PUBLISHER));
    }


    /**
     * @param $folder_id
     * @param int $user_id
     * @return bool
     * @throws Exception
     */
    public function hasUserCreatorAccessOnFolder($folder_id, $user_id = 0) {
        return in_array($this->getUserAccessOnFolder($folder_id, $user_id), array(self::ROLE_CREATOR));
    }

    /**
     * @param $e
     * @param $soap_client
     */
    protected function logException($e, $soap_client) {
        $this->log->write('ERROR');
        $this->log->write('Exception:');
        $this->log->write($e->getMessage() . " ({$e->getCode()})");
        $this->log->write($e->getTraceAsString());
        $this->log->write('Request:');
        $this->log->write($soap_client->__last_request);
        $this->log->write('Response:');
        $this->log->write($soap_client->__last_response);
        $this->log->write('*********');
    }


}