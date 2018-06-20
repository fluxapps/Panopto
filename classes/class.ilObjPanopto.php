<?php
require_once __DIR__ . '/../vendor/autoload.php';

/**
 * Class ilObjPanopto
 *
 * @author Theodor Truffer <tt@studer-raimann.ch>
 */
class ilObjPanopto extends ilObjectPlugin {

    /**
     *
     */
    protected function initType() {
        $this->setType(ilPanoptoPlugin::XPAN);
    }

    /**
     *
     */
    protected function doCreate() {
        $settings = new xpanSettings();
        $settings->setObjId($this->getId());
        $settings->create();
    }


}