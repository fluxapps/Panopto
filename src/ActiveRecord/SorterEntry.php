<?php
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
    public function getSessionId() : string
    {
        return $this->session_id;
    }


    /**
     * @param string $session_id
     */
    public function setSessionId(string $session_id)
    {
        $this->session_id = $session_id;
    }


    /**
     * @param array $sessions
     *
     * @return array
     */
    public static function generateSortedSessions($sessions)
    {
        $sorted = [
            "count"    => $sessions["count"],
            "sessions" => [],
        ];
        $entries = SorterEntry::orderBy("precedence");
        //die(var_dump($entries->get()));

        /* @var $entry SorterEntry */
        foreach ($entries->get() as $entry) {
            $session = null;
            foreach ($sessions["sessions"] as $sessionEntry) {
                if ($sessionEntry->getId() === $entry->getSessionId()) {
                    $session = $sessionEntry;
                }
            }

            if (!is_null($session)) {
                array_push($sorted["sessions"], $session);
            }
        }

        // Append sessions that haven't been sorted yet
        $diff = array_udiff($sessions["sessions"], $sorted["sessions"],
            function ($obj_a, $obj_b) {

                return $obj_a->getId() - $obj_b->getId();
            }
        );
        foreach ($diff as $session) {
            array_push($sorted["sessions"], $session);
        }

        return $sorted;
    }
}