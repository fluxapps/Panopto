<?php

use srag\Plugins\Panopto\DTO\RESTToken;

/**
 * Class xpanConfig
 *
 * @author Theodor Truffer <tt@studer-raimann.ch>
 */
class xpanConfig extends ActiveRecord {

    const DB_TABLE_NAME = 'xpan_config';

    const F_OBJECT_TITLE = 'object_title';
    const F_API_USER = 'api_user';
    const F_API_PASSWORD = 'api_password';
    const F_HOSTNAME = 'hostname';
    const F_INSTANCE_NAME = 'instance_name';
    const F_APPLICATION_KEY = 'application_key';
    const F_USER_ID = 'user_id';
    const F_REST_CLIENT_NAME = 'rest_client_name';
    const F_REST_CLIENT_SECRET = 'rest_client_secret';
    const F_REST_CLIENT_ID = 'rest_client_id';
    const F_REST_TOKEN = 'rest_token';
    const SUB_F_LOGIN = 'login';
    const SUB_F_EXT_ACCOUNT = 'external_account';
    const SUB_F_EMAIL = 'email';

    /**
     * @var array
     */
    protected static $cache = array();
    /**
     * @var array
     */
    protected static $cache_loaded = array();

    /**
     * @var string
     *
     * @db_has_field        true
     * @db_is_unique        true
     * @db_is_primary       true
     * @db_is_notnull       true
     * @db_fieldtype        text
     * @db_length           250
     */
    protected $name;
    /**
     * @var string
     *
     * @db_has_field        true
     * @db_fieldtype        text
     * @db_length           4000
     */
    protected $value;


    public static function returnDbTableName() {
        return self::DB_TABLE_NAME;
    }

    /**
     * @param $name
     *
     * @return mixed
     */
    public static function getConfig($name) {
        if (!self::$cache_loaded[$name]) {
            try {
                $obj = new self($name);
            } catch (Exception $e) {
                $obj = new self();
                $obj->setName($name);
            }
            self::$cache[$name] = json_decode($obj->getValue(), true);
            self::$cache_loaded[$name] = true;
        }

        return self::$cache[$name];
    }


    /**
     * @param $name
     * @param $value
     */
    public static function set($name, $value) {
        try {
            $obj = new self($name);
        } catch (Exception $e) {
            $obj = new self();
            $obj->setName($name);
        }
        $obj->setValue(json_encode($value));
        $obj->store();
    }

    /**
     * @param string $name
     */
    public function setName($name) {
        $this->name = $name;
    }


    /**
     * @return string
     */
    public function getName() {
        return $this->name;
    }


    /**
     * @param string $value
     */
    public function setValue($value) {
        $this->value = $value;
    }


    /**
     * @return string
     */
    public function getValue() {
        return $this->value;
    }

    public static function storeToken(RESTToken $token)
    {
        self::set(self::F_REST_TOKEN, $token->jsonSerialize());
    }

    /**
     * @return RESTToken|null
     */
    public static function getToken()
    {
        $serialized_token = self::getConfig(self::F_REST_TOKEN);
        return $serialized_token ? RESTToken::jsonUnserialize($serialized_token) : null;
    }
}
