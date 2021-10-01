<?php

/**
 * Class xpanSettings
 *
 * @author Theodor Truffer <tt@studer-raimann.ch>
 */
class xpanSettings extends ActiveRecord {

    const DB_TABLE_NAME = 'xpan_settings';

    /**
     * @return string
     */
    public function getConnectorContainerName() {
        return self::DB_TABLE_NAME;
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
     * @var int
     *
     * @db_has_field        true
     * @db_fieldtype        integer
     * @db_length           8
     */
    protected $folder_ext_id;

    /**
     * @return int
     */
    public function getObjId() : int
    {
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
    public function isOnline() : int
    {
        return $this->is_online;
    }

    /**
     * @param int $is_online
     */
    public function setIsOnline($is_online) {
        $this->is_online = $is_online;
    }

    /**
     * @return int|null
     */
    public function getFolderExtId()
    {
        return $this->folder_ext_id;
    }

    /**
     * @param int $folder_ext_id
     */
    public function setFolderExtId(int $folder_ext_id)
    {
        $this->folder_ext_id = $folder_ext_id;
    }
}
