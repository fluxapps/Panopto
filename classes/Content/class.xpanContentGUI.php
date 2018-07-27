<?php

/**
 * Class xpanContentGUI
 *
 * @author Theodor Truffer <tt@studer-raimann.ch>
 *
 * @ilCtrl_isCalledBy xpanContentGUI: ilObjPanoptoGUI
 */
class xpanContentGUI extends xpanGUI {

    /**
     * @var xpanClient
     */
    protected $client;
    /**
     * @var String
     */
    protected $folder_id;

    /**
     * xpanContentGUI constructor.
     * @param ilObjPanoptoGUI $parent_gui
     */
    public function __construct(ilObjPanoptoGUI $parent_gui) {
        parent::__construct($parent_gui);

        $this->client = xpanClient::getInstance();

        $this->folder_id = $this->client->getFolderByExternalId($_GET['ref_id'])->getId();

        if (!$this->client->hasUserViewerAccessOnFolder($this->folder_id)) {
            $this->client->grantCurrentUserAccessToFolder($this->folder_id, xpanClient::ROLE_VIEWER);
        }
    }

    protected function index() {
        $sessions = $this->client->getSessionsOfFolder($this->folder_id);

        if (!$sessions) {
            ilUtil::sendInfo($this->pl->txt('msg_no_videos'));
            return;
        }

        $tpl = new ilTemplate('tpl.content_list.html', true, true, $this->pl->getDirectory());

        foreach ($sessions as $session) {
            $tpl->setCurrentBlock('list_item');
            $tpl->setVariable('SID', $session->getId());
            $tpl->setVariable('THUMBNAIL', 'https://' . xpanConfig::getConfig(xpanConfig::F_HOSTNAME) . $session->getThumbUrl());
            $tpl->setVariable('TITLE', $session->getName());
            $tpl->setVariable('DESCRIPTION', $session->getDescription());
            $tpl->setVariable('DURATION', $this->formatDuration($session->getDuration()));
            $tpl->parseCurrentBlock();
        }

        $this->tpl->addCss($this->pl->getDirectory() . '/templates/default/content_list.css');
        $this->tpl->addJavaScript($this->pl->getDirectory() . '/js/Panopto.js');
//        $this->tpl->addOnLoadCode('$("div.box").each(function(k, e) {$(e).delay(500).width("100%");});');
        $this->tpl->setContent($tpl->get() . $this->getModalPlayer());
    }


    protected function formatDuration($duration_in_seconds) {
        $t = floor($duration_in_seconds);
        return sprintf('%02d:%02d:%02d', ($t/3600),($t/60%60), $t%60);
    }

    /**
     * @return String
     */
    protected function getModalPlayer() {
        $this->tpl->addCss($this->pl->getDirectory() . '/templates/default/modal.css');
        $modal = ilModalGUI::getInstance();
        $modal->setId('xpan_modal_player');
        $modal->setType(ilModalGUI::TYPE_LARGE);
//		$modal->setHeading('<div id="xoct_waiter_modal" class="xoct_waiter xoct_waiter_mini"></div>');
        $modal->setBody('<section><div id="xpan_video_container"></div></section>');
        return $modal->getHTML();
    }
}