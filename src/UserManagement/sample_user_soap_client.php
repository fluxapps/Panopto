<?php
/**
 * The user soap client for Panopto
 *
 * @package block_panopto
 * @copyright Panopto 2009 - 2016
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(__FILE__) . '/UserManagement/UserManagementAutoload.php');

class panopto_user_soap_client extends SoapClient {
    /**
     * @var array $authparam
     */
    private $authparam;

    /**
     * @var array $apiurl
     */
    private $apiurl;

    /**
     * main constructor
     *
     * @param string $servername
     * @param string $apiuseruserkey
     * @param string $apiuserauthcode
     */
    public function __construct($servername, $apiuseruserkey, $apiuserauthcode) {

        // Instantiate SoapClient in WSDL mode.
        // Set call timeout to 5 minutes.
        $this->apiurl = 'https://'. $servername . '/Panopto/PublicAPI/4.6/UserManagement.svc?wsdl';

        // Cache web service credentials for all calls requiring authentication.
        $this->authparam = new UserManagementStructAuthenticationInfo($apiuserauthcode, null, $apiuseruserkey);
    }

    /**
     * Syncs a user with all of the listed groups, the user will be removed from any unlisted groups
     *
     * @param string $firstname user first name
     * @param string $lastname user last name
     * @param string $email user email address
     * @param array $externalgroupids array of group ids the user needs to be in
     * @param boolean $sendemailnotifications whether user gets emails from Panopto updates
     */
    public function sync_external_user($firstname, $lastname, $email, $externalgroupids, $sendemailnotifications = false) {
        $usermanagementsync = new UserManagementServiceSync(array('wsdl_url' => $this->apiurl));
        $syncparamsobject = new UserManagementStructSyncExternalUser(
            $this->authparam,
            $firstname,
            $lastname,
            $email,
            $sendemailnotifications,
            $externalgroupids
        );

        // Returns false if the call failed.
        if (!$usermanagementsync->SyncExternalUser($syncparamsobject)) {
            error_log(print_r($usermanagementsync->getLastError(), true));
        }
    }
    
    /**
     * Tries to get a user by their username
     *
     * @param string $userkey the user name of the user we are trying to get.
     */
    public function get_user_by_key($userkey) {
        $result = false;
        $usermanagementserviceget = new UserManagementServiceGet(array('wsdl_url' => $this->apiurl));
        $getuserbykeyparams = new UserManagementStructGetUserByKey(
            $this->authparam,
            $userkey
        );

        // Returns false if the call failed.
        if ($usermanagementserviceget->GetUserByKey($getuserbykeyparams)) {
            $result = $usermanagementserviceget->getResult();
        } else {
            error_log(print_r($usermanagementserviceget->getLastError(), true));
        }

        return $result;
    }
    
    /**
     * Creates a user in panopto
     *
     * @param string $email - email for the new user
     * @param bool $emailsessionnotifications - should user receive email notifications.
     * @param string $firstname - first name of the user.
     * @param array<string> $groupmemberships - firstname of the user.
     * @param string $lastname - family name of the user.
     * @param string $systemrole - system role for the user to be given.
     * @param string $userbio - bio description for the user.
     * @param string $userid - guid for the new user.
     * @param string $userkey - user name for the new user.
     * @param string $usersettingsurl - url used to pass in optional settings.
     * @param string $password - optional initial password for the user.
     */
    public function create_user($email, $emailsessionnotifications, $firstname, $groupmemberships,
    		$lastname, $systemrole, $userbio, $userid, $userkey, $usersettingsurl, $password) {
    	$result = false;
    	$usermanagementcreate = new UserManagementServiceCreate(array('wsdl_url' => $this->apiurl));
    	$decoratedgroupmemberships = new UserManagementStructArrayOfguid($groupmemberships);
    	$userparamobject = new UserManagementStructUser(
    		$email,
    		$emailsessionnotifications,
    		$firstname,
    		$decoratedgroupmemberships,
    		$lastname,
    		$systemrole,
    		$userbio,
    		$userid,
    		$userkey,
    		$usersettingsurl
    	);
    			
    	$createuserparams = new UserManagementStructCreateUser(
    		$this->authparam,
    		$userparamobject,
    		$password
    	);
    			
    	// Returns false if the call failed.
    	if ($usermanagementcreate->CreateUser($createuserparams)) {
    		$result = $usermanagementcreate->getResult();
    	} else {
    		error_log(print_r($usermanagementcreate->getLastError(), true));
    	}
    			
    	return $result;
    }
    
    /**
     * Deletes users in panopto, found by list of user id
     *
     * @param string (or array<string>) $userids - guid for the new user.
     */
	public function delete_users($userids) {
		$result = false;
		$usermanagementdelete = new UserManagementServiceDelete(array('wsdl_url' => $this->apiurl));
		
		if (!is_array($userids)) {
			$userids= array($userids);
		}
		
		$deleteuserparams = new UserManagementStructDeleteUsers(
			$this->authparam,
			$userids
		);
		
		if ($usermanagementdelete->DeleteUsers($deleteuserparams)) {
			$result = $usermanagementdelete->getResult();
		} else {
			error_log(print_r($usermanagementdelete->getLastError(), true));
		}
		
		return $result;
	}
}

/* End of file panopto_user_soap_client.php */
