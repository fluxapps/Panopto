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
                "cmd" => ilObjPanoptoGUI::CMD_STANDARD,
                "default" => true,
            ]
        ];

        return $commands;
    }

    /**
     * @return string
     */
    function initType() {
        $this->setType(ilPanoptoPlugin::XPAN);
        return ilPanoptoPlugin::XPAN;
    }



    /**
     * get all alert properties
     *
     * @return array
     */
    public function getAlertProperties() {
        $alert = array();
        foreach ((array)$this->getCustomProperties(array()) as $prop) {
            if ($prop['alert'] == true) {
                $alert[] = $prop;
            }
        }

        return $alert;
    }

    /**
     * Get item properties
     *
     * @return    array        array of property arrays:
     *                        'alert' (boolean) => display as an alert property (usually in red)
     *                        'property' (string) => property name
     *                        'value' (string) => property value
     */
    public function getCustomProperties($a_prop) {
        $props = parent::getCustomProperties(array());

        $settings = xpanSettings::find($this->obj_id);
        if (!$settings->isOnline()) {
            $props[] = array(
                'alert' => true,
                'newline' => true,
                'property' => 'Status',
                'value' => 'Offline',
                'propertyNameVisible' => true
            );
        }

        return $props;
    }
}