<?php
namespace srag\Plugins\Panopto\DTO;

/**
 * Class ContentObject
 * @author Theodor Truffer <tt@studer-raimann.ch>
 */
class ContentObject
{
    /**
     * @var string
     */
    protected $id;
    /**
     * @var string
     */
    protected $title;
    /**
     * @var string
     */
    protected $description;
    /**
     * @var string
     */
    protected $thumbnail_url;

    /**
     * ContentObject constructor.
     * @param string $id
     * @param string $title
     * @param string $description
     * @param string $thumbnail_url
     */
    public function __construct(string $id, string $title, string $description, string $thumbnail_url)
    {
        $this->id = $id;
        $this->title = $title;
        $this->thumbnail_url = $thumbnail_url;
        $this->description = $description;
    }

    /**
     * @return string
     */
    public function getId() : string
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getTitle() : string
    {
        return $this->title;
    }

    /**
     * @return string
     */
    public function getDescription() : string
    {
        return $this->description;
    }

    /**
     * @return string
     */
    public function getThumbnailUrl() : string
    {
        return $this->thumbnail_url;
    }


}
