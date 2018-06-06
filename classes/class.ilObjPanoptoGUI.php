<?php
require_once __DIR__ . '/../vendor/autoload.php';

/**
 * Class ilObjPanoptoGUI
 *
 * @author Theodor Truffer <tt@studer-raimann.ch>
 */
class ilObjPanoptoGUI extends ilObjectPluginGUI {

    const CMD_INDEX = 'index';

    function getAfterCreationCmd() {
        // TODO: Implement getAfterCreationCmd() method.
    }

    function getStandardCmd() {
        // TODO: Implement getStandardCmd() method.
    }

    function getType() {
        return ilPanoptoPlugin::XPAN;
    }


}