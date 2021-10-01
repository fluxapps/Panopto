<?php
namespace srag\Plugins\Panopto\DTO;

/**
 * Class Session
 * @author Theodor Truffer <tt@studer-raimann.ch>
 */
class Session extends ContentObject
{
    /**
     * @var int
     */
    protected $duration;

    /**
     * Session constructor.
     * @param string $id
     * @param string $title
     * @param string $description
     * @param string $thumbnail_url
     * @param int    $duration
     */
    public function __construct(string $id, string $title, string $description, string $thumbnail_url, int $duration)
    {
        $this->duration = $duration;
        parent::__construct($id, $title, $description, $thumbnail_url);
    }

    /**
     * @return int
     */
    public function getDuration() : int
    {
        return $this->duration;
    }


}
