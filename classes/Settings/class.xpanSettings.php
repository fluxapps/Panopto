<?php

/**
 * Class xpanSettings
 *
 * @author Theodor Truffer <tt@studer-raimann.ch>
 */
class xpanSettings extends ACtiveRecord {

    const DB_TABLE = 'xpan_settings';

    /**
     * @return string
     */
    public function getConnectorContainerName() {
        return self::DB_TABLE;
    }

    /**
     * @var int
     *
     * @db_has_field        true
     * @db_is_unique        true
     * @db_is_primary       true
     * @db_fieldtype        integer
     * @db_length           8
     */
    protected $obj_id;
    /**
     * @var int
     *
     * @db_has_field        true
     * @db_fieldtype        integer
     * @db_length           1
     */
    protected $is_online = 0;

    /**
     * @return int
     */
    public function getObjId() {
        return $this->obj_id;
    }

    /**
     * @param int $obj_id
     */
    public function setObjId($obj_id) {
        $this->obj_id = $obj_id;
    }

    /**
     * @return int
     */
    public function isOnline() {
        return $this->is_online;
    }

    /**
     * @param int $is_online
     */
    public function setIsOnline($is_online) {
        $this->is_online = $is_online;
    }



}