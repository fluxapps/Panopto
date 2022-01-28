<?php

use League\OAuth2\Client\Token\AccessTokenInterface;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Provider\GenericProvider as OAuth2Provider;
use srag\Plugins\Panopto\DTO\RESTToken;
use srag\Plugins\Panopto\DTO\Playlist;
use srag\Plugins\Panopto\DTO\ContentObjectBuilder;

/**
 * Class xpanRESTClient
 * @author Theodor Truffer <tt@studer-raimann.ch>
 */
class xpanRESTClient
{
    /**
     * @var xpanRESTClient
     */
    protected static $instance;
    /**
     * @var xpanLog
     */
    private $log;
    /**
     * @var string
     */
    private $base_url;
    /**
     * @var OAuth2Provider
     */
    private $oauth2_provider;
    /**
     * @var RESTToken
     */
    private $token;

    /**
     * @return xpanRESTClient
     */
    public static function getInstance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * xpanRESTClient constructor.
     */
    public function __construct()
    {
        $this->log = xpanLog::getInstance();
        $host = xpanConfig::getConfig(xpanConfig::F_HOSTNAME);
        if (strpos($host, 'https://') === 0) {
            $host = substr($host, 8);
        }
        $this->base_url = 'https://' . rtrim($host, '/');
        $this->oauth2_provider = new OAuth2Provider(array(
            'clientId' => xpanConfig::getConfig(xpanConfig::F_REST_CLIENT_ID),
            'clientSecret' => xpanConfig::getConfig(xpanConfig::F_REST_CLIENT_SECRET),
            'urlAccessToken' => $this->base_url . '/Panopto/oauth2/connect/token',
            'urlAuthorize' => '',
            'urlResourceOwnerDetails' => ''
        ));
        $this->loadToken();
    }

    private function loadToken()
    {
        $token = xpanConfig::getToken();
        if (!$token || $token->isExpired()) {
            $this->log('fetch access token');
            $oauth2_token = $this->oauth2_provider->getAccessToken("password", [
                "username" => xpanConfig::getConfig(xpanConfig::F_REST_API_USER),
                "password" => xpanConfig::getConfig(xpanConfig::F_REST_API_PASSWORD),
                "scope" => "api"
            ]);
            $token = new RESTToken($oauth2_token->getToken(), $oauth2_token->getExpires());
            xpanConfig::storeToken($token);
        }
        $this->token = $token;
    }

    /**
     * @param string $folder_id
     * @return Playlist[]
     * @throws ilException
     */
    public function getPlaylistsOfFolder(string $folder_id) : array
    {
        $response = $this->get('/Panopto/api/v1/folders/' . $folder_id . '/playlists');
        return ContentObjectBuilder::buildPlaylistDTOsFromArray($response["Results"]);
    }

    /**
     * @param string $playlist_id
     * @return array
     * @throws ilException
     */
    public function getSessionsOfPlaylist(string $playlist_id) : array
    {
        $response = $this->get('/Panopto/api/v1/playlists/' . $playlist_id . '/sessions');
        return ContentObjectBuilder::buildSessionDTOsFromArray($response['Results']);
    }

    /**
     * @param string $playlist_id
     * @return string
     * @throws ilException
     */
    public function getFolderIdOfPlaylist(string $playlist_id) : string
    {
        $response = $this->get('/Panopto/api/v1/playlists/' . $playlist_id);
        if (!isset($response['Folder']['Id'])) {
            throw new ilException('Panopto REST: could not fetch folder id of playlist ' . $playlist_id);
        }
        return $response['Folder']['Id'];
    }

    /**
     * @param string $relative_url
     * @return array
     * @throws ilException
     */
    private function get(string $relative_url) : array
    {
        $this->log('GET ' . $relative_url);
        $url = $this->base_url . $relative_url;
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $this->token->getAccessToken()]);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($curl);
        if ($response === false) {
            throw new ilException('Panopto REST: curl error nr: ' . curl_errno($curl) . ', message: ' . curl_error($curl));
        }
        $http_status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        if ($http_status >= 300) {
            $message = is_string($response) ? $response : (is_array($response) ? print_r($response, true) : '');
            throw new ilException('Panopto REST: error response from Panopto server, status ' . $http_status . ', message: ' . $message);
        }
        return json_decode($response, true);
    }

    private function log(string $message)
    {
        $this->log->write('Panopto REST Client: ' . $message);
    }
}
