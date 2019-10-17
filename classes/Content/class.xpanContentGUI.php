<?php

/**
 * Class xpanContentGUI
 *
 * @author Theodor Truffer <tt@studer-raimann.ch>
 *
 * @ilCtrl_isCalledBy xpanContentGUI: ilObjPanoptoGUI
 */
class xpanContentGUI extends xpanGUI {

    const CMD_SHOW = "index";
    const CMD_SORTING = "sorting";
    const TAB_SUB_SHOW = "subShow";
    const TAB_SUB_SORTING = "subSorting";

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
     * @throws ilException
     */
    public function __construct(ilObjPanoptoGUI $parent_gui) {
        parent::__construct($parent_gui);

        $this->client = xpanClient::getInstance();

        $folder = $this->client->getFolderByExternalId($_GET['ref_id']);
        if (!$folder) {
            throw new ilException('No external folder found for this object.');
        }

        $this->folder_id = $folder->getId();

        // grant user permissions on the fly
        if (!$this->client->hasUserViewerAccessOnFolder($this->folder_id)) {
            $this->client->grantUserAccessToFolder($this->folder_id, xpanClient::ROLE_VIEWER);
        }
    }

    /**
     * @throws Exception
     */
    protected function index() {
        $this->addSubTabs(self::TAB_SUB_SHOW);
        $sessions = $this->client->getSessionsOfFolder($this->folder_id, $_GET['xpan_page']);

        if (!$sessions['count']) {
            ilUtil::sendInfo($this->pl->txt('msg_no_videos'));
            return;
        }

        $tpl = new ilTemplate('tpl.content_list.html', true, true, $this->pl->getDirectory());
        $pages = 1 + floor($sessions['count'] / 10);

        // "previous" button
        if ($_GET['xpan_page']) {
            $this->ctrl->setParameter($this, 'xpan_page', $_GET['xpan_page'] - 1);
            $link = $this->ctrl->getLinkTarget($this, self::CMD_STANDARD);
            // top
            $tpl->setCurrentBlock('previous_top');  // for some reason, i had to do 2 different blocks for top and bottom pagination
            $tpl->setVariable('LINK_PREVIOUS', $link);
            $tpl->parseCurrentBlock();
            // bottom
            $tpl->setCurrentBlock('previous_bottom');
            $tpl->setVariable('LINK_PREVIOUS', $link);
            $tpl->parseCurrentBlock();
        }

        // pages
        if ($pages > 1) {
            for ($i = 1; $i <= $pages; $i++) {
                $this->ctrl->setParameter($this, 'xpan_page', $i - 1);
                $link = $this->ctrl->getLinkTarget($this, self::CMD_STANDARD);
                // top
                $tpl->setCurrentBlock('page_top');
                $tpl->setVariable('LINK_PAGE', $link);
                if (($i-1) == $_GET['xpan_page']) {
                    $tpl->setVariable('ADDITIONAL_CLASS', 'xpan_page_active');
                }
                $tpl->setVariable('LABEL_PAGE', $i);
                $tpl->parseCurrentBlock();
                // bottom
                $tpl->setCurrentBlock('page_bottom');
                $tpl->setVariable('LINK_PAGE', $link);
                if (($i-1) == $_GET['xpan_page']) {
                    $tpl->setVariable('ADDITIONAL_CLASS', 'xpan_page_active');
                }
                $tpl->setVariable('LABEL_PAGE', $i);
                $tpl->parseCurrentBlock();
            }
        }

        // "next" button
        if ($sessions['count'] > (($_GET['xpan_page'] + 1)*10)) {
            $this->ctrl->setParameter($this, 'xpan_page', $_GET['xpan_page'] + 1);
            $link = $this->ctrl->getLinkTarget($this, self::CMD_STANDARD);
            // top
            $tpl->setCurrentBlock('next_top');
            $tpl->setVariable('LINK_NEXT', $link);
            $tpl->parseCurrentBlock();
            // bottom
            $tpl->setCurrentBlock('next_bottom');
            $tpl->setVariable('LINK_NEXT', $link);
            $tpl->parseCurrentBlock();
        }

        // videos
        foreach ($sessions['sessions'] as $session) {
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
        $this->tpl->addOnLoadCode('Panopto.base_url = "https://' . xpanConfig::getConfig(xpanConfig::F_HOSTNAME) . '";');
        $this->tpl->setContent($tpl->get() . $this->getModalPlayer());
    }


    protected function sorting()
    {
        $this->addSubTabs(self::TAB_SUB_SORTING);
    }


    /**
     * @param $duration_in_seconds
     * @return string
     */
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


    /**
     * Add sub tabs and activate the forwarded sub tab in the parameter.
     *
     * @param string $active_sub_tab
     */
    protected function addSubTabs($active_sub_tab)
    {
        global $DIC;
        $DIC->tabs()->addSubTab(self::TAB_SUB_SHOW,
            $this->pl->txt('content_show'),
            $DIC->ctrl()->getLinkTarget($this, self::CMD_SHOW)
        );

        if ($DIC->access()->checkAccess("write", "", $this->parent_gui->ref_id)) {
            $DIC->tabs()->addSubTab(self::TAB_SUB_SORTING,
                $this->pl->txt('content_sorting'),
                $DIC->ctrl()->getLinkTarget($this, self::CMD_SORTING)
            );
        }

        $DIC->tabs()->activateSubTab($active_sub_tab);
    }
}