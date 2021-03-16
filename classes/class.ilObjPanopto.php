<?php
require_once __DIR__ . '/../vendor/autoload.php';

/**
 * Class ilObjPanopto
 *
 * @author Theodor Truffer <tt@studer-raimann.ch>
 */
class ilObjPanopto extends ilObjectPlugin {

    protected function initType()
    {
        $this->setType(ilPanoptoPlugin::XPAN);
    }

    protected function doCreate()
    {
        $settings = new xpanSettings();
        $settings->setObjId($this->getId());
        $settings->create();
    }

    public function getFolderExtId() : int
    {
        return $this->getSettings()->getFolderExtId() ?: $this->getReferenceId();
    }

    public function getSettings() : xpanSettings
    {
        return xpanSettings::find($this->getId());
    }

    /**
     * get ref_id, but load if not yet loaded
     *
     * @return int
     */
    public function getReferenceId() : int
    {
        return $this->getRefId() ?: self::_getAllReferences($this->getId())[0];
    }

    /**
     * @param      $new_obj ilObjPanopto
     * @param      $a_target_id
     * @param null $a_copy_id
     */
    protected function doCloneObject($new_obj, $a_target_id, $a_copy_id = null)
    {
        $settings = $new_obj->getSettings();
        $settings->setFolderExtId($this->getFolderExtId());
        $settings->update();
    }

}
