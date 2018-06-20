<?php

/**
 * The auth soap client for Panopto
 *
 * @package block_panopto
 * @copyright Panopto 2009 - 2017
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(__FILE__) . '/AuthManagement/AuthManagementAutoload.php');

class panopto_auth_soap_client extends SoapClient {


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
        $this->apiurl = $servername . '/Panopto/PublicAPI/4.2/Auth.svc?wsdl';

        // Cache web service credentials for all calls requiring authentication.
        $this->authparam = new AuthManagementStructAuthenticationInfo($apiuserauthcode, null, $apiuseruserkey);
    }

    /**
     * gets the version of the server.
     */
    public function get_server_version() {
        $returnvalue = false;
        $authmanagementserviceget = new AuthManagementServiceGet(array('wsdl_url' => $this->apiurl));
        if ($authmanagementserviceget->GetServerVersion()) {
            $returnvalue = $authmanagementserviceget->getResult()->GetServerVersionResult;
        } else {
            error_log(print_r($authmanagementserviceget->getLastError(), true));
        }
        return $returnvalue;
    }
}
/* End of file panopto_auth_soap_client.php */
