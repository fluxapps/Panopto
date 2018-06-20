<?php
/**
 * The user soap client for Panopto
 *
 * @copyright Panopto 2009 - 2016
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(__FILE__) . '/SessionManagement/SessionManagementAutoload.php');

class panopto_session_soap_client extends SoapClient {
    /**
     * @var array $authparam auth param needed for all soap calls.
     */
    private $authparam;

    /**
     * @var array $apiurl url used by the soap wsdl.
     */
    private $apiurl;

    /**
     * @var SessionManagementServiceAdd $sessionmanagementserviceadd soap service for add based calls
     */
    private $sessionmanagementserviceadd;

    /**
     * @var SessionManagementServiceProvision $sessionmanagementserviceprovision soap service for provision based calls
     */
    private $sessionmanagementserviceprovision;

    /**
     * @var SessionManagementServiceSet $sessionmanagementserviceset soap service for set calls
     */
    private $sessionmanagementserviceset;

    /**
     * @var SessionManagementServiceGet $sessionmanagementserviceget soap service for get calls
     */
    private $sessionmanagementserviceget;

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
        $this->apiurl =  $servername . '/Panopto/PublicAPI/4.6/SessionManagement.svc?wsdl';

        // Cache web service credentials for all calls requiring authentication.
        $this->authparam = new SessionManagementStructAuthenticationInfo(
            $apiuserauthcode,
            null,
            $apiuseruserkey
        );
    }
    
    /**
     * Creates a folder in panopto
     *
     * @param string $foldername The name of the folder we want to create
     * @param string $parentguid The guid of the parent of the new folder. null for a root level folder
     * @param string $ispublic is this a public folder?
     */
    public function add_folder($foldername, $parentguid = null, $ispublic = false) {
    	$ret = false;
    	
    	if (!isset($this->sessionmanagementserviceadd)) {
    		$this->sessionmanagementserviceadd = new SessionManagementServiceAdd(
    			array('wsdl_url' => $this->apiurl)
    		);
    	}
    	
    	$folderparams = new SessionManagementStructAddFolder(
    			$this->authparam,
    			$foldername,
    			$parentguid,
    			$ispublic
    	);
    	
    	if ($this->sessionmanagementserviceadd->AddFolder($folderparams)) {
    		$ret = $this->sessionmanagementserviceadd->getResult();
    	} else {
    		error_log(print_r($this->sessionmanagementserviceadd->getLastError(), true));
    	}
    	
    	return $ret;
    }
    
    /**
     * Delete a list of passed in folders by their id..
     *
     * @param array<string> $folderid the ids of the folders we want to delete
     */
    public function delete_folders($folderids) {
    	$ret = false;
    	
    	if (!isset($this->sessionmanagementservicedelete)) {
    		$this->sessionmanagementservicedelete = new SessionManagementServiceDelete(
    			array('wsdl_url' => $this->apiurl)
    		);
    	}
    	
    	if (!is_array($folderids)) {
    		$folderids = array($folderids);
    	}
    	
    	$folderparams = new SessionManagementStructDeleteFolders(
    			$this->authparam,
    			$folderids
    	);
    	
    	if ($this->sessionmanagementservicedelete->DeleteFolders($folderparams)) {
    		$ret = $this->sessionmanagementservicedelete->getResult();
    	} else {
    		error_log(print_r($this->sessionmanagementservicedelete->getLastError(), true));
    	}
    	
    	return $ret;
    }
    
    /**
     * Provisions a course and makes access groups for the specified roles pass into the call.
     *
     * @param string $fullname the name of the folder we are provisioning.
     * @param string $externalcourseid the external id of the folder we are provisioning.
     * @param string rolestoensure roles we want to make sure get permissions in the target course.
     *
     */
    public function provision_external_course_with_roles($fullname, $externalcourseid, $roletoensure = null) {
        $ret = false;

        if (!isset($this->sessionmanagementserviceprovision)) {
            $this->sessionmanagementserviceprovision = new SessionManagementServiceProvision(
                array('wsdl_url' => $this->apiurl)
            );
        }
        
        // By default lets just add all 3 possible roles.
        if (!isset($rolestoensure) || empty($rolestoensure)) {
        	$rolestoensure = array(
        			"Viewer",
        			"Creator",
        			"Publisher"
        	);
        }
        
        $rolelist = new SessionManagementStructArrayOfAccessRole($rolestoensure);

        $provisionparams = new SessionManagementStructProvisionExternalCourseWithRoles(
            $this->authparam,
            $fullname,
            $externalcourseid,
            $rolelist
        );

        if ($this->sessionmanagementserviceprovision->ProvisionExternalCourseWithRoles($provisionparams)) {
            $retobj = $this->sessionmanagementserviceprovision->getResult();
            $ret = $retobj->ProvisionExternalCourseWithRolesResult;
        } else {
            error_log(print_r($this->sessionmanagementserviceprovision->getLastError(), true));
        }

        return $ret;
    }
    
    /**
     * Gives a course access groups for a list of roles to a target copy or imported course.
     *
     * @param string $fullname the name of the folder being given copy permissions
     * @param string $externalcourseid the external id of the folder being given copy permissions
     * @param string folderids the session group ids of the folder we want to share.
     * @param string rolestoensure roles we want to make sure get permissions in the target course.
     *
     */
    public function set_external_course_access_for_roles($fullname, $externalcourseid, $folderids, $rolestoensure = null) {
        $ret = false;

        if (!isset($this->sessionmanagementserviceset)) {
            $this->sessionmanagementserviceset = new SessionManagementServiceSet(
                array('wsdl_url' => $this->apiurl)
            );
        }

        if (!is_array($folderids)) {
            $folderids = array($folderids);
        }

        $folderidlist = new SessionManagementStructArrayOfguid($folderids);

        // By default lets just add all 3 possible roles.
        if (!isset($rolestoensure) || empty($rolestoensure)) {
	        $rolestoensure = array(
	            "Viewer",
	            "Creator",
	            "Publisher"
	        );
        }
        
        $rolelist = new SessionManagementStructArrayOfAccessRole($rolestoensure);

        $courseaccessparams = new SessionManagementStructSetExternalCourseAccessForRoles(
            $this->authparam,
            $fullname,
            $externalcourseid,
            $folderidlist,
            $rolelist
        );

        if ($this->sessionmanagementserviceset->SetExternalCourseAccessForRoles($courseaccessparams)) {
            $retobj = $this->sessionmanagementserviceset->getResult();
            // We do not support multiple folders per course in Moodle atm so we can assume 1 result.
            $ret = $retobj->SetExternalCourseAccessForRolesResult->Folder[0];
        } else {
            error_log(print_r($this->sessionmanagementserviceset->getLastError(), true));
        }

        return $ret;
    }
    
    /**
     * Gives a course viewer access groups for the specified roles to a target copy or imported course.
     *
     * @param string $fullname the name of the folder being given copy permissions
     * @param string $externalcourseid the external id of the folder being given copy permissions
     * @param string folderids the session group ids of the folder we want to share.
     * @param string rolestoensure roles we want to make sure get viewer permissions in the target course.
     * 
     */
    public function set_copied_external_course_access_for_roles($fullname, $externalcourseid, $folderids, $rolestoensure = null) {
        $ret = false;

        if (!isset($this->sessionmanagementserviceset)) {
            $this->sessionmanagementserviceset = new SessionManagementServiceSet(
                array('wsdl_url' => $this->apiurl)
            );
        }

        if (!is_array($folderids)) {
            $folderids = array($folderids);
        }

        $folderidlist = new SessionManagementStructArrayOfguid($folderids);

        
        // By default lets just add all 3 possible roles.
        if (!isset($rolestoensure) || empty($rolestoensure)) {
        	$rolestoensure = array(
        			"Viewer",
        			"Creator",
        			"Publisher"
        	);
        }
        
        $rolelist = new SessionManagementStructArrayOfAccessRole($rolestoensure);

        $copiedaccessparams = new SessionManagementStructSetCopiedExternalCourseAccessForRoles(
            $this->authparam,
            $fullname,
            $externalcourseid,
            $folderidlist,
            $rolelist
        );

        if ($this->sessionmanagementserviceset->SetCopiedExternalCourseAccessForRoles($copiedaccessparams)) {
            $retobj = $this->sessionmanagementserviceset->getResult();
            $ret = $retobj->SetCopiedExternalCourseAccessForRolesResult->Folder[0];
        } else {
            error_log(print_r($this->sessionmanagementserviceset->getLastError(), true));
        }

        return $ret;
    }
    
    /**
     * Get a list of folders by their session group folder Id
     *
     * @param array<string> $folderids a list of session group ids for the folders we want to grab
     */
    public function get_folders_by_id($folderids) {
        $ret = false;
        if (!isset($this->sessionmanagementserviceget)) {
            $this->sessionmanagementserviceget = new SessionManagementServiceGet(
                array('wsdl_url' => $this->apiurl)
            );
        }

        if (!is_array($folderids)) {
            $folderids = array($folderids);
        }

        $folderidlist = new SessionManagementStructArrayOfguid($folderids);
        $getfolderparams = new SessionManagementStructGetFoldersById($this->authparam, $folderidlist);

        if ($this->sessionmanagementserviceget->GetFoldersById($getfolderparams)) {
            $retobj = $this->sessionmanagementserviceget->getResult();
            $ret = $retobj->GetFoldersByIdResult->Folder[0];
        } else {
            $lasterror = $this->sessionmanagementserviceget->getLastError()['SessionManagementServiceGet::GetFoldersById'];

            // Parsing error message for not found.
            if (strpos($lasterror->getMessage(), 'not found') !== false) {
                // Making ret -1 since folder was not found.
                $ret = -1;
            }

            error_log(print_r($lasterror, true));
        }

        return $ret;
    }

    /**
     * Get a list of folders by their external folder Id
     *
     * @param array<string> $folderids a list of ids for the folders we want to grab
     */
    public function get_folders_by_external_id($folderids) {
        $ret = false;

        if (!isset($this->sessionmanagementserviceget)) {
            $this->sessionmanagementserviceget = new SessionManagementServiceGet(
                array('wsdl_url' => $this->apiurl)
            );
        }

        if (!is_array($folderids)) {
            $folderids = array($folderids);
        }

        $folderidlist = new SessionManagementStructArrayOfstring($folderids);

        $getfolderparams = new SessionManagementStructGetFoldersByExternalId(
            $this->authparam,
            $folderidlist
        );

        if ($this->sessionmanagementserviceget->GetFoldersByExternalId()) {
            $retobj = $this->sessionmanagementserviceget->getResult();
            $ret = $retobj->GetFoldersByExternalIdResult->Folder[0];
        } else {
            error_log(print_r($this->sessionmanagementserviceget->getLastError(), true));
        }

        return $ret;
    }
    
    /**
     * Get a list of folders the user has access to.
     * Since we can only grab up to 1k at a time if there are more than 1k folders loop until we get them all.
     */
    public function get_folders_list() {
        $result = false;

        if (!isset($this->sessionmanagementserviceget)) {
            $this->sessionmanagementserviceget = new SessionManagementServiceGet(
                array('wsdl_url' => $this->apiurl)
            );
        }

        $resultsperpage = 1000;
        $currentpage = 0;
        $pagination = new SessionManagementStructPagination($resultsperpage, $currentpage);
        $parentfolderid = null;
        $publiconly = false;
        $sortby = SessionManagementEnumFolderSortField::VALUE_NAME;
        $sortincreasing = true;
        $wildcardsearchnameonly = false;

        $folderlistrequest = new SessionManagementStructListFoldersRequest(
            $pagination,
            $parentfolderid,
            $publiconly,
            $sortby,
            $sortincreasing,
            $wildcardsearchnameonly
        );
        $searchquery = null;

        $folderlistparams = new SessionManagementStructGetFoldersList(
            $this->authparam,
            $folderlistrequest,
            $searchquery
        );

        if ($this->sessionmanagementserviceget->GetFoldersList($folderlistparams)) {
            $retobj = $this->sessionmanagementserviceget->getResult();
            $totalresults = $retobj->GetFoldersListResult->TotalNumberResults;

            $folderlist = $retobj->GetFoldersListResult->Results->Folder;

            if ($totalresults > $resultsperpage) {

                $folderstoget = $totalresults - $resultsperpage;
                ++$currentpage;
                while ($folderstoget > 0) {
                    $pagination = new SessionManagementStructPagination($resultsperpage, $currentpage);

                    $folderlistrequest = new SessionManagementStructListFoldersRequest(
                        $pagination,
                        $parentfolderid,
                        $publiconly,
                        $sortby,
                        $sortincreasing,
                        $wildcardsearchnameonly
                    );

                    $folderlistparams = new SessionManagementStructGetFoldersList(
                        $this->authparam,
                        $folderlistrequest,
                        $searchquery
                    );

                    if ($this->sessionmanagementserviceget->GetFoldersList($folderlistparams)) {
                        $retobj = $this->sessionmanagementserviceget->getResult();
                        $folderlist = array_merge($folderlist, $retobj->GetFoldersListResult->Results->Folder);
                    } else {
                        error_log(print_r($this->sessionmanagementserviceget->getLastError(), true));
                        break;
                    }

                    ++$currentpage;
                    $folderstoget -= $resultsperpage;
                }
            }

            $result = $folderlist;
        } else {
            error_log(print_r($this->sessionmanagementserviceget->getLastError(), true));
        }

        return $result;
    }

    /**
     * Get a folder by it's public Id
     * 
     * @param string folderid
     */
    public function get_session_list($folderid) {
        $ret = false;

        if (!isset($this->sessionmanagementserviceget)) {
            $this->sessionmanagementserviceget = new SessionManagementServiceGet(
                array('wsdl_url' => $this->apiurl)
            );
        }

        $startdate = null;
        $enddate = null;
        $pagination = new SessionManagementStructPagination(100, 0);
        $remoterecorderid = null;
        $sortby = SessionManagementEnumSessionSortField::VALUE_DATE;
        $sortincreasing = true;
        $states = new SessionManagementStructArrayOfSessionState(
            array(
                SessionManagementEnumSessionState::VALUE_BROADCASTING,
                SessionManagementEnumSessionState::VALUE_COMPLETE,
                SessionManagementEnumSessionState::VALUE_RECORDING
            )
        );

        $sessionrequest = new SessionManagementStructListSessionsRequest(
            $enddate,
            $folderid,
            $pagination,
            $remoterecorderid,
            $sortby,
            $sortincreasing,
            $startdate,
            $states
        );
        $searchquery = null;

        $getsessionlistparams = new SessionManagementStructGetSessionsList(
            $this->authparam,
            $sessionrequest,
            $searchquery
        );

        if ($this->sessionmanagementserviceget->GetSessionsList($getsessionlistparams)) {
            $ret = $this->sessionmanagementserviceget->getResult()->GetSessionsListResult->Results->Session;
        } else {
            error_log(print_r($this->sessionmanagementserviceget->getLastError(), true));
        }

        return $ret;
    }
	
    
    /**
     * Return a list of OS specific recorder download links.
     */
    public function get_recorder_download_urls() {
        $ret = false;

        if (!isset($this->sessionmanagementserviceget)) {
            $this->sessionmanagementserviceget = new SessionManagementServiceGet(
                array('wsdl_url' => $this->apiurl)
            );
        }

        if ($this->sessionmanagementserviceget->GetRecorderDownloadUrls()) {
            $ret = $this->sessionmanagementserviceget->getResult()->GetRecorderDownloadUrlsResult;
        } else {
            error_log(print_r($this->sessionmanagementserviceget->getLastError(), true));
        }

        return $ret;
    }
}

/* End of file panopto_user_soap_client.php */
