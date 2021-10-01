<?php
namespace srag\Plugins\Panopto\DTO;

use stdClass;

/**
 * Class RESTToken
 * @author Theodor Truffer <tt@studer-raimann.ch>
 */
class RESTToken
{
    /**
     * @var string
     */
    private $access_token;
    /**
     * @var int
     */
    private $expiry;

    /**
     * RESTToken constructor.
     * @param string $access_token
     * @param int    $expiry
     */
    public function __construct(string $access_token, int $expiry)
    {
        $this->access_token = $access_token;
        $this->expiry = $expiry;
    }

    /**
     * @return string
     */
    public function getAccessToken() : string
    {
        return $this->access_token;
    }

    /**
     * @return int
     */
    public function getExpiry() : int
    {
        return $this->expiry;
    }

    public function isExpired() : bool
    {
        return time() > $this->expiry;
    }

    public function jsonSerialize() : string
    {
        $std_class = new stdClass();
        $std_class->access_token = $this->access_token;
        $std_class->expiry = $this->expiry;
        return json_encode($std_class);
    }

    public static function jsonUnserialize(string $json) : self
    {
        $decoded = json_decode($json);
        return new self($decoded->access_token, $decoded->expiry);
    }
}
