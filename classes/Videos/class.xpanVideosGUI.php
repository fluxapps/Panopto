<?php

/**
 * Class xpanVideosGUI
 *
 * @author Theodor Truffer <tt@studer-raimann.ch>
 *
 * @ilCtrl_isCalledBy xpanVideosGUI: ilObjPanoptoGUI
 */
class xpanVideosGUI extends xpanGUI {

    protected function index() {
        $html = xpanLTILaunch::launch();
        $this->tpl->addJavaScript($this->pl->getDirectory() . '/templates/default/waiter.js');
        $this->tpl->addOnLoadCode('$("#lti_form").submit();');
        $this->tpl->setContent($html);
    }
}