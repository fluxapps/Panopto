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
        $html = xpanLTILaunch::launch($this->getObject());
        $this->tpl->addCss($this->pl->getDirectory() . '/templates/default/waiter.css');
        $this->tpl->addJavaScript($this->pl->getDirectory() . '/js/waiter.js');
        $this->tpl->addOnLoadCode('$("#lti_form").submit();');
        $this->tpl->addOnLoadCode('srWaiter.show();');
        $this->tpl->addOnLoadCode('$("iframe#basicltiLaunchFrame").load(function(){srWaiter.hide();});');
        $this->tpl->setContent($html . '<div id="sr_waiter" class="sr_waiter"></div>');
    }
}
