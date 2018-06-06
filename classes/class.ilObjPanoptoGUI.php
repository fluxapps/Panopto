<?php
require_once __DIR__ . '/../vendor/autoload.php';

/**
 * Class ilObjPanoptoGUI
 *
 * @author Theodor Truffer <tt@studer-raimann.ch>
 *
 * @ilCtrl_isCalledBy ilObjUdfEditorGUI: ilRepositoryGUI, ilObjPluginDispatchGUI, ilAdministrationGUI
 * @ilCtrl_Calls      ilObjUdfEditorGUI: ilPermissionGUI, ilInfoScreenGUI, ilCommonActionDispatcherGUI
 *
 */
class ilObjPanoptoGUI extends ilObjectPluginGUI {

    const CMD_STANDARD = 'index';

    function getAfterCreationCmd() {
        // TODO: Implement getAfterCreationCmd() method.
    }

    function getStandardCmd() {
        return self::CMD_STANDARD;
    }

    function getType() {
        return ilPanoptoPlugin::XPAN;
    }


}