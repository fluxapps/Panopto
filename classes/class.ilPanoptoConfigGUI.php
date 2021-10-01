<?php
require_once __DIR__ . '/../vendor/autoload.php';

/**
 * Class ilPanoptoConfigGUI
 *
 * @author Theodor Truffer <tt@studer-raimann.ch>
 */
class ilPanoptoConfigGUI extends ilPluginConfigGUI {

    const CMD_STANDARD = 'configure';
    const CMD_UPDATE = 'update';

    /**
     * @var ilTemplate
     */
    protected $tpl;
    /**
     * @var ilCtrl
     */
    protected $ctrl;
    /**
     * @var ilPanoptoPlugin
     */
    protected $pl;
    /**
     * @var ilToolbarGUI
     */
    protected $toolbar;
    /**
     * @var ilTabsGUI
     */
    protected $tabs;

    /**
     * ilPanoptoConfigGUI constructor.
     */
    public function __construct() {
        global $DIC;
        $tpl = $DIC['tpl'];
        $ilCtrl = $DIC['ilCtrl'];
        $ilToolbar = $DIC['ilToolbar'];
        $ilTabs = $DIC['ilTabs'];
        $this->toolbar = $ilToolbar;
        $this->tpl = $tpl;
        $this->ctrl = $ilCtrl;
        $this->pl = ilPanoptoPlugin::getInstance();
        $this->tabs = $ilTabs;
    }


    /**
     * @param $cmd
     */
    function performCommand($cmd) {
        switch ($cmd) {
            default:
                $this->{$cmd}();
                break;
        }
    }

    /**
     *
     */
    protected function configure() {
        $xpanConfFormGUI = new xpanConfigFormGUI($this);
        $xpanConfFormGUI->fillForm();
        $this->tpl->setContent($xpanConfFormGUI->getHTML());
    }


    /**
     *
     */
    protected function update() {
        $xpanConfFormGUI = new xpanConfigFormGUI($this);
        $xpanConfFormGUI->setValuesByPost();
        if ($xpanConfFormGUI->saveObject()) {
            ilUtil::sendSuccess($this->pl->txt('msg_success'), true);
            $this->ctrl->redirect($this, self::CMD_STANDARD);
        }
        $this->tpl->setContent($xpanConfFormGUI->getHTML());
    }


    /**
     * @param $lang_var
     * @return string
     */
    public function txt($lang_var) {
        return $this->pl->txt('conf_' . $lang_var);
    }
}
