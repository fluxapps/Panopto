<?php
require_once __DIR__ . "/../vendor/autoload.php";

/**
 * Class ilObjPanoptoListGUI
 *
 * @author Theodor Truffer <tt@studer-raimann.ch>
 */
class ilObjPanoptoListGUI extends ilObjectPluginListGUI {

    function getGuiClass() {
        return ilObjPanoptoGUI::class;
    }

    function initCommands() {
        $this->timings_enabled = false;
        $this->subscribe_enabled = false;
        $this->payment_enabled = false;
        $this->link_enabled = false;
        $this->info_screen_enabled = true;
        $this->delete_enabled = true;
        $this->cut_enabled = false;
        $this->copy_enabled = false;

        $commands = [
            [
                "permission" => "read",
                "cmd" => ilObjPanoptoGUI::CMD_INDEX,
                "default" => true,
            ]
        ];

        return $commands;
    }

    function initType() {
        return ilPanoptoPlugin::XPAN;
    }
}