<?php
require_once __DIR__ . '/../vendor/autoload.php';
use srag\DIC\Panopto\DICTrait;
/**
 * Class ilObjPanoptoGUI
 *
 * @author Theodor Truffer <tt@studer-raimann.ch>
 *
 * @ilCtrl_isCalledBy ilObjPanoptoGUI: ilRepositoryGUI, ilObjPluginDispatchGUI, ilAdministrationGUI
 * @ilCtrl_Calls      ilObjPanoptoGUI: ilPermissionGUI, ilInfoScreenGUI, ilCommonActionDispatcherGUI
 *
 */
class ilObjPanoptoGUI extends ilObjectPluginGUI {
    use DICTrait;
    const TAB_CONTENT = 'content';
    const TAB_INFO = 'info';
    const TAB_VIDEOS = 'videos';
    const TAB_SETTINGS = 'settings';
    const TAB_PERMISSIONS = 'permissions';

    const CMD_STANDARD = 'index';
    const CMD_MANAGE_VIDEOS = 'manageVideos';

    /**
     * @return bool|void
     */
    function executeCommand() {
        $next_class = $this->ctrl->getNextClass();
        $cmd = $this->ctrl->getCmd();

        if (!ilObjPanoptoAccess::hasReadAccess() && $next_class != "ilinfoscreengui" && $cmd != "infoScreen") {
            ilUtil::sendFailure($this->plugin->txt('access_denied'), true);
            $this->ctrl->returnToParent($this);
        }
        if (self::version()->is6()) {
            $this->tpl->loadStandardTemplate();
        } else {
        $this->tpl->getStandardTemplate();
        }

        try {
            switch ($next_class) {
                case 'xpancontentgui':
                    if (!$this->ctrl->isAsynch()) {
                        $this->initHeader();
                        $this->setTabs();
                    }
                    $this->tabs_gui->activateTab(self::TAB_CONTENT);
                    $xvmpGUI = new xpanContentGUI($this);
                    $this->ctrl->forwardCommand($xvmpGUI);
                    if (self::version()->is6()) {
                        $this->tpl->printToStdout();
                    } else {
                    $this->tpl->show();
                    }
                    break;
                case 'xpansettingsgui':
                    if (!$this->ctrl->isAsynch()) {
                        $this->initHeader();
                        $this->setTabs();
                    }
                    $this->tabs_gui->activateTab(self::TAB_SETTINGS);
                    $xvmpGUI = new xpanSettingsGUI($this);
                    $this->ctrl->forwardCommand($xvmpGUI);
                    if (self::version()->is6()) {
                        $this->tpl->printToStdout();
                    } else {
                    $this->tpl->show();
                    }
                    break;
                case 'xpanvideosgui':
                    if (!$this->ctrl->isAsynch()) {
                        $this->initHeader();
                        $this->setTabs();
                    }
                    $this->tabs_gui->activateTab(self::TAB_VIDEOS);
                    $xvmpGUI = new xpanVideosGUI($this);
                    $this->ctrl->forwardCommand($xvmpGUI);
                    if (self::version()->is6()) {
                        $this->tpl->printToStdout();
                    } else {
                    $this->tpl->show();
                    }
                    break;

                case "ilinfoscreengui":
                    if (!$this->ctrl->isAsynch()) {
                        $this->initHeader();
                        $this->setTabs();
                    }
                    $this->tabs_gui->activateTab(self::TAB_INFO);
                    $this->checkPermission("visible");
                    $this->infoScreen();	// forwards command
                    if (self::version()->is6()) {
                        $this->tpl->printToStdout();
                    } else {
                    $this->tpl->show();
                    }
                    break;
                case 'ilpermissiongui':
                    $this->initHeader(false);
                    parent::executeCommand();
                    break;
                default:
                    // workaround for object deletion; 'parent::executeCommand()' shows the template and leads to "Headers already sent" error
                    if ($next_class == "" && $cmd == 'deleteObject') {
                        $this->deleteObject();
                        break;
                    }
                    parent::executeCommand();
                    break;
            }
        } catch (Exception $e) {
            ilUtil::sendFailure($e->getMessage());
            if (!$this->creation_mode) {
                if (self::version()->is6()) {
                    $this->tpl->printToStdout();
                } else {
                $this->tpl->show();
                }
            }
        }
    }

    /**
     *
     */
    protected function initHeader($render_locator = true) {
        if ($render_locator) {
            $this->setLocator();
        }
        $this->tpl->setTitleIcon(ilObjPanopto::_getIcon($this->object_id));
        $this->tpl->setTitle($this->object->getTitle());
        $this->tpl->setDescription($this->object->getDescription());

        if (ilObjPanoptoAccess::_isOffline($this->object->getId())) {
            /**
             * @var $list_gui ilObjPanoptoListGUI
             */
            $list_gui = ilObjectListGUIFactory::_getListGUIByType('xpan');
            $this->tpl->setAlertProperties($list_gui->getAlertProperties());
        }

    }


    /**
     * @return bool
     */
    protected function setTabs() {
        global $DIC;
        $lng = $DIC['lng'];

        $this->tabs_gui->addTab(self::TAB_CONTENT, $this->lng->txt(self::TAB_CONTENT), $this->ctrl->getLinkTargetByClass(xpanContentGUI::class, xpanContentGUI::CMD_STANDARD));
        $this->tabs_gui->addTab(self::TAB_INFO, $this->lng->txt(self::TAB_INFO . '_short'), $this->ctrl->getLinkTargetByClass(ilInfoScreenGUI::class));

        if (ilObjPanoptoAccess::hasWriteAccess()) {
            $this->tabs_gui->addTab(self::TAB_VIDEOS, $this->plugin->txt('tab_' . self::TAB_VIDEOS), $this->ctrl->getLinkTargetByClass(xpanVideosGUI::class, xpanVideosGUI::CMD_STANDARD));
            $this->tabs_gui->addTab(self::TAB_SETTINGS, $this->lng->txt(self::TAB_SETTINGS), $this->ctrl->getLinkTargetByClass(xpanSettingsGUI::class, xpanSettingsGUI::CMD_STANDARD));
        }

        if ($this->checkPermissionBool("edit_permission")) {
            $this->tabs_gui->addTab("perm_settings", $lng->txt("perm_settings"), $this->ctrl->getLinkTargetByClass(array(
                get_class($this),
                "ilpermissiongui",
            ), "perm"));
        }

        return true;
    }

    /**
     * @param $cmd
     */
    protected function performCommand($cmd) {
        $this->{$cmd}();
    }

    /**
     * @return int
     */
    public function getObjId() {
        return $this->obj_id;
    }

    /**
     * @return ilObjPanopto
     */
    public function getObject() {
        return $this->object;
    }

    /**
     * @return ilPanoptoPlugin|object
     */
    protected function getPlugin() {
        return ilPanoptoPlugin::getInstance();
    }

    /**
     *
     */
    protected function index() {
        $this->ctrl->redirectByClass(xpanContentGUI::class);
    }

    /**
     *
     */
    protected function manageVideos() {
        $this->ctrl->redirectByClass(xpanVideosGUI::class);
    }

    /**
     * @return bool
     */
    protected function supportsCloning() {
        return true;
    }


    /**
     * @return string
     */
    function getAfterCreationCmd() {
        return self::CMD_MANAGE_VIDEOS;
    }

    /**
     * @return string
     */
    function getStandardCmd() {
        return self::CMD_STANDARD;
    }

    /**
     * @return string
     */
    function getType() {
        return ilPanoptoPlugin::XPAN;
    }


}
