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
        $this->base_url = 'https://' . rtrim(ltrim(xpanConfig::getConfig(xpanConfig::F_HOSTNAME), "https://"), '/');
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
     */
    public function getPlaylistsOfFolder(string $folder_id) : array
    {
        $url = $this->base_url . '/Panopto/api/v1/folders/' . $folder_id . '/playlists';
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $this->token->getAccessToken()]);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $response = json_decode(curl_exec($curl), true);
        // TODO: error handling
        return ContentObjectBuilder::buildPlaylistDTOsFromArray($response["Results"]);
    }

    public function getSessionsOfPlaylist(string $playlist_id) : array
    {
        $url = $this->base_url . '/Panopto/api/v1/playlists/' . $playlist_id . '/sessions';
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $this->token->getAccessToken()]);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $response = json_decode(curl_exec($curl), true);
        return ContentObjectBuilder::buildSessionDTOsFromArray($response['Results']);
    }
}
