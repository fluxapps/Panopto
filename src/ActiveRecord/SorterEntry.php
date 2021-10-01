<?php

use srag\Plugins\Panopto\DTO\ContentObject;

require_once __DIR__ . "/../../vendor/autoload.php";

/**
 * Class SorterEntry
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 */
class SorterEntry extends ActiveRecord
{

    const TABLE_NAME = 'rep_robj_srtr_entry';
    const STATUS_NEW = 0;
    const STATUS_APPROVED = 1;
    const GENERIC_INCOME_ID = -1;
    const GENERIC_EXPENSES_ID = -2;


    /**
     * @return string
     * @description Return the Name of your Database Table
     * @deprecated
     */
    static function returnDbTableName()
    {
        return self::TABLE_NAME;
    }


    /**
     * @var int
     *
     * @con_is_primary  true
     * @con_is_unique   true
     * @con_has_field   true
     * @con_fieldtype   integer
     * @con_sequence    true
     * @con_length      8
     */
    protected $id;
    /**
     * @var int
     *
     * @con_has_field   true
     * @con_fieldtype   integer
     * @con_length      8
     */
    protected $ref_id;
    /**
     * @var int
     *
     * @con_has_field   true
     * @con_fieldtype   integer
     * @con_length      8
     */
    protected $precedence;
    /**
     * @var string
     *
     * @con_has_field   true
     * @con_fieldtype   text
     * @con_length      255
     */
    protected $session_id;


    /**
     * @return int
     */
    public function getId() : int
    {
        return $this->id;
    }


    /**
     * @param int $id
     */
    public function setId(int $id)
    {
        $this->id = $id;
    }


    /**
     * @return int
     */
    public function getRefId() : int
    {
        return $this->ref_id;
    }


    /**
     * @param int $ref_id
     */
    public function setRefId(int $ref_id)
    {
        $this->ref_id = $ref_id;
    }


    /**
     * @return int
     */
    public function getPrecedence() : int
    {
        return $this->precedence;
    }


    /**
     * @param int $precedence
     */
    public function setPrecedence(int $precedence)
    {
        $this->precedence = $precedence;
    }


    /**
     * @return string
     */
    public function getObjectId() : string
    {
        return $this->session_id;
    }


    /**
     * @param string $object_id
     */
    public function setObjectId(string $object_id)
    {
        $this->session_id = $object_id;
    }

    /**
     * @param ContentObject[] $objects
     * @param int   $ref_id
     * @return array
     * @throws Exception
     */
    public static function generateSortedObjects(array $objects, int $ref_id = 0) : array
    {
        $sorted = [];
        if (count($objects) > 0) {
            $entries = SorterEntry::orderBy("precedence");

            /* @var $entry SorterEntry */
            foreach (($ref_id > 0 ? $entries->where(['ref_id' => $ref_id])->get() : $entries->get()) as $entry) {
                $content_object = null;
                foreach ($objects as $object) {
                    if ($object->getId() === $entry->getObjectId()) {
                        $content_object = $object;
                    }
                }

                if (!is_null($content_object)) {
                    array_push($sorted, $content_object);
                }
            }

            $diff = array_udiff($objects, $sorted,
                function ($obj_a, $obj_b) {
                    return strcmp($obj_a->getId(), $obj_b->getId());
                }
            );

            foreach ($diff as $content_object) {
                array_push($sorted, $content_object);
            }
        }

        return $sorted;
    }
}
