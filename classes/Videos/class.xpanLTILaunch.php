<?php

use League\OAuth1\Client as OAuth1;

/**
 * Class xpanLTILaunch
 *
 * @author Theodor Truffer <tt@studer-raimann.ch>
 */
class xpanLTILaunch {

    public static function launch() {
        global $DIC;

        # Load config
        $launch_url = 'https://' . xpanConfig::getConfig(xpanConfig::F_HOSTNAME);
        $key = xpanConfig::getConfig(xpanConfig::F_INSTANCE_NAME);
        $secret = xpanConfig::getConfig(xpanConfig::F_APPLICATION_KEY);

        $launch_data = array(
            "user_id" => (xpanConfig::getConfig(xpanConfig::F_USER_ID) == xpanConfig::SUB_F_LOGIN) ? $DIC->user()->getLogin() : $DIC->user()->getExternalAccount(),
            "roles" => "Instructor",
            "resource_link_id" => $_GET['ref_id'],
            "resource_link_title" => 'ilias_object_' . $_GET['ref_id'],
//            "resource_link_title" => ilObjPanopto::_lookupTitle(ilObjPanopto::_lookupObjId($_GET['ref_id'])) . ' (ID: ' . $_GET['ref_id'] . ')',
            "lis_person_name_full" => $DIC->user()->getFullname(),
            "lis_person_name_family" => $DIC->user()->getLastname(),
            "lis_person_name_given" => $DIC->user()->getFirstname(),
            "lis_person_contact_email_primary" => $DIC->user()->getEmail(),
            "context_id" => $_GET['ref_id'],
            "context_title" => ilObjPanopto::_lookupTitle(ilObjPanopto::_lookupObjId($_GET['ref_id'])) . ' (ID: ' . $_GET['ref_id'] . ')',
            "context_label" => "urn:lti:context-type:ilias/Object_" . $_GET['ref_id'],
            "context_type" => "urn:lti:context-type:ilias/Object",
//            "launch_presentation_width" => 500,
//            "launch_presentation_height" => 300,
            'launch_presentation_locale' => 'de',
            'launch_presentation_document_target' => 'iframe',
        );

        #
        # END OF CONFIGURATION SECTION
        # ------------------------------

        $now = new DateTime();

        $launch_data["lti_version"] = "LTI-1p0";
        $launch_data["lti_message_type"] = "basic-lti-launch-request";


        # Basic LTI uses OAuth to sign requests
        # OAuth Core 1.0 spec: http://oauth.net/core/1.0/
        $launch_data["oauth_callback"] = "about:blank";
        $launch_data["oauth_consumer_key"] = $key;
        $launch_data["oauth_version"] = "1.0";
        $launch_data["oauth_nonce"] = uniqid('', true);
        $launch_data["oauth_timestamp"] = $now->getTimestamp();
        $launch_data["oauth_signature_method"] = "HMAC-SHA1";

        # In OAuth, request parameters must be sorted by name
        $launch_data_keys = array_keys($launch_data);
        sort($launch_data_keys);
        $launch_params = array();
        foreach ($launch_data_keys as $key) {
            array_push($launch_params, $key . "=" . rawurlencode($launch_data[$key]));
        }

        $credentials = new OAuth1\Credentials\ClientCredentials();
        $credentials->setIdentifier($key);
        $credentials->setSecret($secret);
//        $credentials->setCallbackUri('http://local.ilias52.com/Customizing/global/plugins/Services/Repository/RepositoryObject/Panopto/classes/bounce.php');

        ksort($launch_data);
        $signature = new OAuth1\Signature\HmacSha1Signature($credentials);
        $oauth_signature = $signature->sign($launch_url . '/Panopto/BasicLTI/BasicLTILanding.aspx', $launch_data, 'POST');
        $launch_data['oauth_signature'] = $oauth_signature;

        $html = '<form id="lti_form" action="' . $launch_url . '/Panopto/BasicLTI/BasicLTILanding.aspx" method="post" target="basicltiLaunchFrame" enctype="application/x-www-form-urlencoded">';

        foreach ($launch_data as $k => $v) {
            $html .= "<input type='hidden' name='$k' value='$v'>";
        }

        $html .= '</form>';
        $html .= '<iframe name="basicltiLaunchFrame"  id="basicltiLaunchFrame" src="" style="width:100%;height:100%;min-height:800px;border:none;"></iframe>';


        return $html;
    }
}