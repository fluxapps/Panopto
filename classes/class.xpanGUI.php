<?php

/**
 * Class xpanGUI
 *
 * @author Theodor Truffer <tt@studer-raimann.ch>
 */
abstract class xpanGUI {

    const CMD_STANDARD = 'index';

    /**
     * @var ilCtrl
     */
    protected $ctrl;
    /**
     * @var ilObjUser
     */
    protected $user;
    /**
     * @var ilLanguage
     */
    protected $lng;
    /**
     * @var ilTemplate
     */
    protected $tpl;
    /**
     * @var ilTabsGUI
     */
    protected $tabs;
    /**
     * @var ilToolbarGUI
     */
    protected $toolbar;
    /**
     * @var ilPanoptoPlugin
     */
    protected $pl;
    /**
     * @var
     */
    protected $parent_gui;

    /**
     * xpanGUI constructor.
     * @param ilObjPanoptoGUI $parent_gui
     */
    public function __construct(ilObjPanoptoGUI $parent_gui) {
        global $DIC;
        $this->ctrl = $DIC['ilCtrl'];
        $this->user = $DIC['ilUser'];
        $this->lng = $DIC['lng'];
        $this->tpl = $DIC['tpl'];
        $this->tabs = $DIC['ilTabs'];
        $this->toolbar = $DIC['ilToolbar'];
        $this->pl = ilPanoptoPlugin::getInstance();
        $this->parent_gui = $parent_gui;
    }

    /**
     *
     */
    public function executeCommand() {
        $this->setSubtabs();
        $next_class = $this->ctrl->getNextClass();
        switch ($next_class) {
            default:
                $cmd = $this->ctrl->getCmd(self::CMD_STANDARD);
                $this->performCommand($cmd);
                break;
        }
    }

    /**
     * @param $cmd
     */
    protected function performCommand($cmd) {
        $this->{$cmd}();
    }

    /**
     *
     */
    protected function setSubtabs() {
        // overwrite if class has subtabs
    }

    /**
     * @return int
     */
    public function getObjId() {
        return $this->parent_gui->getObjId();
    }

    /**
     * @return ilObjPanopto
     */
    public function getObject() {
        return $this->parent_gui->getObject();
    }

    /**
     *
     */
    protected abstract function index();
}