<?php

/**
 * Class xpanSettingsGUI
 *
 * @author Theodor Truffer <tt@studer-raimann.ch>
 *
 * @ilCtrl_isCalledBy xpanSettingsGUI: ilObjPanoptoGUI
 */
class xpanSettingsGUI extends xpanGUI {

    const CMD_UPDATE = 'update';

    /**
     *
     */
    protected function index() {
        $xpanSettingsFormGUI = new xpanSettingsFormGUI($this);
        $xpanSettingsFormGUI->fillForm();
        $this->tpl->setContent($xpanSettingsFormGUI->getHTML());
    }

    /**
     *
     */
    protected function update() {
        $xpanSettingsFormGUI = new xpanSettingsFormGUI($this);
        $xpanSettingsFormGUI->setValuesByPost();
        if (!$xpanSettingsFormGUI->saveForm()) {
            ilUtil::sendFailure($this->pl->txt('msg_incomplete'));
            $this->tpl->setContent($xpanSettingsFormGUI->getHTML());
            return;
        }
        ilUtil::sendSuccess($this->pl->txt('form_saved'), true);
        $this->ctrl->redirect($this, self::CMD_STANDARD);
    }
}